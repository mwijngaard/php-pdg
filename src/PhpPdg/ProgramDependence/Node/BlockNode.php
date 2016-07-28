<?php

namespace PhpPdg\ProgramDependence\Node;

use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\Graph\Node\AbstractNode;

class BlockNode extends AbstractNode {
	/** @var Block  */
	public $block;
	private $startLine = -1;
	private $endLine = -1;

	/**
	 * BlockNode constructor.
	 * @param Block $block
	 */
	public function __construct(Block $block) {
		$this->block = $block;
		if (empty($this->block->children) === false) {
			$this->startLine = $this->block->children[0]->getLine();
			foreach ($this->block->children as $op) {
				$this->endLine = max($this->endLine, $op->getLine());
			}
		}
	}

	public function toString() {
		return sprintf('Block@%d:%d', $this->startLine, $this->endLine);
	}

	public function getHash() {
		return sprintf('BLOCK@%d:%d(%s)', $this->startLine, $this->endLine, spl_object_hash($this->block));
	}
}