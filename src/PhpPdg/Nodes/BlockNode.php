<?php

namespace PhpPdg\Nodes;

use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\Graph\NodeInterface;

class BlockNode implements NodeInterface {
	/** @var Block  */
	public $block;

	/**
	 * BlockNode constructor.
	 * @param Block $block
	 */
	public function __construct(Block $block) {
		$this->block = $block;
	}

	public function toString() {
		return sprintf('Block [%s]', implode(', ', array_map(function (Op $op) {
			$startLine = $op->getAttribute('startLine');
			$endLine = $op->getAttribute('endLine');
			$lines = $startLine === $endLine ? $startLine : $startLine . ':' . $endLine;
			return sprintf('Op %s @ line %s', str_replace("PHPCfg\\Op\\", '', get_class($op)), $lines);
		}, $this->block->children)));
	}

	public function getHash() {
		return 'BLOCK(' . spl_object_hash($this->block) . ')';
	}
}