<?php

namespace PhpPdg\Program;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;

abstract class Program {
	/** @var NodeInterface  */
	public $entry_node;
	/** @var NodeInterface[] */
	public $call_nodes = [];
	/** @var NodeInterface[] */
	public $return_nodes = [];
	/** @var NodeInterface[] */
	public $exceptional_return_nodes = [];
	/** @var NodeInterface[] */
	public $closure_nodes = [];
	/** @var GraphInterface */
	public $dependence_graph;

	/**
	 * AbstractCallable constructor.
	 * @param NodeInterface $entry_node
	 * @param GraphInterface $dependence_graph
	 */
	public function __construct(NodeInterface $entry_node, GraphInterface $dependence_graph) {
		$this->entry_node = $entry_node;
		$this->dependence_graph = $dependence_graph;
	}

	abstract public function getIdentifier();
}