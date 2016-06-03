<?php

namespace PhpPdg;

use PHPCfg\Func as CfgFunc;
use PHPCfg\Op\CallableOp;
use PHPCfg\Op\Stmt\ClassMethod;
use PHPCfg\Operand;
use PHPCfg\Operand\Literal;
use PHPCfg\Script;
use PHPCfg\Traverser;
use PhpPdg\Graph\FactoryInterface as GraphFactoryInterface;
use PhpPdg\ControlDependence\GeneratorInterface as ControlDependenceGeneratorInterface;
use PhpPdg\DataDependence\GeneratorInterface as DataDependenceGeneratorInterface;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\EntryNode;
use PhpPdg\Nodes\OpNode;
use PHPTypes\State;
use PHPTypes\Type;
use PHPTypes\TypeReconstructor;

class Factory implements FactoryInterface {
	/** @var GraphFactoryInterface  */
	private $graph_factory;
	/** @var ControlDependenceGeneratorInterface  */
	private $control_dependence_generator;
	/** @var DataDependenceGeneratorInterface  */
	private $data_dependence_generator;
	/** @var  TypeReconstructor */
	private $type_reconstructor;

	public function __construct(GraphFactoryInterface $graph_factory, ControlDependenceGeneratorInterface $control_dependence_generator, DataDependenceGeneratorInterface $data_dependence_generator) {
		$this->graph_factory = $graph_factory;
		$this->control_dependence_generator = $control_dependence_generator;
		$this->data_dependence_generator = $data_dependence_generator;
		$this->type_reconstructor = new TypeReconstructor();
	}

	public function create(array $scripts_by_path) {
		$graph = $this->graph_factory->create();
		$system = new System($graph);
		$state = new State($scripts_by_path);
		$this->type_reconstructor->resolve($state);     // add type information to cfg's

		$pdg_func_lookup = new \SplObjectStorage();

		/** @var Script $script */
		foreach ($scripts_by_path as $path => $script) {
			$main_entry_node = new EntryNode("script[$path]");
			$system->scripts[$path] = new Func(null, null, $main_entry_node);
			$pdg_func = $this->initFunc($script->main, $main_entry_node, $graph);
			$pdg_func_lookup[$script->main] = $pdg_func;

			foreach ($script->functions as $cfg_func) {
				$scoped_name = $cfg_func->getScopedName();
				$entry_node = new EntryNode("func[$scoped_name]");
				$func = new Func($cfg_func->name, $cfg_func->class, $entry_node);
				if ($cfg_func->class !== null) {
					$system->methods[$scoped_name] = $func;
				} else if (strpos($cfg_func->name, '{anonymous}#') === 0) {
					$system->closures[$scoped_name] = $func;
				} else {
					$system->functions[$scoped_name] = $func;
				}
				$pdg_func = $this->initFunc($cfg_func, $entry_node, $graph);
				$pdg_func_lookup[$cfg_func] = $pdg_func;
			}
		}

		// link function calls to their functions
		foreach ($state->funcCalls as $funcCallPair) {
			$funcCall = $funcCallPair[0];
			if ($funcCall->name instanceof Literal) {
				$name = strtolower($funcCall->name->value);
				if (isset($state->functionLookup[$name]) === true) {
					$this->addFunctionsCallEdges($graph, new OpNode($funcCall), $funcCall->args, $state->functionLookup[$name], $pdg_func_lookup);
				}
			}
		}

		foreach ($state->nsFuncCalls as $nsFuncCallPair) {
			$nsFuncCall = $nsFuncCallPair[0];
			assert($nsFuncCall->nsName instanceof Literal); // should always be the case, as otherwise it would be a normal func call
			$functions = null;
			$nsName = strtolower($nsFuncCall->nsName->value);
			if (isset($state->functionLookup[$nsName]) === true) {
				$functions = $state->functionLookup[$nsName];
			} else {
				assert($nsFuncCall->name instanceof Literal);
				$name = strtolower($nsFuncCall->name->value);
				if (isset($state->functionLookup[$name]) === true) {
					$functions = $state->functionLookup[$name];
				}
			}

			if ($functions !== null) {
				$this->addFunctionsCallEdges($graph, new OpNode($nsFuncCall), $nsFuncCall->args, $functions, $pdg_func_lookup);
			}
		}

		foreach ($state->methodCalls as $methodCallPair) {
			$methodCall = $methodCallPair[0];
			if ($methodCall->name instanceof Literal) {
				$name = strtolower($methodCall->name->value);
				$var_type = $methodCall->var->type;
				if ($var_type->type === Type::TYPE_OBJECT) {
					$class_name = strtolower($var_type->userType);
					$methods = $this->resolveClassMethods($state, $class_name, $name);

					$this->addFunctionsCallEdges($graph, new OpNode($methodCall), $methodCall->args, $methods, $pdg_func_lookup);
				}
			}
		}

		foreach ($state->staticCalls as $staticCallPair) {
			$staticCall = $staticCallPair[0];
			if ($staticCall->name instanceof Literal) {
				if ($staticCall->class instanceof Literal) {
					$class_name = strtolower($staticCall->class->value);
				} else {
					$class_name = $this->resolveClassNameFromType($staticCall->class->type);
				}
				if ($class_name !== null) {
					$name = strtolower($staticCall->name->value);
					$methods = $this->resolveClassMethods($state, $class_name, $name);
					$this->addFunctionsCallEdges($graph, new OpNode($staticCall), $staticCall->args, $methods, $pdg_func_lookup);
				}
			}
		}

		return $system;
	}

	private function initFunc(CfgFunc $cfg_func, $entry_node, GraphInterface $graph) {
		$pdg_func = new Func($cfg_func->name, $cfg_func->class, $entry_node);
		$graph->addNode($entry_node);
		$traverser = new Traverser();
		$traverser->addVisitor(new InitializingVisitor($graph, $pdg_func));
		$traverser->traverseFunc($cfg_func);
		$this->control_dependence_generator->addFuncControlDependenceEdgesToGraph($cfg_func, $graph, $entry_node);
		$this->data_dependence_generator->addFuncDataDependenceEdgesToGraph($cfg_func, $graph);
		return $pdg_func;
	}

	/**
	 * @param GraphInterface $graph
	 * @param OpNode $call_op_node
	 * @param Operand[] $call_op_args
	 * @param CallableOp[] $callable_ops
	 * @param \SplObjectStorage $pdg_func_lookup
	 */
	private function addFunctionsCallEdges(GraphInterface $graph, OpNode $call_op_node, $call_op_args, $callable_ops, \SplObjectStorage $pdg_func_lookup) {
		foreach ($callable_ops as $callable_op) {
			$cfg_func = $callable_op->getFunc();
			/** @var Func $pdg_func */
			$pdg_func = $pdg_func_lookup[$cfg_func];
			$graph->addEdge($call_op_node, $pdg_func->entry_node, [
				'type' => 'call'
			]);
			foreach ($call_op_args as $i => $arg) {
				if (isset($pdg_func->param_nodes[$i]) === true) {
					$graph->addEdge($call_op_node, $pdg_func->param_nodes[$i], [
						'type' => 'param in',
						'index' => $i
					]);
				}
			}
			foreach ($pdg_func->return_nodes as $return_node) {
				$graph->addEdge($return_node, $call_op_node, [
					'type' => 'return'
				]);
			}
		}
	}

	/**
	 * @param Type $type
	 * @return string|null
	 */
	private function resolveClassNameFromType(Type $type) {
		if ($type->type === Type::TYPE_OBJECT) {
			return strtolower($type->userType);
		}
		return null;
	}

	/**
	 * @param State $state
	 * @param string $class_name
	 * @param string $method_name
	 * @return ClassMethod[]
	 */
	private function resolveClassMethods(State $state, $class_name, $method_name) {
		$methods = [];
		if (isset($state->classResolves[$class_name]) === true) {
			foreach ($state->classResolves[$class_name] as $class) {
				foreach ($class->stmts->children as $op) {
					if ($op instanceof ClassMethod && strtolower($op->func->name) === $method_name) {
						$methods[] = $op;
					}
				}
			}
		}
		return $methods;
	}
}