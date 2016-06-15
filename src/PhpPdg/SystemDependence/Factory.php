<?php

namespace PhpPdg\SystemDependence;

use PHPCfg\Op\Stmt\ClassMethod;
use PHPCfg\Op\Stmt\Function_;
use PHPCfg\Operand;
use PHPCfg\Operand\Literal;
use PhpPdg\Graph\FactoryInterface as GraphFactoryInterface;
use PhpPdg\ProgramDependence\FactoryInterface as PdgFactoryInterface;
use PhpPdg\CfgBridge\System as CfgBridgeSystem;
use PhpPdg\SystemDependence\Node\CallNode;
use PhpPdg\SystemDependence\Node\FuncNode;
use PHPTypes\State;
use PHPTypes\Type;
use PHPTypes\TypeReconstructor;
use PhpPdg\Graph\Factory as GraphFactory;
use PhpPdg\ProgramDependence\Factory as PdgFactory;
use PhpPdg\SystemDependence\Factory as SdgFactory;

class Factory implements FactoryInterface {
	/** @var GraphFactoryInterface  */
	private $graph_factory;
	/** @var PdgFactoryInterface  */
	private $pdg_factory;
	/** @var  TypeReconstructor */
	private $type_reconstructor;

	public function __construct(GraphFactoryInterface $graph_factory, PdgFactoryInterface $pdg_factory) {
		$this->graph_factory = $graph_factory;
		$this->pdg_factory = $pdg_factory;
		$this->type_reconstructor = new TypeReconstructor();
	}

	public static function createDefault() {
		$graph_factory = new GraphFactory();
		return new SdgFactory($graph_factory, PdgFactory::createDefault($graph_factory));
	}

	/**
	 * @param CfgBridgeSystem $cfg_bridge_system
	 * @return System
	 */
	public function create(CfgBridgeSystem $cfg_bridge_system) {
		$sdg = $this->graph_factory->create();
		$system = new System($sdg);

		/** @var FuncNode[]|\SplObjectStorage $pdg_func_lookup */
		$pdg_func_lookup = new \SplObjectStorage();
		$cfg_scripts = [];
		foreach ($cfg_bridge_system->getFilePaths() as $file_path) {
			$cfg_bridge_script = $cfg_bridge_system->getScript($file_path);
			$cfg_scripts[] = $cfg_script = $cfg_bridge_script->getScript();

			$pdg_func = $this->pdg_factory->create($cfg_script->main, $file_path);
			$system->scripts[$file_path] = $pdg_func;
			$func_node = new FuncNode("script[$file_path]", $pdg_func);
			$system->sdg->addNode($func_node);
			$pdg_func_lookup[$cfg_script->main] = $func_node;

			foreach ($cfg_script->functions as $cfg_func) {
				$pdg_func = $this->pdg_factory->create($cfg_func);
				$scoped_name = $cfg_func->getScopedName();
				if ($cfg_func->class !== null) {
					$system->methods[$scoped_name] = $pdg_func;
					$id = "method[$scoped_name]";
				} else if (strpos($cfg_func->name, '{anonymous}#') === 0) {
					$system->closures[$scoped_name] = $pdg_func;
					$id = "closure[$scoped_name]";
				} else {
					$system->functions[$scoped_name] = $pdg_func;
					$id = "function[$scoped_name]";
				}
				$func_node = new FuncNode($id, $pdg_func);
				$system->sdg->addNode($func_node);
				$pdg_func_lookup[$cfg_func] = $func_node;
			}
		}

		$state = new State($cfg_scripts);
		$this->type_reconstructor->resolve($state);

		// link function calls to their functions
		foreach ($state->funcCalls as $funcCallPair) {
			list($func_call, $containing_cfg_func) = $funcCallPair;
			assert(isset($pdg_func_lookup[$containing_cfg_func]));
			$func_node = $pdg_func_lookup[$containing_cfg_func];
			$call_node = new CallNode($func_call, $func_node->getId(), $func_node->getFunc());
			$system->sdg->addNode($call_node);
			if ($func_call->name instanceof Literal) {
				$name = strtolower($func_call->name->value);
				if (isset($state->functionLookup[$name]) === true) {
					/** @var Function_ $cfg_function */
					foreach ($state->functionLookup[$name] as $cfg_function) {
						$cfg_func = $cfg_function->func;
						assert(isset($pdg_func_lookup[$cfg_func]));
						$system->sdg->addEdge($call_node, $pdg_func_lookup[$cfg_func]);
					}
				}
			}
		}

		foreach ($state->nsFuncCalls as $nsFuncCallPair) {
			list($ns_func_call, $containing_cfg_func) = $nsFuncCallPair;
			assert(isset($pdg_func_lookup[$containing_cfg_func]));
			$func_node = $pdg_func_lookup[$containing_cfg_func];
			$call_node = new CallNode($ns_func_call, $func_node->getId(), $func_node->getFunc());
			$system->sdg->addNode($call_node);

			assert($ns_func_call->nsName instanceof Literal); // should always be the case, as otherwise it would be a normal func call
			$cfg_functions = null;
			$nsName = strtolower($ns_func_call->nsName->value);
			if (isset($state->functionLookup[$nsName]) === true) {
				$cfg_functions = $state->functionLookup[$nsName];
			} else {
				assert($ns_func_call->name instanceof Literal);
				$name = strtolower($ns_func_call->name->value);
				if (isset($state->functionLookup[$name]) === true) {
					$cfg_functions = $state->functionLookup[$name];
				}
			}

			if ($cfg_functions !== null) {
				/** @var Function_ $cfg_function */
				foreach ($cfg_functions as $cfg_function) {
					$cfg_func = $cfg_function->func;
					assert(isset($pdg_func_lookup[$cfg_func]));
					$system->sdg->addEdge($call_node, $pdg_func_lookup[$cfg_func]);
				}
			}
		}

		foreach ($state->methodCalls as $methodCallPair) {
			list($method_call, $containing_cfg_func) = $methodCallPair;
			assert(isset($pdg_func_lookup[$containing_cfg_func]));
			$func_node = $pdg_func_lookup[$containing_cfg_func];
			$call_node = new CallNode($method_call, $func_node->getId(), $func_node->getFunc());
			$system->sdg->addNode($call_node);

			if ($method_call->name instanceof Literal) {
				$name = strtolower($method_call->name->value);
				$var_type = $method_call->var->type;
				if ($var_type->type === Type::TYPE_OBJECT) {
					$class_name = strtolower($var_type->userType);
					$cfg_methods = $this->resolveClassMethods($state, $class_name, $name);

					/** @var ClassMethod $cfg_method */
					foreach ($cfg_methods as $cfg_method) {
						$cfg_func = $cfg_method->func;
						assert(isset($pdg_func_lookup[$cfg_func]));
						$system->sdg->addEdge($call_node, $pdg_func_lookup[$cfg_func]);
					}
				}
			}
		}

		foreach ($state->staticCalls as $staticCallPair) {
			list($static_call, $containing_cfg_func) = $staticCallPair;
			assert(isset($pdg_func_lookup[$containing_cfg_func]));
			$func_node = $pdg_func_lookup[$containing_cfg_func];
			$call_node = new CallNode($static_call, $func_node->getId(), $func_node->getFunc());
			$system->sdg->addNode($call_node);

			if ($static_call->name instanceof Literal) {
				if ($static_call->class instanceof Literal) {
					$class_name = strtolower($static_call->class->value);
				} else {
					$class_name = $this->resolveClassNameFromType($static_call->class->type);
				}
				if ($class_name !== null) {
					$name = strtolower($static_call->name->value);
					$cfg_methods = $this->resolveClassMethods($state, $class_name, $name);
					/** @var ClassMethod $cfg_method */
					foreach ($cfg_methods as $cfg_method) {
						$cfg_func = $cfg_method->func;
						assert(isset($pdg_func_lookup[$cfg_func]));
						$system->sdg->addEdge($call_node, $pdg_func_lookup[$cfg_func]);
					}
				}
			}
		}

		return $system;
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