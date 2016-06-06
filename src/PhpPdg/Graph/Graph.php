<?php

namespace PhpPdg\Graph;

use PhpPdg\Graph\Node\NodeInterface;

class Graph implements GraphInterface {
	/** @var NodeInterface[] */
	private $nodes = [];
	/** @var Edge[] */
	private $edges = [];

	public function addNode(NodeInterface $node) {
		$hash = $node->getHash();
		if (isset($this->nodes[$hash]) === true) {
			throw new \InvalidArgumentException("Node already exists");
		}
		$this->nodes[$hash] = $node;
	}

	public function addEdge(NodeInterface $from_node, NodeInterface $to_node, array $attributes = []) {
		$this->assertNodeExistsIfNotNull('from_node', $from_node);
		$this->assertNodeExistsIfNotNull('to_node', $to_node);
		$this->edges[] = new Edge($from_node, $to_node, $attributes);
	}

	public function hasNode(NodeInterface $node) {
		return isset($this->nodes[$node->getHash()]);
	}

	public function hasEdges(NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false) {
		$this->assertNodeExistsIfNotNull('from_node', $from_node);
		$this->assertNodeExistsIfNotNull('to_node', $to_node);
		foreach ($this->edges as $edge) {
			if ($this->edgeMatches($edge, $from_node, $to_node, $filterAttributes, $filterAttributesExact) === true) {
				return true;
			}
		}
		return false;
	}

	public function getNodes() {
		return array_values($this->nodes);
	}

	public function getEdges(NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false) {
		$this->assertNodeExistsIfNotNull('from_node', $from_node);
		$this->assertNodeExistsIfNotNull('to_node', $to_node);
		$result = [];
		foreach ($this->edges as $edge) {
			if ($this->edgeMatches($edge, $from_node, $to_node, $filterAttributes, $filterAttributesExact) === true) {
				$result[] = $edge;
			}
		}
		return $result;
	}

	public function deleteNode(NodeInterface $node) {
		$this->assertNodeExistsIfNotNull($node, 'node');
		foreach ($this->edges as $index => $edge) {
			if ($this->edgeMatches($edge, $node) === true || $this->edgeMatches($edge, null, $node) === true) {
				unset($this->edges[$index]);
			}
		}
		unset($this->nodes[$node->getHash()]);
	}

	public function deleteEdges(NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false) {
		$this->assertNodeExistsIfNotNull('from_node', $from_node);
		$this->assertNodeExistsIfNotNull('to_node', $to_node);
		foreach ($this->edges as $index => $edge) {
			if ($this->edgeMatches($edge, $from_node, $to_node, $filterAttributes, $filterAttributesExact) === true) {
				unset($this->edges[$index]);
			}
		}
	}

	public function clear() {
		$this->nodes = [];
		$this->edges = [];
	}

	private function assertNodeExistsIfNotNull($label, NodeInterface $node = null) {
		if ($node !== null && isset($this->nodes[$node->getHash()]) === false) {
			throw new \InvalidArgumentException(sprintf("Node `%s` does not exist", $label));
		}
	}

	private function edgeMatches(Edge $edge, NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false) {
		return
			($from_node === null || $from_node->equals($edge->getFromNode()))
			&& ($to_node === null || $to_node->equals($edge->getToNode()))
			&& $this->attributesMatch($edge->getAttributes(), $filterAttributes, $filterAttributesExact);
	}

	private function attributesMatch(array $attributes, array $filter_attributes, $filter_attributes_exact) {
		if ($filter_attributes_exact === true && count($attributes) !== count($filter_attributes)) {
			return false;
		}
		foreach ($filter_attributes as $key => $value) {
			if (isset($attributes[$key]) === false || $attributes[$key] !== $value) {
				return false;
			}
		}
		return true;
	}
}