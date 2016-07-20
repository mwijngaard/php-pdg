<?php

namespace PhpPdg\SystemDependence\Slicing;

use PHPCfg\Op\Expr\FuncCall;
use PHPCfg\Op\Expr\MethodCall;
use PHPCfg\Op\Expr\NsFuncCall;
use PHPCfg\Op\Expr\StaticCall;
use PhpPdg\Graph\Edge;
use PhpPdg\Graph\Graph;
use PhpPdg\Graph\Node\NodeInterface;
use PhpPdg\ProgramDependence\Func;
use PhpPdg\ProgramDependence\Node\OpNode;
use PhpPdg\SystemDependence\Node\FuncNode;
use PhpPdg\SystemDependence\System;

class ForwardSlicer implements SlicerInterface {
	/**
	 * Create a forward slice of an SDG using a slicing criterion consisting of file path and line nr
	 *
	 * @param System $system
	 * @param string $slice_file_path
	 * @param int $slice_line_nr
	 * @return System
	 */
	public function slice(System $system, $slice_file_path, $slice_line_nr) {
		$func_slicing_criterions = new \SplObjectStorage();
		$sdg_slicing_criterion = [];
		foreach ($system->getFuncs() as $func) {
			$func_slicing_criterion = [];
			if ($func->filename === $slice_file_path) {
				foreach ($func->pdg->getNodes() as $node) {
					if ($node instanceof OpNode && $node->op->getLine() === $slice_line_nr && isset($func_slicing_criterion[$node->getHash()]) === false) {
						$func_slicing_criterion[$node->getHash()] = $node;
					}
				}
			}
			if (empty($func_slicing_criterion) === false) {
				$func_slicing_criterions[$func] = $func_slicing_criterion;
				$sliced_pdg = Graph::reachable($func->pdg, $func_slicing_criterion);
				foreach ($sliced_pdg->getNodes() as $node) {
					if ($node instanceof OpNode) {
						$op = $node->op;
						if (($op instanceof FuncCall || $node instanceof NsFuncCall || $node instanceof MethodCall || $node instanceof StaticCall)) {
							$sdg_slicing_criterion[] = $node;
						}
					}
				}
				break;
			}
		}
		$sliced_sdg = Graph::reachable($system->sdg, $sdg_slicing_criterion);
		$sliced_system = new System($sliced_sdg);
		foreach ($sliced_sdg->getNodes() as $node) {
			if ($node instanceof FuncNode) {
				$func = $node->getFunc();
				$func_slicing_criterions[$func] = [$func->entry_node];
			}
		}
		$sliced_system->scripts = $this->sliceFuncs($system->scripts, $func_slicing_criterions);
		$sliced_system->functions = $this->sliceFuncs($system->functions, $func_slicing_criterions);
		$sliced_system->methods = $this->sliceFuncs($system->methods, $func_slicing_criterions);
		$sliced_system->closures = $this->sliceFuncs($system->closures, $func_slicing_criterions);
		return $sliced_system;
	}

	/**
	 * @param Func[] $funcList
	 * @param \SplObjectStorage|NodeInterface[] $func_slicing_criterions
	 * @return Func[]
	 */
	private function sliceFuncs($funcList, $func_slicing_criterions) {
		$result = [];
		foreach ($funcList as $key => $func) {
			if (isset($func_slicing_criterions[$func]) === true) {
				$sliced_pdg = Graph::reachable($func->pdg, $func_slicing_criterions[$func]);
				$result[$key] = new Func($func->name, $func->class_name, $func->filename, $func->entry_node, $sliced_pdg);
			}
		}
		return $result;
	}
}