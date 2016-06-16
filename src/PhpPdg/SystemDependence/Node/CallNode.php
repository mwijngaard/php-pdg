<?php

namespace PhpPdg\SystemDependence\Node;

use PhpPdg\Graph\Node\AbstractNode;
use PHPCfg\Op;
use PhpPdg\ProgramDependence\Func;

class CallNode extends AbstractNode {
	/** @var Op  */
	private $call_op;

	/**
	 * CallNode constructor.
	 * @param Op $call_op
	 */
	public function __construct(Op $call_op) {
		$this->call_op = $call_op;
	}

	public function getCallOp() {
		return $this->call_op;
	}

	public function toString() {
		return sprintf('Call %s @ %s line %s', str_replace("PHPCfg\\Op\\", '', get_class($this->call_op)), $this->call_op->getFile(), $this->call_op->getLine());
	}

	public function getHash() {
		return spl_object_hash($this->call_op);
	}
}