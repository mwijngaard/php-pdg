<?php

namespace PhpPdg\SystemDependence\CallDependence;

use PHPCfg\Op\Expr\Assign;
use PHPCfg\Op\Expr\AssignRef;
use PHPCfg\Op\Expr\Isset_;
use PHPCfg\Op\Terminal\Unset_;
use PHPCfg\Operand;
use PHPCfg\Op\Expr\PropertyFetch;
use PhpPdg\Graph\Graph;
use PhpPdg\Graph\Node\NodeInterface;
use PhpPdg\ProgramDependence\Func;
use PhpPdg\ProgramDependence\Node\OpNode;
use PhpPdg\SystemDependence\Node\FuncNode;
use PhpPdg\SystemDependence\System;
use PHPTypes\State;

class OverloadingCallGenerator implements GeneratorInterface {
	/** @var OperandClassResolverInterface */
	private $operand_class_resolver;
	/** @var MethodResolverInterface */
	private $method_resolver;

	public function __construct(OperandClassResolverInterface $operand_class_resolver, MethodResolverInterface $method_resolver) {
		$this->operand_class_resolver = $operand_class_resolver;
		$this->method_resolver = $method_resolver;
	}

	public function addSystemCallDependences(System $system, State $state, \SplObjectStorage $pdg_func_lookup) {
		$sdg = $system->sdg;
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
								if ($fetch->name instanceof Operand\Literal) {
									$classnames = $this->operand_class_resolver->resolveClassNames($fetch->var);
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
								if ($fetch->name instanceof Operand\Literal) {
									$classnames = $this->operand_class_resolver->resolveClassNames($fetch->var);
									foreach ($classnames as $classname) {
										$nodes = array_merge($nodes, $this->resolvePolymorphicPropertyOverloadingUnset($state, $classname, strtolower($fetch->name->value), $pdg_func_lookup));
									}
								}
							}
						}
					} else if ($op instanceof Assign || $op instanceof AssignRef) {
						if ($op->var instanceof Operand\Temporary && isset($op->var->ops[0]) === true && $op->var->ops[0] instanceof PropertyFetch) {
							$fetch = $op->var->ops[0];
							$handledPropFetches->attach($fetch);
							if ($fetch->name instanceof Operand\Literal) {
								$classnames = $this->operand_class_resolver->resolveClassNames($fetch->var);
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
					Graph::ensureNodesAndEdgesAdded($sdg, $node, $nodes, ['type' => 'call']);
				}
			}

			foreach ($propFetchNodes as $node) {
				$nodes = [];
				$fetch = $node->op;
				if ($handledPropFetches->contains($fetch) === false) {
					if ($fetch->name instanceof Operand\Literal) {
						$classnames = $this->operand_class_resolver->resolveClassNames($fetch->var);
						foreach ($classnames as $classname) {
							$nodes = array_merge($nodes, $this->resolvePolymorphicPropertyOverloadingGet($state, $classname, strtolower($fetch->name->value), $pdg_func_lookup));
						}
					}
				}

				if (empty($nodes) === false) {
					$sdg->addNode($node);
					$sdg->addEdge(new FuncNode($pdg_func), $node, ['type' => 'contains']);
					Graph::ensureNodesAndEdgesAdded($sdg, $node, $nodes, ['type' => 'call']);
				}
			}
		}
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
					$nodes = array_merge($nodes, $this->method_resolver->resolveMethod($state, $sclassname, '__isset', $pdg_func_lookup));
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
					$nodes = array_merge($nodes, $this->method_resolver->resolveMethod($state, $sclassname, '__unset', $pdg_func_lookup));
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
					$nodes = array_merge($nodes, $this->method_resolver->resolveMethod($state, $sclassname, '__set', $pdg_func_lookup));
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
					$nodes = array_merge($nodes, $this->method_resolver->resolveMethod($state, $sclassname, '__get', $pdg_func_lookup));
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
}