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
}