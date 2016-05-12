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
		foreach ($this->block_cdg->getOutgoingEdgeNodes(new BlockNode($block)) as $node) {
			if ($node instanceof BlockNode) {
				$block_children = $node->block->children;
				$last_child = $block_children[count($block_children) - 1];
				$node = new OpNode($last_child);
			}
			$this->target_graph->addEdge($op_node, $node, $this->edge_type);
		}
	}
}