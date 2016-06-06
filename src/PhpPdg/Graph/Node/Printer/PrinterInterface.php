<?php

namespace PhpPdg\Graph\Node\Printer;

use PhpPdg\Graph\Node\NodeInterface;

interface PrinterInterface {
	public function printNode(NodeInterface $node);
}