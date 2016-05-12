<?php

namespace PhpPdg\ControlDependence\Block\Cfg;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;
use PhpPdg\Nodes\BlockNode;

class GeneratingVisitor extends AbstractVisitor {
	/** @var GraphInterface */
	public $graph;
	/** @var  NodeInterface */
	public $entry_node;
	/** @var  NodeInterface */
	public $stop_node;
	/** @var  Block */
	private $last_block_seen;

	public function __construct(GraphInterface $graph, NodeInterface $entry_node, NodeInterface $stop_node) {
		$this->graph = $graph;
		$this->entry_node = $entry_node;
		$this->stop_node = $stop_node;
	}

	public function skipBlock(Block $block, Block $prior = null) {
		$this->last_block_seen = $block;
		$this->graph->addEdge(new BlockNode($prior), new BlockNode($block));
	}

	public function enterBlock(Block $block, Block $prior = null) {
		$this->last_block_seen = $block;
		$block_node = new BlockNode($block);
		$this->graph->addNode($block_node);
		$this->graph->addEdge($prior === null ? $this->entry_node : new BlockNode($prior), $block_node);
	}

	public function leaveBlock(Block $block, Block $prior = null) {
		if ($this->last_block_seen === $block) {
			$this->graph->addEdge(new BlockNode($block), $this->stop_node);
		}
	}
}