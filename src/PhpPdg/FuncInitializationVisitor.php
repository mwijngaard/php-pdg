<?php

namespace PhpPdg;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\Nodes\OpNode;
use PhpPdg\Program\Program;

class FuncInitializationVisitor extends AbstractVisitor {
	private $program;

	public function __construct(Program $program) {
		$this->program = $program;
	}

	public function enterOp(Op $op, Block $block) {
		$op_node = new OpNode($op);
		$this->program->dependence_graph->addNode($op_node);
		if ($op instanceof Op\Terminal\Return_) {
			$this->program->return_nodes[] = $op_node;
		} else if (self::isCall($op) === true) {
			$this->program->call_nodes[] = $op_node;
		} else if ($op instanceof Op\Expr\Closure) {
			$this->program->closure_nodes[] = $op_node;
		}
	}

	private static function isCall(Op $op) {
		return $op instanceof Op\Expr\FuncCall
			|| $op instanceof Op\Expr\MethodCall
			|| $op instanceof Op\Expr\StaticCall
			|| $op instanceof Op\Expr\NsFuncCall;
	}
}