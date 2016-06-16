<?php

namespace PhpPdg\SystemDependence\Slicing;

use PhpPdg\Graph\Edge;
use PhpPdg\Graph\Graph;
use PhpPdg\Graph\Node\NodeInterface;
use PhpPdg\ProgramDependence\Func;
use PhpPdg\ProgramDependence\Node\OpNode;
use PhpPdg\SystemDependence\Node\FuncNode;
use PhpPdg\SystemDependence\System;

class BackwardSlicer implements SlicerInterface {
	/**
	 * Create a backward slice of an SDG using a slicing criterion consisting of file path and line nr
	 *
	 * @param System $system
	 * @param string $slice_file_path
	 * @param int $slice_line_nr
	 * @return System
	 */
	public function slice(System $system, $slice_file_path, $slice_line_nr) {
		$func_slicing_criterions = new \SplObjectStorage();
		$funcs_seen = new \SplObjectStorage();
		$sdg_slicing_criterion = [];
		foreach ($system->getFuncs() as $func) {
			$func_slicing_criterion = [];
			if ($func->filename === $slice_file_path) {
				foreach ($func->pdg->getNodes() as $node) {
					if ($node instanceof OpNode && $node->op->getLine() === $slice_line_nr) {
						$func_slicing_criterion[] = $node;
					}
				}
			}
			if (empty($func_slicing_criterion) === false) {
				$func_slicing_criterions[$func] = $func_slicing_criterion;
				$funcs_seen->attach($func);
				$func_worklist[] = $func;
				$sdg_slicing_criterion[] = new FuncNode($func);
			}
		}
		$sliced_sdg = Graph::reachableInv($system->sdg, $sdg_slicing_criterion);
		foreach ($sliced_sdg->getNodes() as $node) {
			if ($node instanceof OpNode) {
				/** @var Edge[] $contains_edges */
				$contains_edges = $sliced_sdg->getEdges(null, $node, [
					'type' => 'contains'
				]);
				assert(count($contains_edges) === 1);
				/** @var FuncNode $containing_func_node */
				$containing_func_node = $contains_edges[0]->getFromNode();
				assert($containing_func_node instanceof FuncNode);
				$containing_func = $containing_func_node->getFunc();
				$func_slicing_criterion = isset($func_slicing_criterions[$containing_func]) === true ? $func_slicing_criterions[$containing_func] : [];
				$func_slicing_criterion[] = $node;
				$func_slicing_criterions[$containing_func] = $func_slicing_criterion;
			}
		}
		$sliced_system = new System($sliced_sdg);
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
				$sliced_pdg = Graph::reachableInv($func->pdg, $func_slicing_criterions[$func]);
				$result[$key] = new Func($func->name, $func->class_name, $func->filename, $func->entry_node, $sliced_pdg);
			}
		}
		return $result;
	}
}