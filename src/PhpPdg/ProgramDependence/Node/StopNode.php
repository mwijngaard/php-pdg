<?php

namespace PhpPdg\ProgramDependence\Node;

use PhpPdg\Graph\Node\AbstractNode;

class StopNode extends AbstractNode {
	public function getHash() {
		return 'STOP';
	}
}