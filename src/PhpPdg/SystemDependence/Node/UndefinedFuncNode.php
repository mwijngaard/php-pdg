<?php

namespace PhpPdg\SystemDependence\Node;

use PhpPdg\Graph\Node\NodeInterface;

class UndefinedFuncNode implements NodeInterface {
	/** @var  string */
	private $name;
	/** @var  string|null */
	private $class_name;

	/**
	 * UndefinedFuncNode constructor.
	 * @param string $name
	 * @param string|null $class_name
	 */
	public function __construct($name, $class_name) {
		$this->name = $name;
		$this->class_name = $class_name;
	}

	public function toString() {
		return 'Undefined Func ' . $this->getId();
	}

	public function getHash() {
		return 'undefined-func-' . (isset($this->class_name) === true ? $this->class_name . '::' : '') . $this->name;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return null|string
	 */
	public function getClassName() {
		return $this->class_name;
	}

	/**
	 * @return string
	 */
	public function getId() {
		if ($this->class_name !== null) {
			return $this->class_name . '::' . $this->name;
		}
		return $this->name;
	}
}