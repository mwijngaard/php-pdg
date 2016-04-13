<?php

namespace PhpPdg\CfgAdapter;

use PHPCfg\Block;
use PHPCfg\Op;

interface VisitorInterface {
	public function beforeTraverse();
	public function afterTraverse();
	public function skipBlock(Block $block, Block $prior = null);
	public function enterBlock(Block $block, Block $prior = null);
	public function enterOp(Op $op, Block $block);
	public function leaveOp(Op $op, Block $block);
	public function leaveBlock(Block $block, Block $prior = null);
}