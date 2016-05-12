<?php

namespace PhpPdg\Nodes;

use PHPCfg\Block;
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
		return $this->getHash();
	}

	public function getHash() {
		return 'BLOCK(' . spl_object_hash($this->block) . ')';
	}
}