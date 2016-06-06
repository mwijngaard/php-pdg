<?php

namespace PhpPdg\SystemDependence\Node;

use PhpPdg\Graph\Node\AbstractNode;
use PHPCfg\Op;
use PhpPdg\ProgramDependence\Func;

class CallNode extends AbstractNode {
	/** @var Op  */
	private $call_op;
	/** @var  string */
	private $containing_func_id;
	/** @var Func  */
	private $containing_func;

	/**
	 * CallNode constructor.
	 * @param Op $call_op
	 * @param string $containing_func_id
	 * @param Func $containing_func
	 */
	public function __construct(Op $call_op, $containing_func_id, Func $containing_func) {
		$this->call_op = $call_op;
		$this->containing_func_id = $containing_func_id;
		$this->containing_func = $containing_func;
	}

	public function getCallOp() {
		return $this->call_op;
	}

	public function getContainingFuncId() {
		return $this->containing_func_id;
	}

	public function getContainingFunc() {
		return $this->containing_func;
	}

	public function toString() {
		$startLine = $this->call_op->getAttribute('startLine', -1);
		$endLine = $this->call_op->getAttribute('endLine', -1);
		$lines = $startLine === $endLine ? $startLine : $startLine . ':' . $endLine;
		return sprintf('Call %s @ %s on line %s', str_replace("PHPCfg\\Op\\", '', get_class($this->call_op)), $this->containing_func_id, $lines);
	}

	public function getHash() {
		return spl_object_hash($this->call_op) . spl_object_hash($this->containing_func);
	}
}