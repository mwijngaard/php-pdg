<?php

namespace PhpPdg\Graph\Node;

abstract class AbstractNode implements NodeInterface {
	public function toString() {
		return $this->getHash();
	}
}