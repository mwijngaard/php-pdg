<?php

namespace PhpPdg\Graph;

abstract class AbstractNode implements NodeInterface {
	public function equals(NodeInterface $other_node) {
		return $this->getHash() === $other_node->getHash();
	}
}