<?php

namespace PhpPdg\Graph;

use PHPCfg\Op;

interface NodeInterface {
	/**
	 * Get a string representation of the node, for printing
	 *
	 * @return string
	 */
	public function toString();

	/**
	 * Returns a hash uniquely identifying this object. This is used for equality checks in the Graph.
	 *
	 * @return string
	 */
	public function getHash();
}