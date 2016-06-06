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
		foreach ($this->resolveOpDataDependences($op, new \SplObjectStorage()) as $write_op) {
			$write_op_node = new OpNode($write_op);
			$this->target_graph->addEdge($op_node, $write_op_node, [
				'type' => $this->edge_type
			]);
		}
	}

	private function resolveOpDataDependences(Op $op, \SplObjectStorage $seen_phis) {
		$write_ops = [];
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

			$write_ops = array_merge($write_ops, $this->resolveOperandDataDependences($operand, $seen_phis));
		}
		return $write_ops;
	}

	/**
	 * @param Operand|array $operand
	 * @return array
	 */
	private function resolveOperandDataDependences($operand, \SplObjectStorage $seen_phis) {
		$write_ops = [];
		if (is_null($operand) === false) {
			if (is_array($operand) === true) {
				foreach ($operand as $operand_entry) {
					$write_ops = array_merge($write_ops, $this->resolveOperandDataDependences($operand_entry, $seen_phis));
				}
			} else {
				foreach ($operand->ops as $write_op) {
					if ($write_op instanceof Phi) {
						if ($seen_phis->contains($write_op) === false) {
							$seen_phis->attach($write_op);
							$write_ops = array_merge($write_ops, $this->resolveOpDataDependences($write_op, $seen_phis));
						}
					} else {
						$write_ops[] = $write_op;
					}
				}
			}
		}
		return $write_ops;
	}
}