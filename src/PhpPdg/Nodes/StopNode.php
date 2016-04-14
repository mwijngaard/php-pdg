<?php

namespace PhpPdg\Nodes;

use PHPCfg\Op;
use PhpPdg\Graph\NodeInterface;

class StopNode implements NodeInterface {
	public function toString() {
		return "Stop";
	}

	public function getHash() {
		return 'STOP';
	}
}