<?php

namespace PhpPdg\Graph;

use PHPCfg\Op;

class StopNode implements NodeInterface {
	public function toString() {
		return "Stop";
	}

	public function getHash() {
		return spl_object_hash($this);
	}
}