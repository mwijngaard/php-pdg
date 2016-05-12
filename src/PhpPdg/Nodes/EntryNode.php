<?php

namespace PhpPdg\Nodes;

use PHPCfg\Op;
use PhpPdg\Graph\NodeInterface;

class EntryNode implements NodeInterface {
	public function toString() {
		return 'Entry';
	}

	public function getHash() {
		return 'ENTRY';
	}
}