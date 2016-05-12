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
		$startLine = $this->op_node->getAttribute('startLine');
		$endLine = $this->op_node->getAttribute('endLine');
		$lines = $startLine === $endLine ? $startLine : $startLine . ':' . $endLine;
		return sprintf('OP[%s] @ line %s', str_replace("PHPCfg\\Op\\", '', get_class($this->op_node)), $lines) ;
	}

	public function getHash() {
		return 'OP(' . spl_object_hash($this->op_node) . ')';
	}
}