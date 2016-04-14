<?php

namespace PhpPdg\Nodes;

use PHPCfg\Op;
use PhpPdg\Graph\NodeInterface;

class OpNode implements NodeInterface {
	/** @var Op  */
	public $op_node;

	public function __construct(Op $op_node) {
		$this->op_node = $op_node;
	}

	public function toString() {
		return $this->getHash();
	}

	public function getHash() {
		return 'OP(' . spl_object_hash($this->op_node) . ')';
	}
}