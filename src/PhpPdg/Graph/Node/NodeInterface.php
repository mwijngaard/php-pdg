<?php

namespace PhpPdg\Graph\Node;

interface NodeInterface {
	/**
	 * Get a string representation of the node, for printing
	 *
	 * @return string
	 */
	public function toString();

	/**
	 * Returns a hash uniquely identifying this object.
	 *
	 * @return string
	 */
	public function getHash();

	/**
	 * Checks if 2 nodes are equal. Must be consistent with getHash().
	 *
	 * @param NodeInterface $other_node
	 * @return boolean
	 */
	public function equals(NodeInterface $other_node);
}