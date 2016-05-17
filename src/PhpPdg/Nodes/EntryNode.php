<?php

namespace PhpPdg\Nodes;

use PHPCfg\Op;
use PhpPdg\Graph\AbstractNode;
use PhpPdg\Graph\NodeInterface;

class EntryNode extends AbstractNode {

	public function toString() {
		return 'Entry';
	}

	public function getHash() {
		return 'ENTRY';
	}
}