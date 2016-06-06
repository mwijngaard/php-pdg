<?php

namespace PhpPdg\ProgramDependence\Node;

use PhpPdg\Graph\Node\AbstractNode;

class EntryNode extends AbstractNode {
	public function getHash() {
		return "ENTRY";
	}
}