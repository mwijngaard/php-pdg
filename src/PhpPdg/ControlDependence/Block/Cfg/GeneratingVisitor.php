<?php

namespace PhpPdg\ControlDependence\Block\Cfg;

use PHPCfg\Block;
use PhpPdg\CfgAdapter\BaseVisitor;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\BlockNode;

class GeneratingVisitor extends BaseVisitor {
	/** @var GraphInterface */
	public $graph;

	public function __construct(GraphInterface $graph) {
		$this->graph = $graph;
	}

	public function skipBlock(Block $block, Block $prior = null) {
		$this->graph->addEdge(new BlockNode($prior), new BlockNode($block));
	}

	public function enterBlock(Block $block, Block $prior = null) {
		$block_node = new BlockNode($block);
		$this->graph->addNode($block_node);
		if ($prior !== null) {
			$this->graph->addEdge(new BlockNode($prior), $block_node);
		}
	}
}