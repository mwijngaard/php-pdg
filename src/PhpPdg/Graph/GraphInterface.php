<?php

namespace PhpPdg\Graph;

use PhpPdg\Graph\Node\NodeInterface;

interface GraphInterface {
	/**
	 * Add a node to the graph
	 *
	 * @param NodeInterface $node
	 */
	public function addNode(NodeInterface $node);

	/**
	 * Add an edge to the graph (nodes must already exist)
	 *
	 * @param NodeInterface $from_node
	 * @param NodeInterface $to_node
	 * @param array $attributes
	 */
	public function addEdge(NodeInterface $from_node, NodeInterface $to_node, array $attributes = []);

	/**
	 * @param NodeInterface $node
	 * @return boolean
	 */
	public function hasNode(NodeInterface $node);

	/**
	 * @param NodeInterface $from_node
	 * @param NodeInterface $to_node
	 * @param array $filterAttributes
	 * @param boolean $filterAttributesExact
	 * @return boolean
	 */
	public function hasEdges(NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false);

	/**
	 * Return all nodes in the graph
	 *
	 * @return NodeInterface[]
	 */
	public function getNodes();

	/**
	 * Return all nodes that from_node has an edge to
	 *
	 * @param NodeInterface $from_node
	 * @param NodeInterface $to_node
	 * @param array $filterAttributes
	 * @param boolean $filterAttributesExact
	 * @return Edge[]
	 */
	public function getEdges(NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false);


	/**
	 * Delete a single node, and all its connected edges from the graph
	 *
	 * @param NodeInterface $node
	 */
	public function deleteNode(NodeInterface $node);

	/**
	 * Delete a single edge from the graph
	 *
	 * @param NodeInterface $from_node
	 * @param NodeInterface $to_node
	 * @param array $filterAttributes
	 * @param boolean $filterAttributesExact
	 */
	public function deleteEdges(NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false);

	/**
	 * Clear all nodes and edges from the graph
	 */
	public function clear();
}