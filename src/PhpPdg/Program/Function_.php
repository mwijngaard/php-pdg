<?php

namespace PhpPdg\Program;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;

// @codingStandardsIgnoreStart MWY: ignore classname warning
class Function_ extends FunctionLike {
	// @codingStandardsIgnoreEnd

	/** @var  string */
	public $namespaced_name;

	/**
	 * Func constructor.
	 * @param string $namespaced_name
	 * @param NodeInterface $entry_node
	 * @param GraphInterface $dependence_graph
	 */
	public function __construct($namespaced_name, NodeInterface $entry_node, GraphInterface $dependence_graph) {
		$this->namespaced_name = $namespaced_name;
		parent::__construct($entry_node, $dependence_graph);
	}

	public function getNamespacedName() {
		return $this->namespaced_name;
	}

	public function getIdentifier() {
		return $this->namespaced_name;
	}
}