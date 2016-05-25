<?php

namespace PhpPdg;

use PhpPdg\Graph\NodeInterface;
use PhpPdg\Nodes\EntryNode;

class Func {
	/** @var  string */
	public $name;
	/** @var  string|null */
	public $class_name;
	/** @var EntryNode */
	public $entry_node;
	/** @var NodeInterface[] */
	public $return_nodes = [];
	/** @var NodeInterface[] */
	public $exceptional_return_nodes = [];

	public function __construct($name, $class_name = null, NodeInterface $entry_node) {
		$this->name = $name;
		$this->class_name = $class_name;
		$this->entry_node = $entry_node;
	}

	public function getScopedName() {
		if ($this->class_name !== null) {
			return $this->class_name . '::' . $this->name;
		}
		return $this->name;
	}
}