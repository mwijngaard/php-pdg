<?php

namespace PhpPdg\Graph;

class Graph {
	/** @var  \SplObjectStorage|Node[] */
	private $nodes;

	public function __construct() {
		$this->nodes = new \SplObjectStorage();
	}

	public function addNode($obj) {
		if ($this->nodes->contains($obj) === false) {
			$this->nodes->attach($obj, new Node($obj));
		}
	}

	public function addEdge($from_obj, $to_obj) {
		$from_node = $this->getNodeForObject($from_obj);
		$to_node = $this->getNodeForObject($to_obj);
		$from_node->addEdge($to_node);
	}

	/**
	 * @return object[]
	 */
	public function getNodes() {
		return iterator_to_array($this->nodes);
	}

	/**
	 * @param object $from_obj
	 * @return object[]
	 */
	public function getOutgoingEdgeNodes($from_obj) {
		return $this->returnNodeObjects($this->getNodeForObject($from_obj)->getOutgoingEdgeNodes());
	}

	/**
	 * @param object $to_obj
	 * @return object[]
	 */
	public function getIncomingEdgeNodes($to_obj) {
		return $this->returnNodeObjects($this->getNodeForObject($to_obj)->getIncomingEdgeNodes());
	}

	/**
	 * @param object $obj
	 */
	public function deleteNode($obj) {
		$node = $this->getNodeForObject($obj);
		foreach ($node->getIncomingEdgeNodes() as $from_node) {
			$from_node->deleteEdge($node);
		}
		$this->nodes->detach($node);
	}

	/**
	 * @param object $from_obj
	 * @param object $to_obj
	 */
	public function deleteEdge($from_obj, $to_obj) {
		$from_node = $this->getNodeForObject($from_obj);
		$to_node = $this->getNodeForObject($to_obj);
		$from_node->deleteEdge($to_node);
	}

	/**
	 * @param object $obj
	 * @return Node
	 */
	private function getNodeForObject($obj) {
		if ($this->nodes->contains($obj) === false) {
			throw new \InvalidArgumentException("No such node");
		}
		return $this->nodes[$obj];
	}

	/**
	 * @param Node[] $nodes
	 * @return object[]
	 */
	private function returnNodeObjects($nodes) {
		$ret = array();
		foreach ($nodes as $node) {
			$ret[] = $node->getObject();
		}
		return $ret;
	}
}