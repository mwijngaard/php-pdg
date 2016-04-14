<?php

namespace PhpPdg\Nodes;

use PHPCfg\Op;
use PhpPdg\Graph\NodeInterface;

class EntryNode implements NodeInterface {
	public function toString() {
		return $this->getHash();
	}

	public function getHash() {
		return 'ENTRY';
	}
}