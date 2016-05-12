<?php

namespace PhpPdg;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\OpNode;

class GraphInitializationVisitor extends AbstractVisitor {
	private $func;
	/** @var GraphInterface */
	private $graph;

	public function __construct(Func $func, GraphInterface $graph) {
		$this->func = $func;
		$this->graph = $graph;
	}

	public function enterOp(Op $op, Block $block) {
		$op_node = new OpNode($op);
		$this->graph->addNode($op_node);
		if ($op instanceof Op\Terminal\Return_) {
			$this->func->return_nodes[] = $op_node;
		}
	}
}