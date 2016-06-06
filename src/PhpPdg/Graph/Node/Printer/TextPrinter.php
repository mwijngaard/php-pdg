<?php

namespace PhpPdg\Graph\Node\Printer;

use PhpPdg\Graph\Node\NodeInterface;

class TextPrinter implements PrinterInterface {
	private $node_id_map = [];

	public function printNode(NodeInterface $node) {
		$hash = $node->getHash();
		if (isset($this->node_id_map[$hash]) === false) {
			$this->node_id_map[$hash] = count($this->node_id_map);
		}
		return sprintf('#%d %s', $this->node_id_map[$hash], $node->toString());
	}
}