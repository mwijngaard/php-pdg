<?php

namespace PhpPdg\SystemDependence\Node;

use PhpPdg\Graph\Node\AbstractNode;
use PhpPdg\ProgramDependence\Func;

class FuncNode extends AbstractNode {
	/** @var string */
	private $id;
	/** @var Func  */
	private $func;

	/**
	 * FuncNode constructor.
	 * @param string $id
	 * @param Func $func
	 */
	public function __construct($id, Func $func) {
		$this->id = $id;
		$this->func = $func;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return Func
	 */
	public function getFunc() {
		return $this->func;
	}

	public function toString() {
		return 'Func ' . $this->id;
	}

	public function getHash() {
		return $this->id . spl_object_hash($this->func);
	}
}