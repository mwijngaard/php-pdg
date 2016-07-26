<?php

namespace PhpPdg\ProgramDependence\DataDependence;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PHPCfg\Op\Phi;
use PHPCfg\Operand;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\Node\NodeInterface;
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
		foreach ($op->getVariableNames() as $variableName) {
			if ($op->isWriteVariable($variableName) === true) {
				continue;
			}

			$operand = $op->$variableName;
			if ($operand === null) {
				continue;
			}

			if (is_array($operand) === true) {
				foreach ($operand as $i => $arrayOperand) {
					if ($arrayOperand !== null) {
						$writeOps = $this->resolveOperandWriteOps($arrayOperand);
						$this->addDataDependenceEdges($op_node, $writeOps, $variableName . ':' . $i);
					}
				}
			} else {
				$writeOps = $this->resolveOperandWriteOps($operand);
				$this->addDataDependenceEdges($op_node, $writeOps, $variableName);
			}
		}
	}

	private function resolveOperandWriteOps(Operand $operand) {
		$result = new \SplObjectStorage();
		$seenPhis = new \SplObjectStorage();

		$worklist = [$operand];
		while (!empty($worklist)) {
			$operand = array_shift($worklist);

			foreach ($operand->ops as $writeOp) {
				if ($writeOp instanceof Phi) {
					if ($seenPhis->contains($writeOp) === false) {
						$seenPhis->attach($writeOp);
						$worklist = array_merge($worklist, $writeOp->vars);
					}
				} else if ($result->contains($writeOp) === false) {
					$result->attach($writeOp);
				}
			}
		}
		return $result;
	}

	/**
	 * @param NodeInterface $opNode
	 * @param \SplObjectStorage|Op[] $writeOps
	 * @param $operandPath
	 */
	private function addDataDependenceEdges(NodeInterface $opNode, \SplObjectStorage $writeOps, $operandPath) {
		foreach ($writeOps as $writeOp) {
			$writeOpNode = new OpNode($writeOp);
			$this->target_graph->addEdge($writeOpNode, $opNode, [
				'type' => $this->edge_type,
				'operand' => $operandPath
			]);
		}
	}
}