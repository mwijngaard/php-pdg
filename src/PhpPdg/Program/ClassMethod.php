<?php

namespace PhpPdg\Program;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;

class ClassMethod extends FunctionLike {
	/** @var  string */
	public $method_name;
	/** @var  string */
	public $namespaced_class_name;

	/**
	 * ClassMethod constructor.
	 * @param string $method_name
	 * @param string $namespaced_class_name
	 * @param NodeInterface $entry_node
	 * @param GraphInterface $dependence_graph
	 */
	public function __construct($method_name, $namespaced_class_name, NodeInterface $entry_node, GraphInterface $dependence_graph) {
		$this->method_name = $method_name;
		$this->namespaced_class_name = $namespaced_class_name;
		parent::__construct($entry_node, $dependence_graph);
	}

	public function getMethodName() {
		return $this->method_name;
	}

	public function getNamespacedClassName() {
		return $this->namespaced_class_name;
	}

	public function getIdentifier() {
		return $this->namespaced_class_name . '::' . $this->method_name;
	}
}