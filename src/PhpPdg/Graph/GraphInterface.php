<?php

namespace PhpPdg\Graph;

interface GraphInterface {
	/**
	 * Add a node to the graph
	 *
	 * @param NodeInterface $node
	 * @return mixed
	 */
	public function addNode(NodeInterface $node);

	/**
	 * Add an edge to the graph (nodes must already exist)
	 *
	 * @param NodeInterface $from_node
	 * @param NodeInterface $to_node
	 * @return mixed
	 */
	public function addEdge(NodeInterface $from_node, NodeInterface $to_node);

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
	 * @return NodeInterface[]
	 */
	public function getOutgoingEdgeNodes(NodeInterface $from_node);

	/**
	 * Return all nodes that have an edge to this node
	 *
	 * @param NodeInterface $to_node
	 * @return NodeInterface[]
	 */
	public function getIncomingEdgeNodes(NodeInterface $to_node);

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
	 */
	public function deleteEdge(NodeInterface $from_node, NodeInterface $to_node);

	/**
	 * Clear all nodes and edges from the graph
	 */
	public function clear();
}