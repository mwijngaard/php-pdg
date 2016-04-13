<?php

namespace PhpPdg\Pdt\BlockCfg;

use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\CfgAdapter\BaseVisitor;
use PhpPdg\Graph\Graph;

class GeneratingVisitor extends BaseVisitor {
	/** @var  Graph */
	public $graph;

	public function beforeTraverse() {
		$this->graph = new Graph();
	}

	public function skipBlock(Block $block, Block $prior = null) {
		$this->graph->addEdge($prior, $block);
	}

	public function enterBlock(Block $block, Block $prior = null) {
		$this->graph->addNode($block);
		if ($prior !== null) {
			$this->graph->addEdge($prior, $block);
		}
	}
}