<?php

namespace PhpPdg;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\Nodes\OpNode;

class FuncInitializationVisitor extends AbstractVisitor {
	private $func;

	public function __construct(Func $func) {
		$this->func = $func;
	}

	public function enterOp(Op $op, Block $block) {
		$op_node = new OpNode($op);
		$this->func->dependence_graph->addNode($op_node);
		if ($op instanceof Op\Terminal\Return_) {
			$this->func->return_nodes[] = $op_node;
		}
	}
}