<?php

namespace PhpPdg\Graph;

use PhpPdg\Graph\Node\NodeInterface;

class Graph implements GraphInterface {
	/** @var NodeInterface[] */
	private $nodes = [];
	/** @var Edge[][][] */
	private $outgoing_edges = [];
	/** @var Edge[][][] */
	private $incoming_edges = [];

	public function addNode(NodeInterface $node) {
		$hash = $node->getHash();
		if (isset($this->nodes[$hash]) === true) {
			throw new \InvalidArgumentException("Node already exists");
		}
		$this->nodes[$hash] = $node;
	}

	public function addEdge(NodeInterface $from_node, NodeInterface $to_node, array $attributes = []) {
		if ($this->hasEdges($from_node, $to_node, $attributes, true) === true) {
			throw new \InvalidArgumentException("Edge already exists");
		}
		$edge = new Edge($from_node, $to_node, $attributes);
		$from_node_hash = $from_node->getHash();
		$to_node_hash = $to_node->getHash();
		$this->outgoing_edges[$from_node_hash][$to_node_hash][] = $edge;
		$this->incoming_edges[$to_node_hash][$from_node_hash][] = $edge;
	}

	public function hasNode(NodeInterface $node) {
		return isset($this->nodes[$node->getHash()]);
	}

	public function hasEdges(NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false) {
		$this->assertNodeExistsIfNotNull('from_node', $from_node);
		$this->assertNodeExistsIfNotNull('to_node', $to_node);
		return $this->hasEdgesRecursive($this->getEdgeCollection($from_node, $to_node), $filterAttributes, $filterAttributesExact);
	}

	public function getNodes() {
		return array_values($this->nodes);
	}

	public function getEdges(NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false) {
		$this->assertNodeExistsIfNotNull('from_node', $from_node);
		$this->assertNodeExistsIfNotNull('to_node', $to_node);
		$result = [];
		$this->addEdgesRecursive($result, $this->getEdgeCollection($from_node, $to_node), $filterAttributes, $filterAttributesExact);
		return $result;
	}

	public function deleteNode(NodeInterface $node) {
		$this->assertNodeExistsIfNotNull($node, 'node');
		$this->deleteEdges($node);
		$this->deleteEdges(null, $node);
		unset($this->nodes[$node->getHash()]);
	}

	public function deleteEdges(NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false) {
		$this->assertNodeExistsIfNotNull('from_node', $from_node);
		$this->assertNodeExistsIfNotNull('to_node', $to_node);
		foreach ($this->getEdges($from_node, $to_node, $filterAttributes, $filterAttributesExact) as $edge) {
			$from_node_hash = $from_node->getHash();
			$to_node_hash = $to_node->getHash();
			$from_outgoing_edges = &$this->outgoing_edges[$from_node_hash];
			$from_to_outgoing_edges = &$from_outgoing_edges[$to_node_hash];
			unset($from_to_outgoing_edges[array_search($edge, $from_to_outgoing_edges, true)]);
			if (empty($from_to_outgoing_edges) === true) {
				unset($from_to_outgoing_edges[$to_node_hash]);
			}
			if (empty($from_outgoing_edges) === true) {
				unset($this->outgoing_edges[$from_node_hash]);
			}
			$to_incoming_edges = &$this->incoming_edges[$to_node_hash];
			$to_from_incoming_edges = &$to_incoming_edges[$from_node_hash];
			unset($to_from_incoming_edges[array_search($edge, $to_from_incoming_edges, true)]);
			if (empty($to_from_incoming_edges) === true) {
				unset($to_incoming_edges[$from_node_hash]);
			}
			if (empty($to_incoming_edges) === true) {
				unset($this->incoming_edges[$to_node_hash]);
			}
		}
	}

	public function clear() {
		$this->nodes = [];
		$this->outgoing_edges = [];
		$this->incoming_edges = [];
	}

	/**
	 * @param $label
	 * @param NodeInterface|null $node
	 * @throws \InvalidArgumentException
	 */
	private function assertNodeExistsIfNotNull($label, NodeInterface $node = null) {
		if ($node !== null && isset($this->nodes[$node->getHash()]) === false) {
			throw new \InvalidArgumentException(sprintf("Node `%s` does not exist", $label));
		}
	}

	/**
	 * @param NodeInterface|null $from_node
	 * @param NodeInterface|null $to_node
	 * @return Edge[]|Edge[][]|Edge[][][]
	 */
	private function getEdgeCollection(NodeInterface $from_node = null, NodeInterface $to_node = null) {
		if ($from_node !== null && $to_node !== null) {
			$from_node_hash = $from_node->getHash();
			$to_node_hash = $to_node->getHash();
			return isset($this->outgoing_edges[$from_node_hash][$to_node_hash]) === true ? $this->outgoing_edges[$from_node_hash][$to_node_hash] : [];
		} else if ($from_node !== null) {
			$from_node_hash = $from_node->getHash();
			return isset($this->outgoing_edges[$from_node_hash]) === true ? $this->outgoing_edges[$from_node_hash] : [];
		} else if ($to_node !== null) {
			$to_node_hash = $to_node->getHash();
			return isset($this->incoming_edges[$to_node_hash]) === true ? $this->incoming_edges[$to_node_hash] : [];
		}
		return $this->outgoing_edges;
	}

	/**
	 * @param Edge[]|Edge[][]|Edge[][][] $edge_collection
	 * @param array $filterAttributes
	 * @param $filterAttributesExact
	 * @return bool
	 */
	private function hasEdgesRecursive($edge_collection, array $filterAttributes, $filterAttributesExact) {
		foreach ($edge_collection as $edge_collection_entry) {
			if (is_array($edge_collection_entry) === true) {
				if ($this->hasEdgesRecursive($edge_collection_entry, $filterAttributes, $filterAttributesExact) === true) {
					return true;
				}
			} else {
				assert($edge_collection_entry instanceof Edge);
				if ($this->attributesMatch($edge_collection_entry->getAttributes(), $filterAttributes, $filterAttributesExact) === true) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @param array $result
	 * @param Edge[]|Edge[][]|Edge[][][] $edge_collection
	 * @param array $filterAttributes
	 * @param $filterAttributesExact
	 */
	private function addEdgesRecursive(array &$result, $edge_collection, array $filterAttributes, $filterAttributesExact) {
		foreach ($edge_collection as $edge_collection_entry) {
			if (is_array($edge_collection_entry) === true) {
				$this->addEdgesRecursive($result, $edge_collection_entry, $filterAttributes, $filterAttributesExact);
			} else {
				assert($edge_collection_entry instanceof Edge);
				if ($this->attributesMatch($edge_collection_entry->getAttributes(), $filterAttributes, $filterAttributesExact) === true) {
					$result[] = $edge_collection_entry;
				}
			}
		}
	}

	/**
	 * @param array $attributes
	 * @param array $filter_attributes
	 * @param $filter_attributes_exact
	 * @return bool
	 */
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

	/**
	 * Compute the subgraph that is reachable from $nodes
	 * @param GraphInterface $graph
	 * @param NodeInterface[] $nodes
	 * @return GraphInterface
	 */
	public static function reachable(GraphInterface $graph, array $nodes) {
		$result = new Graph();
		$seen = [];
		$worklist = [];
		foreach ($nodes as $i => $node) {
			if ($graph->hasNode($node) === false) {
				throw new \InvalidArgumentException("graph does not contain node `$i`");
			}
			$seen[$node->getHash()] = 1;
			$worklist[] = $node;
			$result->addNode($node);
		}
		while (empty($worklist) === false) {
			$from_node = array_shift($worklist);
			foreach ($graph->getEdges($from_node) as $incoming_edge) {
				$to_node = $incoming_edge->getToNode();
				if (isset($seen[$to_node->getHash()]) === false) {
					$seen[$to_node->getHash()] = 1;
					$worklist[] = $to_node;
					$result->addNode($to_node);
				}
				$result->addEdge($from_node, $to_node, $incoming_edge->getAttributes());
			}
		}
		return $result;
	}

	/**
	 * Compute the subgraph that can reach $nodes
	 * @param GraphInterface $graph
	 * @param NodeInterface[] $nodes
	 * @return GraphInterface
	 */
	public static function reachableInv(GraphInterface $graph, array $nodes) {
		$result = new Graph();
		$seen = [];
		$worklist = [];
		foreach ($nodes as $i => $node) {
			if ($graph->hasNode($node) === false) {
				throw new \InvalidArgumentException("graph does not contain node `$i`");
			}
			$seen[$node->getHash()] = 1;
			$worklist[] = $node;
			$result->addNode($node);
		}
		while (empty($worklist) === false) {
			$to_node = array_shift($worklist);
			foreach ($graph->getEdges(null, $to_node) as $incoming_edge) {
				$from_node = $incoming_edge->getFromNode();
				if (isset($seen[$from_node->getHash()]) === false) {
					$seen[$from_node->getHash()] = 1;
					$worklist[] = $from_node;
					$result->addNode($from_node);
				}
				$result->addEdge($from_node, $to_node, $incoming_edge->getAttributes());
			}
		}
		return $result;
	}

	/**
	 * @param GraphInterface $graph
	 * @param NodeInterface $source_node
	 * @param NodeInterface[] $target_nodes
	 * @param array $attributes
	 */
	public static function ensureNodesAndEdgesAdded(GraphInterface $graph, $source_node, $target_nodes, $attributes = []) {
		foreach ($target_nodes as $target_node) {
			self::ensureNodeAndEdgeAdded($graph, $source_node, $target_node, $attributes);
		}
	}

	/**
	 * @param GraphInterface $graph
	 * @param NodeInterface $source_node
	 * @param NodeInterface $target_node
	 * @param array $attributes
	 */
	public static function ensureNodeAndEdgeAdded(GraphInterface $graph, $source_node, $target_node, $attributes = []) {
		if ($graph->hasNode($target_node) === false) {
			$graph->addNode($target_node);
			$graph->addEdge($source_node, $target_node, $attributes);
		} else if ($graph->hasEdges($source_node, $target_node, $attributes) === false) {
			$graph->addEdge($source_node, $target_node, $attributes);
		}
	}
}