<?php

namespace PhpPdg\BaseGraph;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\OpNode;

class GeneratingVisitor extends AbstractVisitor {
	/** @var GraphInterface */
	private $graph;

	public function __construct(GraphInterface $graph) {
		$this->graph = $graph;
	}

	public function enterOp(Op $op, Block $block) {
		$this->graph->addNode(new OpNode($op));
	}
}