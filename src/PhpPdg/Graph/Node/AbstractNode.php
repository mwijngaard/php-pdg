<?php

namespace PhpPdg\Graph\Node;

abstract class AbstractNode implements NodeInterface {
	public function toString() {
		return $this->getHash();
	}

	public function equals(NodeInterface $other_node) {
		return $this->getHash() === $other_node->getHash();
	}
}