<?php

namespace PhpPdg\ProgramDependence;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\Node\NodeInterface;
use PhpPdg\ProgramDependence\Node\EntryNode;

class Func {
	/** @var  string */
	public $name;
	/** @var  string|null */
	public $class_name;
	/** @var  string|null */
	public $filename;
	/** @var NodeInterface */
	public $entry_node;
	/** @var NodeInterface[] */
	public $param_nodes = [];
	/** @var NodeInterface[] */
	public $return_nodes = [];
	/** @var  GraphInterface */
	public $pdg;

	/**
	 * Func constructor.
	 * @param string $name
	 * @param string|null $class_name
	 * @param string|null $filename
	 * @param NodeInterface $entry_node
	 * @param GraphInterface $pdg
	 */
	public function __construct($name, $class_name, $filename, NodeInterface $entry_node, GraphInterface $pdg) {
		$this->name = $name;
		$this->class_name = $class_name;
		$this->filename = $filename;
		$this->entry_node = $entry_node;
		$this->pdg = $pdg;
	}

	/**
	 * @return string
	 */
	public function getScopedName() {
		if ($this->class_name !== null) {
			return $this->class_name . '::' . $this->name;
		}
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getId() {
		if ($this->filename !== null) {
			return $this->filename . '[' . $this->getScopedName() . ']';
		}
		return $this->getScopedName();
	}
}