<?php

namespace PhpPdg\SystemDependence\Slicing;

use PhpPdg\Graph\Graph;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\Node\NodeInterface;
use PhpPdg\ProgramDependence\Func;
use PhpPdg\ProgramDependence\Node\OpNode;
use PhpPdg\SystemDependence\Node\FuncNode;
use PhpPdg\SystemDependence\System;

class BackwardSlicer implements SlicerInterface {
	/** @var  \SplObjectStorage|NodeInterface[][] */
	private $func_slicing_criterions;
	/** @var  \SplObjectStorage|Func[] */
	private $worklist;

	/**
	 * Create a backward slice of an SDG using a slicing criterion consisting of file path and line nr
	 *
	 * @param System $system
	 * @param string $slice_file_path
	 * @param int $slice_line_nr
	 * @return System
	 */
	public function slice(System $system, $slice_file_path, $slice_line_nr) {
		$this->func_slicing_criterions = new \SplObjectStorage();
		$this->worklist = new \SplObjectStorage();
		$sliced_pdgs = new \SplObjectStorage();

		// set initial slicing criterion by matching file an line number
		foreach ($system->getFuncs() as $func) {
			if ($func->filename === $slice_file_path) {
				foreach ($func->pdg->getNodes() as $node) {
					if ($node instanceof OpNode && $node->op->getLine() === $slice_line_nr) {
						$this->updateFuncSlicingCriterion($func, [$node->getHash() => $node]);
					}
				}
			}
		}

		// iteratively slice and compute UP and DOWN for each procedure
		while ($this->worklist->count() > 0) {
			foreach ($this->worklist as $func) {
				$this->worklist->detach($func);
				$sliced_pdgs[$func] = $sliced_pdg = Graph::reachableInv($func->pdg, $this->func_slicing_criterions[$func]);

				// UP - all procedures calling this function
				foreach ($system->sdg->getEdges(null, new FuncNode($func), ['type' => 'call']) as $incoming_call_edge) {
					$call_node = $incoming_call_edge->getFromNode();
					$contains_edges = $system->sdg->getEdges(null, $call_node, ['type' => 'contains']);
					assert(count($contains_edges) === 1);
					/** @var FuncNode $containing_func_node */
					$containing_func_node = $contains_edges[0]->getFromNode();
					assert($containing_func_node instanceof FuncNode);
					$this->updateFuncSlicingCriterion($containing_func_node->getFunc(), [$call_node->getHash() => $call_node]);
				}

				// DOWN - all procedures called by this functions
				foreach ($sliced_pdg->getNodes() as $node) {
					if ($system->sdg->hasNode($node) === true) {
						foreach ($system->sdg->getEdges($node, null, ['type' => 'call']) as $outgoing_call_edge) {
							$func_node = $outgoing_call_edge->getToNode();
							if ($func_node instanceof FuncNode) {
								$this->updateFuncSlicingCriterion($func_node->getFunc(), $func_node->getFunc()->return_nodes);
							}
						}
					}
				}
			}
		}

		$sliced_system = new System($system->sdg);
		$sliced_system->scripts = $this->getSlicedFuncs($system->scripts, $sliced_pdgs);
		$sliced_system->functions = $this->getSlicedFuncs($system->functions, $sliced_pdgs);
		$sliced_system->methods = $this->getSlicedFuncs($system->methods, $sliced_pdgs);
		$sliced_system->closures = $this->getSlicedFuncs($system->closures, $sliced_pdgs);
		return $sliced_system;
	}

	private function updateFuncSlicingCriterion(Func $func, array $new_func_slicing_criterion) {
		if (isset($this->func_slicing_criterions[$func]) === true) {
			$existing_func_slicing_criterion = $this->func_slicing_criterions[$func];
			$new_func_slicing_criterion = array_merge($existing_func_slicing_criterion, $new_func_slicing_criterion);
			if (count($new_func_slicing_criterion) === count($existing_func_slicing_criterion)) {
				return; // no update, so no need to recompute
			}
		}
		$this->func_slicing_criterions[$func] = $new_func_slicing_criterion;
		$this->worklist->attach($func);
	}

	/**
	 * @param Func[] $funcs
	 * @param \SplObjectStorage|GraphInterface[] $sliced_pdgs
	 * @return array
	 */
	private function getSlicedFuncs($funcs, \SplObjectStorage $sliced_pdgs) {
		$result = [];
		foreach ($funcs as $i => $func) {
			if (isset($sliced_pdgs[$func]) === true) {
				$sliced_pdg = $sliced_pdgs[$func];
				$result[$i] = $sliced_func = new Func($func->name, $func->class_name, $func->filename, $func->entry_node, $sliced_pdg);
				foreach ($func->param_nodes as $param_node) {
					if ($sliced_pdg->hasNode($param_node) === true) {
						$sliced_func->param_nodes[$param_node->getHash()] = $param_node;
					}
				}
				foreach ($func->return_nodes as $return_node) {
					if ($sliced_pdg->hasNode($return_node) === true) {
						$sliced_func->return_nodes[$return_node->getHash()] = $return_node;
					}
				}
			}
		}
		return $result;
	}
}