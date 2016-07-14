<?php

namespace PhpPdg\Graph;

use PhpPdg\Graph\Node\NodeInterface;

interface GraphInterface {
	/**
	 * @param NodeInterface $node
	 */
	public function addNode(NodeInterface $node);

	/**
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
	 * Whether or not the graph has any edges that match the arguments (all match by default)
	 *
	 * @param NodeInterface $from_node
	 * @param NodeInterface $to_node
	 * @param array $filterAttributes
	 * @param boolean $filterAttributesExact Should all attributes match
	 * @return boolean
	 */
	public function hasEdges(NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false);

	/**
	 * @return NodeInterface[]
	 */
	public function getNodes();

	/**
	 * Get edges that match the arguments (all match by default)
	 *
	 * @param NodeInterface $from_node
	 * @param NodeInterface $to_node
	 * @param array $filterAttributes
	 * @param boolean $filterAttributesExact Should all attributes match
	 * @return Edge[]
	 */
	public function getEdges(NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false);


	/**
	 * Delete a node and all its connected edges from the graph
	 *
	 * @param NodeInterface $node
	 */
	public function deleteNode(NodeInterface $node);

	/**
	 * Delete edges that match the arguments (all match by default)
	 *
	 * @param NodeInterface $from_node
	 * @param NodeInterface $to_node
	 * @param array $filterAttributes
	 * @param boolean $filterAttributesExact Should all attributes match
	 */
	public function deleteEdges(NodeInterface $from_node = null, NodeInterface $to_node = null, array $filterAttributes = [], $filterAttributesExact = false);

	public function clear();
}