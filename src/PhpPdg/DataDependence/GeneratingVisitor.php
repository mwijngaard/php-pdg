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
	private $graph;
	/** @var string  */
	private $edge_type;

	public function __construct(GraphInterface $graph, $edge_type = '') {
		$this->graph = $graph;
		$this->edge_type = $edge_type;
	}

	public function enterOp(Op $op, Block $block) {
		$op_node = new OpNode($op);
		foreach ($this->resolveWriteOps($op) as $write_op) {
			$write_op_node = new OpNode($write_op);
			if ($this->graph->hasEdge($op_node, $write_op_node, $this->edge_type) === false) {
				$this->graph->addEdge($op_node, $write_op_node, $this->edge_type);
			}
		}
	}

	private function resolveWriteOps(Op $op) {
		$write_ops = [];
		foreach ($op->getVariableNames() as $variable_name) {
			/** @var Operand $operand */
			$operand = $op->$variable_name;
			if ($operand !== null) {
				foreach ($operand->ops as $write_op) {
					if ($op !== $write_op) {
						if ($write_op instanceof Phi) {
							$write_ops = array_merge($write_ops, $this->resolveWriteOps($write_op));
						} else {
							$write_ops[] = $write_op;
						}
					}
				}
			}
		}
		return $write_ops;
	}
}