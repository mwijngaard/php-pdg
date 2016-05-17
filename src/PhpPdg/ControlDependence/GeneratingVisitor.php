<?php

namespace PhpPdg\ControlDependence;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\BlockNode;
use PhpPdg\Nodes\OpNode;

class GeneratingVisitor extends AbstractVisitor {
	/** @var GraphInterface  */
	private $target_graph;
	/** @var GraphInterface  */
	private $block_cdg;
	/** @var  string */
	private $edge_type;

	/**
	 * GeneratingVisitor constructor.
	 * @param GraphInterface $target_graph
	 * @param GraphInterface $block_cdg
	 * @param string $edge_type
	 */
	public function __construct(GraphInterface $target_graph, GraphInterface $block_cdg, $edge_type) {
		$this->target_graph = $target_graph;
		$this->block_cdg = $block_cdg;
		$this->edge_type = $edge_type;
	}

	public function enterOp(Op $op, Block $block) {
		$op_node = new OpNode($op);
		foreach ($this->block_cdg->getEdges(new BlockNode($block)) as $edge) {
			$to_node = $edge->getToNode();
			if ($to_node instanceof BlockNode) {
				$block_children = $to_node->block->children;
				$last_child = $block_children[count($block_children) - 1];
				$to_node = new OpNode($last_child);
			}
			$this->target_graph->addEdge($op_node, $to_node, array(
				'type' => $this->edge_type,
				'case' => $edge->getAttributes()['case'],
			));
		}
	}
}