<?php

namespace PhpPdg\ProgramDependence;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\Node\NodeInterface;
use PhpPdg\Graph\Node\EntryNode;

class Func {
	/** @var  string */
	public $name;
	/** @var  string|null */
	public $class_name;
	/** @var EntryNode */
	public $entry_node;
	/** @var NodeInterface[] */
	public $param_nodes = [];
	/** @var NodeInterface[] */
	public $return_nodes = [];
	/** @var  GraphInterface */
	public $pdg;

	public function __construct($name, $class_name = null, NodeInterface $entry_node, GraphInterface $pdg) {
		$this->name = $name;
		$this->class_name = $class_name;
		$this->entry_node = $entry_node;
		$this->pdg = $pdg;
	}

	public function getScopedName() {
		if ($this->class_name !== null) {
			return $this->class_name . '::' . $this->name;
		}
		return $this->name;
	}
}