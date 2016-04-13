<?php

namespace PhpPdg\CfgAdapter;

use PHPCfg\Block;
use PHPCfg\Func;
use PHPCfg\Op;

class Traverser {
	private $visitors = array();
	/** @var  \SplObjectStorage */
	private $seen;

	public function addVisitor(VisitorInterface $visitor) {
		$this->visitors[] = $visitor;
	}

	public function traverseFunc(Func $func) {
		$this->event('beforeTraverse');
		$this->seen = new \SplObjectStorage();
		$this->traverseBlock($func->cfg);
		$this->event('afterTraverse');
	}

	private function traverseBlock(Block $block, Block $prior = null) {
		if ($this->seen->contains($block)) {
			$this->event('skipBlock', [$block, $prior]);
			return;
		}
		$this->seen->attach($block);
		$this->event('enterBlock', [$block, $prior]);
		/** @var Op $op */
		foreach ($block->children as $op) {
			$this->event('enterOp', [$op, $block]);
			/** @var Block $sub_block_name */
			foreach ($op->getSubBlocks() as $sub_block_name) {
				$this->traverseBlock($op->$sub_block_name, $block);
			}
			$this->event('leaveOp', [$op, $block]);
		}
		$this->event('leaveBlock', [$block, $prior]);
	}

	private function event($event, $args = []) {
		foreach ($this->visitors as $visitor) {
			call_user_func_array(array($visitor, $event), $args);
		}
	}
}