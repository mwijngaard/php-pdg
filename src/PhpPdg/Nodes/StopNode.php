<?php

namespace PhpPdg\Nodes;

use PHPCfg\Op;
use PhpPdg\Graph\AbstractNode;
use PhpPdg\Graph\NodeInterface;

class StopNode extends AbstractNode {
	public function toString() {
		return "Stop";
	}

	public function getHash() {
		return 'STOP';
	}
}