<?php

namespace PhpPdg\ControlDependence\Block\Cfg;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;
use PhpPdg\Nodes\BlockNode;

class GeneratingVisitor extends AbstractVisitor {
	/** @var GraphInterface */
	public $graph;
	/** @var  NodeInterface */
	public $entry_node;
	/** @var  NodeInterface */
	public $stop_node;
	/** @var  Block */
	private $last_block_seen;

	public function __construct(GraphInterface $graph, NodeInterface $entry_node, NodeInterface $stop_node) {
		$this->graph = $graph;
		$this->entry_node = $entry_node;
		$this->stop_node = $stop_node;
	}

	public function enterBlock(Block $block, Block $prior = null) {
		$this->last_block_seen = $block;
		$this->graph->addNode(new BlockNode($block));
	}

	public function leaveOp(Op $op, Block $block) {
		if ($op instanceof Op\Stmt\Jump) {
			$this->graph->addEdge(new BlockNode($block), new BlockNode($op->target));
		} else if ($op instanceof Op\Stmt\JumpIf) {
			$from_block_node = new BlockNode($block);
			$this->graph->addEdge($from_block_node, new BlockNode($op->if), [
				'case' => true
			]);
			$this->graph->addEdge($from_block_node, new BlockNode($op->else), [
				'case' => false
			]);
		} else if ($op instanceof Op\Stmt\Switch_) {
			$from_block_node = new BlockNode($block);
			foreach ($op->cases as $i => $case) {
				$this->graph->addEdge($from_block_node, new BlockNode($op->targets[$i]), [
					'case' => $case
				]);
			}
			if ($op->default !== null) {
				$this->graph->addEdge($from_block_node, new BlockNode($op->default), [
					'case' => null
				]);
			}
		}
	}

	public function skipBlock(Block $block, Block $prior = null) {
		$this->last_block_seen = $block;
	}

	public function leaveBlock(Block $block, Block $prior = null) {
		if ($this->last_block_seen === $block) {
			$this->graph->addEdge(new BlockNode($block), $this->stop_node);
		}
	}
}