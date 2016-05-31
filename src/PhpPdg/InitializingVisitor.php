<?php

namespace PhpPdg;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\OpNode;
use PHPCfg\Func as CfgFunc;

class InitializingVisitor extends AbstractVisitor {
	private $graph;
	private $func;

	public function __construct(GraphInterface $graph, Func $func) {
		$this->graph = $graph;
		$this->func = $func;
	}

	public function enterFunc(CfgFunc $func) {
		foreach ($func->params as $param) {
			$param_node = new OpNode($param);
			$this->graph->addNode($param_node);
			$this->func->param_nodes[] = $param_node;
		}
	}

	public function enterOp(Op $op, Block $block) {
		$op_node = new OpNode($op);
		$this->graph->addNode($op_node);
		if ($op instanceof Op\Terminal\Return_) {
			assert(!isset($this->func->return_nodes[$op_node->getHash()]));
			$this->func->return_nodes[$op_node->getHash()] = $op_node;
		}
	}
}