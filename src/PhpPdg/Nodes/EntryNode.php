<?php

namespace PhpPdg\Nodes;

use PHPCfg\Op;
use PhpPdg\Graph\AbstractNode;
use PhpPdg\Graph\NodeInterface;

class EntryNode extends AbstractNode {
	private $id;

	/**
	 * EntryNode constructor.
	 * @param string $id
	 */
	public function __construct($id) {
		$this->id = $id;
	}

	public function toString() {
		return "Entry($this->id)";
	}

	public function getHash() {
		return "ENTRY($this->id)";
	}
}