<?php

namespace PhpPdg\SystemDependence\Node;

use PhpPdg\Graph\Node\AbstractNode;
use PhpPdg\ProgramDependence\Func;

class FuncNode extends AbstractNode {
	/** @var Func  */
	private $func;

	/**
	 * FuncNode constructor.
	 * @param Func $func
	 */
	public function __construct(Func $func) {
		$this->func = $func;
	}

	/**
	 * @return Func
	 */
	public function getFunc() {
		return $this->func;
	}

	public function toString() {
		return 'Func ' . $this->func->getId();
	}

	public function getHash() {
		return $this->func->getId();
	}
}