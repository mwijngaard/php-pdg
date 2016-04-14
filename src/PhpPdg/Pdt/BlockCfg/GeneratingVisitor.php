<?php

namespace PhpPdg\Pdt\BlockCfg;

use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\CfgAdapter\BaseVisitor;
use PhpPdg\Graph\Graph;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\BlockNode;

class GeneratingVisitor extends BaseVisitor {
	/** @var GraphInterface */
	public $graph;
	/** @var  \SplObjectStorage */
	private $node_cache;

	public function __construct(GraphInterface $graph) {
		$this->graph = $graph;
	}

	public function beforeTraverse() {
		$this->graph->clear();
		$this->node_cache = new \SplObjectStorage();
	}

	public function skipBlock(Block $block, Block $prior = null) {
		$this->graph->addEdge(new BlockNode($prior), $this->getBlockNode($block));
	}

	public function enterBlock(Block $block, Block $prior = null) {
		$block_node = $this->getBlockNode($block);
		$this->graph->addNode($block_node);
		if ($prior !== null) {
			$this->graph->addEdge($this->getBlockNode($prior), $block_node);
		}
	}

	private function getBlockNode(Block $block) {
		if (isset($this->node_cache[$block]) === false) {
			$this->node_cache[$block] = new BlockNode($block);
		}
		return $this->node_cache[$block];
	}
}