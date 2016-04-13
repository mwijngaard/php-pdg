<?php

namespace PhpPdg\Graph;

class Node {
	/** @var  object */
	private $obj;
	/** @var \SplObjectStorage|Node[]  */
	private $outgoing;
	/** @var \SplObjectStorage|Node[]  */
	private $incoming;

	/**
	 * @param object $obj
	 */
	public function __construct($obj) {
		$this->obj = $obj;
		$this->outgoing = new \SplObjectStorage();
		$this->incoming = new \SplObjectStorage();
	}

	/**
	 * @return object
	 */
	public function getObject() {
		return $this->obj;
	}

	/**
	 * @param Node $to_node
	 */
	public function addEdge(Node $to_node) {
		if ($this->outgoing->contains($to_node) === false) {
			$this->outgoing->attach($to_node);
			$to_node->addIncomingEdge($this);
		}
	}

	/**
	 * @param Node $from_node
	 */
	private function addIncomingEdge(Node $from_node) {
		if ($this->incoming->contains($from_node) === false) {
			$this->incoming->attach($from_node);
		}
	}

	/**
	 * @param Node $to_node
	 */
	public function deleteEdge(Node $to_node) {
		if ($this->outgoing->contains($to_node) === false) {
			throw new \InvalidArgumentException("No such outgoing edge");
		}
		$this->outgoing->detach($to_node);
		$to_node->deleteIncomingEdge($this);
	}

	/**
	 * @param Node $from_node
	 */
	private function deleteIncomingEdge(Node $from_node) {
		if ($this->incoming->contains($from_node) === false) {
			throw new \LogicException("Graph corruption");
		}
		$this->incoming->detach($from_node);
	}

	/**
	 * @return Node[]
	 */
	public function getIncomingEdgeNodes() {
		return $this->incoming;
	}

	/**
	 * @return Node[]
	 */
	public function getOutgoingEdgeNodes() {
		return $this->outgoing;
	}
}