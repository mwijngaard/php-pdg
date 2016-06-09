<?php

namespace PhpPdg\ProgramDependence\DataDependence;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PHPCfg\Op\Phi;
use PHPCfg\Operand;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\ProgramDependence\Node\OpNode;

class GeneratingVisitor extends AbstractVisitor {
	/** @var GraphInterface  */
	private $target_graph;
	/** @var string  */
	private $edge_type;

	/**
	 * GeneratingVisitor constructor.
	 * @param GraphInterface $target_graph
	 * @param string $edge_type
	 */
	public function __construct(GraphInterface $target_graph, $edge_type) {
		$this->target_graph = $target_graph;
		$this->edge_type = $edge_type;
	}

	public function enterOp(Op $op, Block $block) {
		$op_node = new OpNode($op);
		$op_data_dependences_ops = new \SplObjectStorage();
		$this->addOpDataDependenceOps($op, $op_data_dependences_ops, new \SplObjectStorage());
		foreach ($op_data_dependences_ops as $op_data_dependence_op) {
			$write_op_node = new OpNode($op_data_dependence_op);
			$attributes = [
				'type' => $this->edge_type
			];
			$this->target_graph->addEdge($op_node, $write_op_node, $attributes);
		}
	}

	private function addOpDataDependenceOps(Op $op, \SplObjectStorage $result, \SplObjectStorage $seen_phis) {
		foreach ($op->getVariableNames() as $variable_name) {
			// since the CFG is in SSA form, we only need to look at non-write variables
			if ($op->isWriteVariable($variable_name) === true) {
				continue;
			}

			/** @var Operand $operand */
			$operand = $op->$variable_name;
			if ($operand === null) {
				continue;
			}

			$this->addOperandDataDependenceOps($operand, $result, $seen_phis);
		}
	}

	/**
	 * @param Operand|array $operand
	 * @return array
	 */
	private function addOperandDataDependenceOps($operand, \SplObjectStorage $result, \SplObjectStorage $seen_phis) {
		if (is_null($operand) === false) {
			if (is_array($operand) === true) {
				foreach ($operand as $operand_entry) {
					$this->addOperandDataDependenceOps($operand_entry, $result, $seen_phis);
				}
			} else {
				foreach ($operand->ops as $write_op) {
					if ($write_op instanceof Phi) {
						if ($seen_phis->contains($write_op) === false) {
							$seen_phis->attach($write_op);
							$this->addOpDataDependenceOps($write_op, $result, $seen_phis);
						}
					} else {
						$result->attach($write_op);
					}
				}
			}
		}
	}
}