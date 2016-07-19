<?php

namespace PhpPdg\ProgramDependence\DataDependence;

use PHPCfg\AbstractVisitor;
use PHPCfg\Block;
use PHPCfg\Op;
use PHPCfg\Operand;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\ProgramDependence\Node\OpNode;

class MaybeGeneratingVisitor extends AbstractVisitor {
	/** @var GraphInterface  */
	private $target_graph;
	/** @var string  */
	private $edge_type;

	/**
	 * GeneratingVisitor constructor.
	 * @param GraphInterface $target_graph
	 * @param string $edge_type
	 * @param \SplObjectStorage $block_writes
	 * @param \SplObjectStorage $block_reads
	 */
	public function __construct(GraphInterface $target_graph, $edge_type) {
		$this->target_graph = $target_graph;
		$this->edge_type = $edge_type;
	}

	public function enterBlock(Block $block, Block $prior = null) {
		for ($i = 0; $i < count($block->children); $i++) {
			$op = $block->children[$i];
			if ($op instanceof Op\Expr\Eval_) {
				$eval_node = new OpNode($op);
				$this->addOutgoingMaybeDependences($eval_node, $block, $i - 1);
				$this->addIncomingMaybeDependences($eval_node, $block, $i + 1);
			}
		}
	}

	private function addOutgoingMaybeDependences(OpNode $source_node, Block $block, $offset, $mask = [], $path = []) {
		for ($i = $offset; $i >= 0; $i--) {
			$op = $block->children[$i];
			foreach ($op->getVariableNames() as $var_name) {
				if ($op->isWriteVariable($var_name) === true) {
					foreach ((array) $op->$var_name as $operand) {
						if ($operand instanceof Operand\Variable) {
							if (isset($mask[$operand->name->value]) === false) {
								$target_node = new OpNode($op);
								if ($this->target_graph->hasEdges($source_node, $target_node, ['type' => $this->edge_type]) === false) {
									$this->target_graph->addEdge($source_node, $target_node, ['type' => $this->edge_type]);
								}
								$mask[$operand->name->value] = 1;
							}
						}
					}
				}
			}
		}
		$path[] = $block;
		foreach ($block->parents as $parent_block) {
			if (in_array($parent_block, $path, true) === false) {
				$this->addOutgoingMaybeDependences($source_node, $parent_block, count($parent_block->children) - 1, $mask, $path);
			}
		}
	}

	private function addIncomingMaybeDependences(OpNode $target_node, Block $block, $offset, $mask = [], $path = []) {
		$children_ct = count($block->children);
		if ($children_ct > 0) {
			for ($i = $offset; $i < $children_ct; $i++) {
				$op = $block->children[$i];
				foreach ($op->getVariableNames() as $var_name) {
					if ($op->isWriteVariable($var_name) === false) {
						foreach ((array) $op->$var_name as $operand) {
							if ($operand instanceof Operand\Variable) {
								if (isset($mask[$operand->name->value]) === false) {
									$source_node = new OpNode($op);
									if ($this->target_graph->hasEdges($source_node, $target_node, ['type' => $this->edge_type]) === false) {
										$this->target_graph->addEdge($source_node, $target_node, ['type' => $this->edge_type]);
									}
								}
							}
						}
					} else {
						foreach ((array) $op->$var_name as $operand) {
							if ($operand instanceof Operand\Variable) {
								$mask[$operand->name->value] = 1;
							}
						}
					}
				}
			}
			$path[] = $block;
			$last_child = $block->children[$children_ct - 1];
			$target_blocks = [];
			if ($last_child instanceof Op\Stmt\Jump) {
				$target_blocks[] = $last_child->target;
			} else if ($last_child instanceof Op\Stmt\JumpIf) {
				$target_blocks[] = $last_child->if;
				$target_blocks[] = $last_child->else;
			} else if ($last_child instanceof Op\Stmt\Switch_) {
				foreach ($last_child->targets as $target) {
					$target_blocks[] = $target;
				}
			}
			foreach ($target_blocks as $target_block) {
				if (in_array($target_block, $path, true) === false) {
					$this->addIncomingMaybeDependences($target_node, $target_block, 0, $mask, $path);
				}
			}
		}
	}
}