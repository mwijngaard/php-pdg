<?php

namespace PhpPdg\ProgramDependence\Node;

use PHPCfg\Op;
use PhpPdg\Graph\Node\AbstractNode;

class OpNode extends AbstractNode {
	/** @var Op  */
	public $op;

	public function __construct(Op $op) {
		$this->op = $op;
	}

	public function toString() {
		return sprintf('Op[%s]@%d', $this->op->getType(), $this->op->getLine());
	}

	public function getHash() {
		return sprintf('OP[%s]@%d(%s)', $this->op->getType(), $this->op->getLine(), spl_object_hash($this->op));
	}
}