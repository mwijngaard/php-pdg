<?php

namespace PhpPdg;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\OpNode;

class InitializingVisitor extends AbstractVisitor {
	private $graph;

	public function __construct(GraphInterface $graph) {
		$this->graph = $graph;
	}

	public function enterOp(Op $op, Block $block) {
		$op_node = new OpNode($op);
		$this->graph->addNode($op_node);
	}
}