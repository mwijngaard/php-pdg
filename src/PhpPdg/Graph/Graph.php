<?php

namespace PhpPdg\Graph;

class Graph implements GraphInterface {
	/** @var NodeInterface[] */
	private $nodes = [];
	/** @var int[][][] */
	private $outgoing_edges = [];
	/** @var int[][][] */
	private $incoming_edges = [];

	public function addNode(NodeInterface $node) {
		$hash = $node->getHash();
		if (isset($this->nodes[$hash]) === true) {
			throw new \InvalidArgumentException("Node already exists");
		}
		$this->nodes[$hash] = $node;
	}

	public function addEdge(NodeInterface $from_node, NodeInterface $to_node, $type = '') {
		$this->assertNodeExists($from_node, 'from_node');
		$this->assertNodeExists($to_node, 'to_node');
		$from_node_hash = $from_node->getHash();
		$to_node_hash = $to_node->getHash();
		if (isset($this->outgoing_edges[$from_node_hash][$type][$to_node_hash]) === true) {
			throw new \InvalidArgumentException("Edge already exists");
		}
		$this->outgoing_edges[$from_node_hash][$type][$to_node_hash] = 1;
		$this->incoming_edges[$to_node_hash][$type][$from_node_hash] = 1;
	}

	public function hasNode(NodeInterface $node) {
		return isset($this->nodes[$node->getHash()]);
	}

	public function hasEdge(NodeInterface $from_node, NodeInterface $to_node, $type = '') {
		return isset($this->outgoing_edges[$from_node->getHash()][$type][$to_node->getHash()]);
	}

	public function getNodes() {
		return array_values($this->nodes);
	}

	public function getOutgoingEdgeNodes(NodeInterface $from_node, $type = '') {
		$this->assertNodeExists($from_node, 'from_node');
		$from_node_hash = $from_node->getHash();
		$outgoing_edge_types = isset($this->outgoing_edges[$from_node_hash]) ? $this->outgoing_edges[$from_node_hash] : [];
		return isset($outgoing_edge_types[$type]) ? $this->hashesToNodes(array_keys($outgoing_edge_types[$type])) : [];
	}

	public function getIncomingEdgeNodes(NodeInterface $to_node, $type = '') {
		$this->assertNodeExists($to_node, 'to_node');
		$to_node_hash = $to_node->getHash();
		$incoming_edge_types = isset($this->incoming_edges[$to_node_hash]) ? $this->incoming_edges[$to_node_hash] : [];
		return isset($incoming_edge_types[$type]) ? $this->hashesToNodes(array_keys($incoming_edge_types[$type])) : [];
	}

	public function deleteNode(NodeInterface $node) {
		$this->assertNodeExists($node, 'node');
		$hash = $node->getHash();
		foreach ($this->outgoing_edges as $type => $to_nodes) {
			foreach ($to_nodes as $to_node_hash => $_) {
				$this->innerDeleteEdge($hash, $to_node_hash, $type);
			}
		}
		foreach ($this->incoming_edges as $type => $from_nodes) {
			foreach ($from_nodes as $from_node_hash => $_) {
				$this->innerDeleteEdge($from_node_hash, $hash, $type);
			}
		}
		unset($this->nodes[$hash]);
	}

	public function deleteEdge(NodeInterface $from_node, NodeInterface $to_node, $type = '') {
		$this->assertNodeExists($from_node, 'from_node');
		$this->assertNodeExists($to_node, 'to_node');
		$from_node_hash = $from_node->getHash();
		$to_node_hash = $to_node->getHash();
		if (isset($this->nodes[$from_node_hash][$type][$to_node_hash]) === false) {
			throw new \InvalidArgumentException("Edge does not exist");
		}
		$this->innerDeleteEdge($from_node_hash, $to_node_hash, $type);
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

	private function hashesToNodes(array $hashes) {
		return array_map(function ($hash) {
			return $this->nodes[$hash];
		}, $hashes);
	}

	private function innerDeleteEdge($from_node_hash, $to_node_hash, $type) {
		unset($this->outgoing_edges[$from_node_hash][$type][$to_node_hash]);
		if (count($this->outgoing_edges[$from_node_hash][$type]) === 0) {
			unset($this->outgoing_edges[$from_node_hash][$type]);
		}
		if (count($this->outgoing_edges[$from_node_hash]) === 0) {
			unset($this->outgoing_edges[$from_node_hash]);
		}
		unset($this->incoming_edges[$to_node_hash][$type][$from_node_hash]);
		if (count($this->incoming_edges[$to_node_hash][$type]) === 0) {
			unset($this->incoming_edges[$to_node_hash][$type]);
		}
		if (count($this->incoming_edges[$to_node_hash]) === 0) {
			unset($this->incoming_edges[$to_node_hash]);
		}
	}
}