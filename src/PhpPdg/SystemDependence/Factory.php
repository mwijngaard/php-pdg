<?php

namespace PhpPdg\SystemDependence;

use PHPCfg\Op\Expr\MethodCall;
use PHPCfg\Op\Expr\StaticCall;
use PHPCfg\Op\Stmt\Class_;
use PHPCfg\Op\Stmt\ClassMethod;
use PHPCfg\Op\Stmt\Function_;
use PHPCfg\Operand;
use PHPCfg\Operand\Literal;
use PhpPdg\CfgBridge\System as CfgSystem;
use PhpPdg\Graph\FactoryInterface as GraphFactoryInterface;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\ProgramDependence\FactoryInterface as PdgFactoryInterface;
use PhpPdg\ProgramDependence\Node\OpNode;
use PhpPdg\SystemDependence\Node\BuiltinFuncNode;
use PhpPdg\SystemDependence\Node\FuncNode;
use PhpPdg\SystemDependence\Node\UndefinedFuncNode;
use PhpPdg\SystemDependence\Node\UndefinedNsFuncNode;
use PHPTypes\InternalArgInfo;
use PHPTypes\State;
use PHPTypes\Type;
use PHPTypes\TypeReconstructor;
use PhpPdg\Graph\Factory as GraphFactory;
use PhpPdg\ProgramDependence\Factory as PdgFactory;

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
		return new self($graph_factory, PdgFactory::createDefault($graph_factory));
	}

	public function create(CfgSystem $cfg_system) {
		$sdg = $this->graph_factory->create();
		$system = new System($sdg);

		/** @var FuncNode[]|\SplObjectStorage $pdg_func_lookup */
		$pdg_func_lookup = new \SplObjectStorage();
		$cfg_scripts = [];
		/** @var \SplFileInfo $fileinfo */
		foreach ($cfg_system->getFilenames() as $filename) {
			$cfg_scripts[] = $cfg_script = $cfg_system->getScript($filename);

			$pdg_func = $this->pdg_factory->create($cfg_script->main, $filename);
			$system->scripts[$filename] = $pdg_func;
			$func_node = new FuncNode($pdg_func);
			$system->sdg->addNode($func_node);
			$pdg_func_lookup[$cfg_script->main] = $func_node;

			foreach ($cfg_script->functions as $cfg_func) {
				$pdg_func = $this->pdg_factory->create($cfg_func, $filename);
				$scoped_name = $cfg_func->getScopedName();
				if ($cfg_func->class !== null) {
					$system->methods[$scoped_name] = $pdg_func;
				} else if (strpos($cfg_func->name, '{anonymous}#') === 0) {
					$system->closures[$scoped_name] = $pdg_func;
				} else {
					$system->functions[$scoped_name] = $pdg_func;
				}
				$func_node = new FuncNode($pdg_func);
				$system->sdg->addNode($func_node);
				$pdg_func_lookup[$cfg_func] = $func_node;
			}
		}

		$state = new State($cfg_scripts);
		$this->type_reconstructor->resolve($state);

		// link function calls to their functions
		foreach ($state->funcCalls as $funcCallPair) {
			list($func_call, $containing_cfg_func) = $funcCallPair;
			$call_node = new OpNode($func_call);
			$system->sdg->addNode($call_node);
			assert(isset($pdg_func_lookup[$containing_cfg_func]));
			$system->sdg->addEdge($pdg_func_lookup[$containing_cfg_func], $call_node, ['type' => 'contains']);
			if ($func_call->name instanceof Literal) {
				$name = strtolower($func_call->name->value);
				if (isset($state->functionLookup[$name]) === true) {
					$this->linkFunctions($sdg, $call_node, $state->functionLookup[$name], $pdg_func_lookup);
				}
				// take monkey-patching into account and also link builtin functions
				if (isset($state->internalTypeInfo->functions[$name]) === true) {
					$builtin_func_node = new BuiltinFuncNode($name, null);
					if ($sdg->hasNode($builtin_func_node) === false) {
						$sdg->addNode($builtin_func_node);
						$sdg->addEdge($call_node, $builtin_func_node, ['type' => 'call']);
					}
				}
				// if we haven't linked anything yet, this is most likely a vendor function (because we know its name).
				if ($sdg->hasEdges($call_node, null, ['type' => 'call']) === false) {
					$undefined_func_node = new UndefinedFuncNode($name, null);
					if ($sdg->hasNode($undefined_func_node) === false) {
						$sdg->addNode($undefined_func_node);
						$sdg->addEdge($call_node, $undefined_func_node, ['type' => 'call']);
					}
				}
			}
		}

		foreach ($state->nsFuncCalls as $nsFuncCallPair) {
			list($ns_func_call, $containing_cfg_func) = $nsFuncCallPair;
			$call_node = new OpNode($ns_func_call);
			$system->sdg->addNode($call_node);
			assert(isset($pdg_func_lookup[$containing_cfg_func]));
			$system->sdg->addEdge($pdg_func_lookup[$containing_cfg_func], $call_node, ['type' => 'contains']);

			// should always be the case, as otherwise it would be a normal func call
			assert($ns_func_call->name instanceof Literal);
			assert($ns_func_call->nsName instanceof Literal);

			$name = strtolower($ns_func_call->name->value);
			$nsName = strtolower($ns_func_call->nsName->value);

			if (isset($state->functionLookup[$nsName]) === true) {
				$this->linkFunctions($sdg, $call_node, $state->functionLookup[$nsName], $pdg_func_lookup);
			} else {
				$name = strtolower($ns_func_call->name->value);
				if (isset($state->functionLookup[$name]) === true) {
					$this->linkFunctions($sdg, $call_node, $state->functionLookup[$name], $pdg_func_lookup);
				}
				// take monkey-patching into account and also link builtin functions
				if (isset($state->internalTypeInfo->functions[$name]) === true) {
					$builtin_func_node = new BuiltinFuncNode($name, null);
					if ($sdg->hasNode($builtin_func_node) === false) {
						$sdg->addNode($builtin_func_node);
						$sdg->addEdge($call_node, $builtin_func_node, ['type' => 'call']);
					}
				}
			}
			// if we haven't linked anything yet, this is most likely a vendor function (because we know its name) but
			// we still do not know if it refers to the namespaced or regular variant, so store both.
			if ($sdg->hasEdges($call_node, null, ['type' => 'call']) === false) {
				$undefined_ns_func_node = new UndefinedNsFuncNode($name, $nsName);
				if ($sdg->hasNode($undefined_ns_func_node) === false) {
					$sdg->addNode($undefined_ns_func_node);
					$sdg->addEdge($call_node, $undefined_ns_func_node, ['type' => 'call']);
				}
			}
		}

		foreach ($state->methodCalls as $methodCallPair) {
			/** @var MethodCall $method_call */
			list($method_call, $containing_cfg_func) = $methodCallPair;
			$call_node = new OpNode($method_call);
			$system->sdg->addNode($call_node);
			assert(isset($pdg_func_lookup[$containing_cfg_func]));
			$system->sdg->addEdge($pdg_func_lookup[$containing_cfg_func], $call_node, ['type' => 'contains']);

			if ($method_call->name instanceof Literal) {
				$methodname = strtolower($method_call->name->value);
				$classname = $this->resolveClassName($method_call->var);
				if ($classname !== null) {
					$nodes = $this->resolvePolymorphicMethodCall($state, $classname, $methodname, $pdg_func_lookup);
					if (empty($nodes) === false) {
						$this->addNodesAndCallEdges($sdg, $call_node, $nodes);
					} else {
						$nodes = $this->resolvePolymorphicMethodCall($state, $classname, '__call', $pdg_func_lookup);
						if (empty($nodes) === false) {
							$this->addNodesAndCallEdges($sdg, $call_node, $nodes);
						} else {
							$this->addNodeAndCallEdge($sdg, $call_node, new UndefinedFuncNode($methodname, $classname));
						}
					}
				}
			}
		}

		foreach ($state->staticCalls as $staticCallPair) {
			/** @var StaticCall $static_call */
			list($static_call, $containing_cfg_func) = $staticCallPair;
			$call_node = new OpNode($static_call);
			$system->sdg->addNode($call_node);
			assert(isset($pdg_func_lookup[$containing_cfg_func]));
			$system->sdg->addEdge($pdg_func_lookup[$containing_cfg_func], $call_node, ['type' => 'contains']);

			if ($static_call->name instanceof Literal) {
				$methodname = strtolower($static_call->name->value);
				$classname = $this->resolveClassName($static_call->class);
				if ($classname !== null) {
					$nodes = $this->resolvePolymorphicMethodCall($state, $classname, $methodname, $pdg_func_lookup);
					if (empty($nodes) === false) {
						$this->addNodesAndCallEdges($sdg, $call_node, $nodes);
					} else {
						$nodes = $this->resolvePolymorphicMethodCall($state, $classname, '__callStatic', $pdg_func_lookup);
						if (empty($nodes) === false) {
							$this->addNodesAndCallEdges($sdg, $call_node, $nodes);
						} else {
							$this->addNodeAndCallEdge($sdg, $call_node, new UndefinedFuncNode($methodname, $classname));
						}
					}
				}
			}
		}

		return $system;
	}

	private function linkFunctions(GraphInterface $sdg, $call_node, $cfg_functions, \SplObjectStorage $pdg_func_lookup) {
		/** @var Function_ $cfg_function */
		foreach ($cfg_functions as $cfg_function) {
			$cfg_func = $cfg_function->func;
			assert(isset($pdg_func_lookup[$cfg_func]));
			$sdg->addEdge($call_node, $pdg_func_lookup[$cfg_func], ['type' => 'call']);
		}
	}

	private function resolveClassName(Operand $class) {
		$classname = null;
		if (is_object($class->type) === true && $class->type instanceof Type) {
			/** @var Type $classType */
			$classType = $class->type;
			switch ($classType->type) {
				case Type::TYPE_STRING:
					if ($class instanceof Literal) {
						$classname = $class->value;
					}
					break;
				case Type::TYPE_OBJECT:
					$classname = $classType->userType;
			}
		}
		if ($classname !== null) {
			$classname = strtolower($classname);
		}
		return $classname;
	}

	private function resolvePolymorphicMethodCall(State $state, $classname, $methodname, \SplObjectStorage $pdg_func_lookup) {
		$nodes = [];
		if (isset($state->classResolvedBy[$classname]) === true) {
			foreach ($state->classResolvedBy[$classname] as $sclassname) {
				foreach ($this->resolveMethodCall($state, $sclassname, $methodname, $pdg_func_lookup) as $node) {
					$nodes[] = $node;
				}
			}
		}
		return array_unique($nodes, SORT_REGULAR);
	}

	private function resolveMethodCall(State $state, $classname, $methodname, \SplObjectStorage $pdg_func_lookup) {
		$nodes = [];

		// try resolve in system
		if (isset($state->classLookup[$classname]) === true) {
			foreach ($state->classLookup[$classname] as $class) {
				if (isset($state->methodLookup[$class][$methodname]) === true) {
					/** @var ClassMethod $method */
					foreach ($state->methodLookup[$class][$methodname] as $method) {
						$func = $method->getFunc();
						assert(isset($pdg_func_lookup[$func]));
						$nodes[] = $pdg_func_lookup[$func];
					}
				} else if ($class->extends !== null) {
					foreach ($this->resolveMethodCall($state, strtolower($class->extends->value), $methodname, $pdg_func_lookup) as $node) {
						$nodes[] = $node;
					}
				}
			}
		}

		$node = $this->resolveBuiltinMethodCall($state->internalTypeInfo, $classname, $methodname);
		if ($node !== null) {
			$nodes[] = $node;
		}

		return $nodes;
	}

	private function resolveBuiltinMethodCall(InternalArgInfo $internalArgInfo, $classname, $methodname) {
		$node = null;
		if (isset($internalArgInfo->methods[$classname][$methodname]) === true) {
			$node = new BuiltinFuncNode($classname, $methodname);
		}
		if (isset($internalArgInfo->classExtends[$classname]) === true) {
			$node = $this->resolveBuiltinMethodCall($internalArgInfo, $internalArgInfo->classExtends[$classname], $methodname);
		}
		return $node;
	}

	private function addNodesAndCallEdges(GraphInterface $sdg, $source_node, $target_nodes) {
		foreach ($target_nodes as $target_node) {
			$this->addNodeAndCallEdge($sdg, $source_node, $target_node);
		}
	}

	private function addNodeAndCallEdge(GraphInterface $sdg, $source_node, $target_node) {
		if ($sdg->hasNode($target_node) === false) {
			$sdg->addNode($target_node);
		}
		$sdg->addEdge($source_node, $target_node, ['type' => 'call']);
	}
}