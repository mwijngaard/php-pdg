<?php

namespace PhpPdg\Graph;

use PHPCfg\Op;

class EntryNode implements NodeInterface {
	public function toString() {
		return "Entry";
	}

	public function getHash() {
		return 'ENTRY';
	}
}