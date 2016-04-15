<?php

namespace PhpPdg\DataDependence;

use PHPCfg\Block;
use PHPCfg\Op;
use PHPCfg\Op\Phi;
use PHPCfg\Operand;
use PhpPdg\CfgAdapter\BaseVisitor;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\OpNode;

class GeneratingVisitor extends BaseVisitor {
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
		foreach ($this->resolveOpDataDependences($op) as $write_op) {
			$write_op_node = new OpNode($write_op);
			$this->target_graph->addEdge($op_node, $write_op_node, $this->edge_type);
		}
	}

	private function resolveOpDataDependences(Op $op) {
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

			if (is_array($operand) === true) {
				foreach ($operand as $operand_entry) {
					$write_ops = array_merge($write_ops, $this->resolveOperandDataDependences($operand_entry));
				}
			} else {
				$write_ops = array_merge($write_ops, $this->resolveOperandDataDependences($operand));
			}
		}
		return $write_ops;
	}

	private function resolveOperandDataDependences(Operand $operand) {
		$write_ops = [];
		foreach ($operand->ops as $write_op) {
			if ($write_op instanceof Phi) {
				$write_ops = array_merge($write_ops, $this->resolveOpDataDependences($write_op));
			} else {
				$write_ops[] = $write_op;
			}
		}
		return $write_ops;
	}
}