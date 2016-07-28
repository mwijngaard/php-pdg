<?php

namespace PhpPdg\ProgramDependence\ControlDependence;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\ProgramDependence\Node\BlockNode;
use PhpPdg\ProgramDependence\Node\OpNode;

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
		$block_node = new BlockNode($block);
		if ($this->block_cdg->hasNode($block_node) === true) {
			foreach ($this->block_cdg->getEdges(null, $block_node) as $edge) {
				$from_node = $edge->getFromNode();
				if ($from_node instanceof BlockNode) {
					$block_children = $from_node->block->children;
					$last_child = $block_children[count($block_children) - 1];
					$from_node = new OpNode($last_child);
				}
				$this->target_graph->addEdge($from_node, $op_node, array_merge($edge->getAttributes(), [
					'type' => $this->edge_type,
				]));
			}
		}
	}
}