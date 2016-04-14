<?php

namespace PhpPdg\Graph;

class Graph implements GraphInterface {
	/** @var NodeInterface[] */
	private $nodes = [];
	/** @var NodeInterface[][] */
	private $outgoing_edges = [];
	/** @var NodeInterface[][] */
	private $incoming_edges = [];

	public function addNode(NodeInterface $node) {
		$hash = $node->getHash();
		if (isset($hash) === true) {
			throw new \InvalidArgumentException("Node already exists");
		}
		$this->nodes[$hash] = $node;
		$this->outgoing_edges[$hash] = [];
		$this->incoming_edges[$hash] = [];
	}

	public function addEdge(NodeInterface $from_node, NodeInterface $to_node) {
		$this->assertNodeExists($from_node, 'from_node');
		$this->assertNodeExists($to_node, 'to_node');
		$from_node_hash = $from_node->getHash();
		$to_node_hash = $to_node->getHash();
		if (isset($this->outgoing_edges[$from_node_hash][$to_node_hash]) === true) {
			throw new \InvalidArgumentException("Edge already exists");
		}
		$this->outgoing_edges[$from_node_hash][$to_node_hash] = $to_node;
		$this->incoming_edges[$to_node_hash][$from_node_hash] = $from_node;
	}

	public function hasNode(NodeInterface $node) {
		return isset($this->nodes[$node->getHash()]);
	}

	public function hasEdge(NodeInterface $from_node, NodeInterface $to_node) {
		return isset($this->outgoing_edges[$from_node->getHash()][$to_node->getHash()]);
	}

	public function getNodes() {
		return $this->nodes;
	}

	public function getOutgoingEdgeNodes(NodeInterface $from_node) {
		$this->assertNodeExists($from_node, 'from_node');
		return $this->outgoing_edges[$from_node->getHash()];
	}

	public function getIncomingEdgeNodes(NodeInterface $to_node) {
		$this->assertNodeExists($to_node, 'to_node');
		return $this->incoming_edges[$to_node->getHash()];
	}

	public function deleteNode(NodeInterface $node) {
		$this->assertNodeExists($node, 'node');
		$hash = $node->getHash();
		foreach ($this->outgoing_edges as $to_hash => $_) {
			unset($this->outgoing_edges[$to_hash][$hash]);
		}
		foreach ($this->incoming_edges as $from_hash => $_) {
			unset($this->incoming_edges[$from_hash][$hash]);
		}
		unset($this->outgoing_edges[$hash]);
		unset($this->incoming_edges[$hash]);
		unset($this->nodes[$hash]);
	}

	public function deleteEdge(NodeInterface $from_node, NodeInterface $to_node) {
		$this->assertNodeExists($from_node, 'from_node');
		$this->assertNodeExists($to_node, 'to_node');
		$from_node_hash = $from_node->getHash();
		$to_node_hash = $to_node->getHash();
		if (isset($this->nodes[$from_node_hash][$to_node_hash]) === false) {
			throw new \InvalidArgumentException("Edge does not exist");
		}
		unset($this->nodes[$from_node_hash][$to_node_hash]);
	}

	public function clear() {
		$this->nodes = [];
		$this->incoming_edges = [];
		$this->outgoing_edges = [];
	}

	private function assertNodeExists(NodeInterface $node, $label) {
		if (isset($this->nodes[$node->getHash()]) === false) {
			throw new \InvalidArgumentException(sprintf("Node `%s` does not exist", $label));
		}
	}
}