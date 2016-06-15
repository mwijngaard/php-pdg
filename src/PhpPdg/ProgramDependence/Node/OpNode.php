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
		$startLine = $this->op->getAttribute('startLine', -1);
		$endLine = $this->op->getAttribute('endLine', -1);
		$lines = $startLine === $endLine ? $startLine : $startLine . ':' . $endLine;
		return sprintf('Op %s @ line %s', str_replace("PHPCfg\\Op\\", '', get_class($this->op)), $lines) ;
	}

	public function getHash() {
		return 'OP(' . spl_object_hash($this->op) . ')';
	}
}