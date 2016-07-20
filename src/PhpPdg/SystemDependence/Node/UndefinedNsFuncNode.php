<?php

namespace PhpPdg\SystemDependence\Node;

use PhpPdg\Graph\Node\NodeInterface;

class UndefinedNsFuncNode implements NodeInterface {
	/** @var  string */
	private $name;
	/** @var  string */
	private $ns_name;

	/**
	 * UndefinedNsFuncNode constructor.
	 * @param string $name
	 * @param string $ns_name
	 */
	public function __construct($name, $ns_name) {
		$this->name = $name;
		$this->ns_name = $ns_name;
	}

	public function toString() {
		return 'Undefined NsFunc ' . $this->getId();
	}

	public function getHash() {
		return 'undefined-ns-func-' . $this->getId();
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getNsName() {
		return $this->ns_name;
	}

	/**
	 * @return string
	 */
	public function getId() {
		return $this->name . '|' . $this->ns_name;
	}
}