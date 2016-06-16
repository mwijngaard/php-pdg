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
		return sprintf('Op %s @ %s line %s', str_replace("PHPCfg\\Op\\", '', get_class($this->op)), $this->op->getFile(), $this->op->getLine()) ;
	}

	public function getHash() {
		return 'OP(' . spl_object_hash($this->op) . ')';
	}
}