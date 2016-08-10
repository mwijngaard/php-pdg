<?php

namespace PhpPdg\SystemDependence\CallDependence;

use PHPCfg\Op\Expr\MethodCall;
use PHPCfg\Op\Expr\StaticCall;
use PHPCfg\Operand\Literal;
use PhpPdg\Graph\Graph;
use PhpPdg\Graph\Node\NodeInterface;
use PhpPdg\ProgramDependence\Node\OpNode;
use PhpPdg\SystemDependence\Node\UndefinedFuncNode;
use PhpPdg\SystemDependence\System;
use PHPTypes\State;

class MethodCallGenerator implements GeneratorInterface {
	/** @var OperandClassResolverInterface */
	private $operand_class_resolver;
	/** @var MethodResolverInterface */
	private $method_resolver;

	public function __construct(OperandClassResolverInterface $operand_class_resolver, MethodResolverInterface $method_resolver) {
		$this->operand_class_resolver = $operand_class_resolver;
		$this->method_resolver = $method_resolver;
	}

	public function addCallDependencesToSystem(System $system, State $state, \SplObjectStorage $pdg_func_lookup) {
		$sdg = $system->sdg;

		foreach ($state->methodCalls as $methodCallPair) {
			/** @var MethodCall $method_call */
			list($method_call, $containing_cfg_func) = $methodCallPair;
			$call_node = new OpNode($method_call);
			$system->sdg->addNode($call_node);
			assert(isset($pdg_func_lookup[$containing_cfg_func]));
			$system->sdg->addEdge($pdg_func_lookup[$containing_cfg_func], $call_node, ['type' => 'contains']);

			if ($method_call->name instanceof Literal) {
				$methodname = strtolower($method_call->name->value);
				$classnames = $this->operand_class_resolver->resolveClassNames($method_call->var);
				foreach ($classnames as $classname) {
					$nodes = $this->resolvePolymorphicMethodCall($state, $classname, $methodname, $pdg_func_lookup, false);
					if (empty($nodes) === false) {
						Graph::ensureNodesAndEdgesAdded($sdg, $call_node, $nodes, ['type' => 'call']);
					} else {
						Graph::ensureNodeAndEdgeAdded($sdg, $call_node, new UndefinedFuncNode($methodname, $classname), ['type' => 'call']);
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
				$classnames = $this->operand_class_resolver->resolveClassNames($static_call->class);
				foreach ($classnames as $classname) {
					$nodes = $this->resolvePolymorphicMethodCall($state, $classname, $methodname, $pdg_func_lookup, true);
					if (empty($nodes) === false) {
						Graph::ensureNodesAndEdgesAdded($sdg, $call_node, $nodes, ['type' => 'call']);
					} else {
						Graph::ensureNodeAndEdgeAdded($sdg, $call_node, new UndefinedFuncNode($methodname, $classname), ['type' => 'call']);
					}
				}
			}
		}
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
				$classnodes = $this->method_resolver->resolveMethod($state, $sclassname, $methodname, $pdg_func_lookup);
				if (empty($classnodes) === false) {
					$allnodes = array_merge($allnodes, $classnodes);
				} else {
					$classnodes = $this->method_resolver->resolveMethod($state, $sclassname, $is_static_call === true ? '__callStatic' : '__call', $pdg_func_lookup);
					if (empty($classnodes) === false) {
						$allnodes = array_merge($allnodes, $classnodes);
					}
				}
			}
		}
		return $allnodes;
	}
}