<?php

namespace PhpPdg\SystemDependence;

use PHPCfg\Op\Expr\Assign;
use PHPCfg\Op\Expr\AssignRef;
use PHPCfg\Op\Expr\Isset_;
use PHPCfg\Op\Expr\MethodCall;
use PHPCfg\Op\Expr\PropertyFetch;
use PHPCfg\Op\Expr\StaticCall;
use PHPCfg\Op\Stmt\Class_;
use PHPCfg\Op\Stmt\ClassMethod;
use PHPCfg\Op\Stmt\Function_;
use PHPCfg\Op\Terminal\Unset_;
use PHPCfg\Operand;
use PHPCfg\Operand\Literal;
use PhpParser\Node\Expr\AssignOp;
use PhpPdg\CfgBridge\System as CfgSystem;
use PhpPdg\Graph\FactoryInterface as GraphFactoryInterface;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\Node\NodeInterface;
use PhpPdg\ProgramDependence\FactoryInterface as PdgFactoryInterface;
use PhpPdg\ProgramDependence\Func;
use PhpPdg\ProgramDependence\Node\OpNode;
use PhpPdg\SystemDependence\Node\BuiltinFuncNode;
use PhpPdg\SystemDependence\Node\FuncNode;
use PhpPdg\SystemDependence\Node\UndefinedFuncNode;
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
					$this->ensureNodeAndCallEdgeAdded($sdg, $call_node, new BuiltinFuncNode($name, null));
				}
				// if we haven't linked anything yet, this is most likely a vendor function (because we know its name).
				if ($sdg->hasEdges($call_node, null, ['type' => 'call']) === false) {
					$this->ensureNodeAndCallEdgeAdded($sdg, $call_node, new UndefinedFuncNode($name, null));
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

			$nsName = strtolower($ns_func_call->nsName->value);
			$name = strtolower($ns_func_call->name->value);

			if (isset($state->functionLookup[$nsName]) === true) {
				$this->linkFunctions($sdg, $call_node, $state->functionLookup[$nsName], $pdg_func_lookup);
			}
			if (isset($state->functionLookup[$name]) === true) {
				$this->linkFunctions($sdg, $call_node, $state->functionLookup[$name], $pdg_func_lookup);
			}
			// take monkey-patching into account and also link builtin functions
			if (isset($state->internalTypeInfo->functions[$name]) === true) {
				$this->ensureNodeAndCallEdgeAdded($sdg, $call_node, new BuiltinFuncNode($name, null));
			}
			// if we haven't linked anything yet, this is most likely a vendor function (because we know its name).
			if ($sdg->hasEdges($call_node, null, ['type' => 'call']) === false) {
				$this->ensureNodeAndCallEdgeAdded($sdg, $call_node, new UndefinedFuncNode($nsName, null));
				$this->ensureNodeAndCallEdgeAdded($sdg, $call_node, new UndefinedFuncNode($name, null));
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
				$classnames = $this->resolveClassNames($method_call->var);
				foreach ($classnames as $classname) {
					$nodes = $this->resolvePolymorphicMethodCall($state, $classname, $methodname, $pdg_func_lookup, false);
					if (empty($nodes) === false) {
						$this->ensureNodesAndCallEdgesAdded($sdg, $call_node, $nodes);
					} else {
						$this->ensureNodeAndCallEdgeAdded($sdg, $call_node, new UndefinedFuncNode($methodname, $classname));
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
				$classnames = $this->resolveClassNames($static_call->class);
				foreach ($classnames as $classname) {
					$nodes = $this->resolvePolymorphicMethodCall($state, $classname, $methodname, $pdg_func_lookup, true);
					if (empty($nodes) === false) {
						$this->ensureNodesAndCallEdgesAdded($sdg, $call_node, $nodes);
					} else {
						$this->ensureNodeAndCallEdgeAdded($sdg, $call_node, new UndefinedFuncNode($methodname, $classname));
					}
				}
			}
		}

		/** @var Func $pdg_func */
		foreach (array_merge($system->scripts, $system->functions, $system->methods, $system->closures) as $pdg_func) {
			$handledPropFetches = new \SplObjectStorage();
			$propFetchNodes = [];
			foreach ($pdg_func->pdg->getNodes() as $node) {
				$nodes = [];
				if ($node instanceof OpNode) {
					$op = $node->op;
					if ($op instanceof Isset_) {
						foreach ($op->vars as $var) {
							if ($var instanceof Operand\Temporary && isset($var->ops[0]) === true && $var->ops[0] instanceof PropertyFetch) {
								$fetch = $var->ops[0];
								$handledPropFetches->attach($fetch);
								if ($fetch->name instanceof Literal) {
									$classnames = $this->resolveClassNames($fetch->var);
									foreach ($classnames as $classname) {
										$nodes = array_merge($nodes, $this->resolvePolymorphicPropertyOverloadingIsset($state, $classname, strtolower($fetch->name->value), $pdg_func_lookup));
									}
								}
							}
						}
					} else if ($op instanceof Unset_) {
						foreach ($op->exprs as $expr) {
							if ($expr instanceof Operand\Temporary && isset($expr->ops[0]) === true && $expr->ops[0] instanceof PropertyFetch) {
								$fetch = $expr->ops[0];
								$handledPropFetches->attach($fetch);
								if ($fetch->name instanceof Literal) {
									$classnames = $this->resolveClassNames($fetch->var);
									foreach ($classnames as $classname) {
										$nodes = array_merge($nodes, $this->resolvePolymorphicPropertyOverloadingUnset($state, $classname, strtolower($fetch->name->value), $pdg_func_lookup));
									}
								}
							}
						}
					} else if ($op instanceof Assign || $op instanceof AssignRef || $op instanceof AssignOp) {
						if ($op->var instanceof Operand\Temporary && isset($op->var->ops[0]) === true && $op->var->ops[0] instanceof PropertyFetch) {
							$fetch = $op->var->ops[0];
							$handledPropFetches->attach($fetch);
							if ($fetch->name instanceof Literal) {
								$classnames = $this->resolveClassNames($fetch->var);
								foreach ($classnames as $classname) {
									$nodes = array_merge($nodes, $this->resolvePolymorphicPropertyOverloadingSet($state, $classname, strtolower($fetch->name->value), $pdg_func_lookup));
								}
							}
						}
					} else if ($op instanceof PropertyFetch) {
						$propFetchNodes[] = $node;
					}
				}

				if (empty($nodes) === false) {
					$sdg->addNode($node);
					$sdg->addEdge(new FuncNode($pdg_func), $node, ['type' => 'contains']);
					$this->ensureNodesAndCallEdgesAdded($sdg, $node, $nodes);
				}
			}

			foreach ($propFetchNodes as $node) {
				$nodes = [];
				$fetch = $node->op;
				if ($handledPropFetches->contains($fetch) === false) {
					if ($fetch->name instanceof Literal) {
						$classnames = $this->resolveClassNames($fetch->var);
						foreach ($classnames as $classname) {
							$nodes = array_merge($nodes, $this->resolvePolymorphicPropertyOverloadingGet($state, $classname, strtolower($fetch->name->value), $pdg_func_lookup));
						}
					}
				}

				if (empty($nodes) === false) {
					$sdg->addNode($node);
					$sdg->addEdge(new FuncNode($pdg_func), $node, ['type' => 'contains']);
					$this->ensureNodesAndCallEdgesAdded($sdg, $node, $nodes);
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

	private function resolveClassNames(Operand $operand) {
		$classnames = [];
		if (is_object($operand->type) === true && $operand->type instanceof Type) {
			/** @var Type $type */
			$type = $operand->type;
			if ($type->type === Type::TYPE_STRING) {
				if ($operand instanceof Literal) {
					$classnames[] = strtolower($operand->value);
				}
			} else {
				$classnames = array_merge($classnames, $this->resolveClassNamesFromUserTypes($type));
			}
		}
		return $classnames;
	}

	private function resolveClassNamesFromUserTypes(Type $type) {
		$classnames = [];
		switch ($type->type) {
			case Type::TYPE_OBJECT:
				if ($type->userType !== null) {
					$classnames[] = strtolower($type->userType);
				}
				break;
			case Type::TYPE_UNION:
				foreach ($type->subTypes as $subType) {
					$classnames = array_merge($classnames, $this->resolveClassNamesFromUserTypes($subType));
				}
				break;

		}
		return $classnames;
	}

	/**
	 * @param State $state
	 * @param string $classname
	 * @param string $methodname
	 * @param \SplObjectStorage $pdg_func_lookup
	 * @param bool $is_static_call
	 * @return NodeInterface[]
	 */
	private function resolvePolymorphicMethodCall(State $state, $classname, $methodname, \SplObjectStorage $pdg_func_lookup, $is_static_call) {
		$allnodes = [];
		if (isset($state->classResolvedBy[$classname]) === true) {
			foreach ($state->classResolvedBy[$classname] as $sclassname) {
				$classnodes = $this->resolveMethodCall($state, $sclassname, $methodname, $pdg_func_lookup);
				if (empty($classnodes) === false) {
					$allnodes = array_merge($allnodes, $classnodes);
				} else {
					$classnodes = $this->resolveMethodCall($state, $sclassname, $is_static_call === true ? '__callStatic' : '__call', $pdg_func_lookup);
					if (empty($classnodes) === false) {
						$allnodes = array_merge($allnodes, $classnodes);
					}
				}
			}
		}
		return $allnodes;
	}

	/**
	 * @param State $state
	 * @param string $classname
	 * @param string $methodname
	 * @param \SplObjectStorage $pdg_func_lookup
	 * @return NodeInterface[]
	 */
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
						/** @var FuncNode $funcnode */
						$funcnode = $pdg_func_lookup[$func];
						$nodes[$funcnode->getHash()] = $funcnode;
					}
				} else if ($class->extends !== null) {
					foreach ($this->resolveMethodCall($state, strtolower($class->extends->value), $methodname, $pdg_func_lookup) as $node) {
						$nodes[$node->getHash()] = $node;
					}
				}
			}
		}

		$node = $this->resolveBuiltinMethodCall($state->internalTypeInfo, $classname, $methodname);
		if ($node !== null) {
			$nodes[$node->getHash()] = $node;
		}

		return $nodes;
	}

	/**
	 * @param InternalArgInfo $internalArgInfo
	 * @param string $classname
	 * @param string $methodname
	 * @return null|BuiltinFuncNode
	 */
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

	/**
	 * @param State $state
	 * @param string $classname
	 * @param string $propname
	 * @param \SplObjectStorage|FuncNode[] $pdg_func_lookup
	 * @return NodeInterface[]
	 */
	private function resolvePolymorphicPropertyOverloadingIsset(State $state, $classname, $propname, $pdg_func_lookup) {
		$nodes = [];
		if (isset($state->classResolvedBy[$classname]) === true) {
			foreach ($state->classResolvedBy[$classname] as $sclassname) {
				if ($this->hasProperty($state, $sclassname, $propname) === false) {
					$nodes = array_merge($nodes, $this->resolveMethodCall($state, $sclassname, '__isset', $pdg_func_lookup));
				}
			}
		}
		return $nodes;
	}

	/**
	 * @param State $state
	 * @param string $classname
	 * @param string $propname
	 * @param \SplObjectStorage|FuncNode[] $pdg_func_lookup
	 * @return NodeInterface[]
	 */
	private function resolvePolymorphicPropertyOverloadingUnset(State $state, $classname, $propname, $pdg_func_lookup) {
		$nodes = [];
		if (isset($state->classResolvedBy[$classname]) === true) {
			foreach ($state->classResolvedBy[$classname] as $sclassname) {
				if ($this->hasProperty($state, $sclassname, $propname) === false) {
					$nodes = array_merge($nodes, $this->resolveMethodCall($state, $sclassname, '__unset', $pdg_func_lookup));
				}
			}
		}
		return $nodes;
	}

	/**
	 * @param State $state
	 * @param string $classname
	 * @param string $propname
	 * @param \SplObjectStorage|FuncNode[] $pdg_func_lookup
	 * @return NodeInterface[]
	 */
	private function resolvePolymorphicPropertyOverloadingSet(State $state, $classname, $propname, $pdg_func_lookup) {
		$nodes = [];
		if (isset($state->classResolvedBy[$classname]) === true) {
			foreach ($state->classResolvedBy[$classname] as $sclassname) {
				if ($this->hasProperty($state, $sclassname, $propname) === false) {
					$nodes = array_merge($nodes, $this->resolveMethodCall($state, $sclassname, '__set', $pdg_func_lookup));
				}
			}
		}
		return $nodes;
	}

	/**
	 * @param State $state
	 * @param string $classname
	 * @param string $propname
	 * @param \SplObjectStorage|FuncNode[] $pdg_func_lookup
	 * @return NodeInterface[]
	 */
	private function resolvePolymorphicPropertyOverloadingGet(State $state, $classname, $propname, $pdg_func_lookup) {
		$nodes = [];
		if (isset($state->classResolvedBy[$classname]) === true) {
			foreach ($state->classResolvedBy[$classname] as $sclassname) {
				if ($this->hasProperty($state, $sclassname, $propname) === false) {
					$nodes = array_merge($nodes, $this->resolveMethodCall($state, $sclassname, '__get', $pdg_func_lookup));
				}
			}
		}
		return $nodes;
	}

	/**
	 * @param State $state
	 * @param string $classname
	 * @param string $propname
	 * @return bool
	 */
	private function hasProperty(State $state, $classname, $propname) {
		if (isset($state->classLookup[$classname]) === true) {
			foreach ($state->classLookup[$classname] as $class) {
				if (isset($state->propertyLookup[$class][$propname]) === true) {
					return true;
				} else if ($class->extends !== null) {
					if ($this->hasProperty($state, strtolower($class->extends->value), $propname) === true) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * @param GraphInterface $sdg
	 * @param NodeInterface $source_node
	 * @param NodeInterface[] $target_nodes
	 */
	private function ensureNodesAndCallEdgesAdded(GraphInterface $sdg, $source_node, $target_nodes) {
		foreach ($target_nodes as $target_node) {
			$this->ensureNodeAndCallEdgeAdded($sdg, $source_node, $target_node);
		}
	}

	/**
	 * @param GraphInterface $sdg
	 * @param NodeInterface $source_node
	 * @param NodeInterface $target_node
	 */
	private function ensureNodeAndCallEdgeAdded(GraphInterface $sdg, $source_node, $target_node) {
		if ($sdg->hasNode($target_node) === false) {
			$sdg->addNode($target_node);
			$sdg->addEdge($source_node, $target_node, ['type' => 'call']);
		} else if ($sdg->hasEdges($source_node, $target_node, ['type' => 'call']) === false) {
			$sdg->addEdge($source_node, $target_node, ['type' => 'call']);
		}
	}
}