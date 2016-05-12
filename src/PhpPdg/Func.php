<?php

namespace PhpPdg;

use PHPCfg\Op;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;

class Func {
	/** @var  string */
	public $name;
	/** @var  string */
	public $class;
	/** @var NodeInterface  */
	public $entry_node;
	/** @var NodeInterface[] */
	public $param_nodes = [];
	/** @var NodeInterface[] */
	public $return_nodes = [];
	/** @var NodeInterface[] */
	public $exceptional_return_nodes = [];
	/** @var GraphInterface */
	public $dependence_graph;

	/**
	 * Func constructor.
	 * @param string $name
	 * @param string $class
	 * @param NodeInterface $entry_node
	 * @param GraphInterface $dependence_graph
	 */
	public function __construct($name, $class, NodeInterface $entry_node, GraphInterface $dependence_graph) {
		$this->name = $name;
		$this->class = $class;
		$this->entry_node = $entry_node;
		$this->dependence_graph = $dependence_graph;
	}
}