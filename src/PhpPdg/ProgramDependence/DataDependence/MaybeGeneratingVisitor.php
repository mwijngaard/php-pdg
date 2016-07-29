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
	 */
	public function __construct(GraphInterface $target_graph, $edge_type) {
		$this->target_graph = $target_graph;
		$this->edge_type = $edge_type;
	}

	public function enterBlock(Block $block, Block $prior = null) {
		for ($i = 0; $i < count($block->children); $i++) {
			$op = $block->children[$i];

			if ($op instanceof Op\Expr\Eval_) {
				$op_node = new OpNode($op);
				$this->addReadMaybeDependences($op_node, $block, $i - 1);
				$this->addWriteMaybeDependences($op_node, $block, $i + 1);
			} else {
				foreach ($op->getVariableNames() as $variableName) {
					$operands = is_array($op->$variableName) === true ? $op->$variableName : [$op->$variableName];
					foreach ($operands as $operand) {
						if ($operand !== null) {
							assert($operand instanceof Operand);
							/** @var Operand $operand */
							if ($operand instanceof Operand\Variable && $operand->name instanceof Operand\Literal === false) {
								$op_node = new OpNode($op);
								if ($op->isWriteVariable($variableName) === false) {
									$this->addReadMaybeDependences($op_node, $block, $i - 1);
								} else {
									$this->addWriteMaybeDependences($op_node, $block, $i + 1);
								}
							}
						}
					}
				}
			}
		}
	}

	private function addReadMaybeDependences(OpNode $op_node, Block $block, $offset, $mask = [], $path = []) {
		for ($i = $offset; $i >= 0; $i--) {
			$op = $block->children[$i];
			foreach ($op->getVariableNames() as $var_name) {
				if ($op->isWriteVariable($var_name) === true) {
					$operands = is_array($op->$var_name) === true ? $op->$var_name : [$op->$var_name];
					foreach ($operands as $operand) {
						$variable = $this->getOperandVariable($operand);
						if ($variable !== null) {
							if ($this->isMasked($variable, $mask) === false) {
								$mask = $this->mask($variable, $mask);
								$source_node = new OpNode($op);
								if ($this->target_graph->hasEdges($source_node, $op_node, ['type' => $this->edge_type]) === false) {
									$this->target_graph->addEdge($source_node, $op_node, ['type' => $this->edge_type]);
								}
							}
						}
					}
				}
			}
		}
		$path[] = $block;
		foreach ($block->parents as $parent_block) {
			if (in_array($parent_block, $path, true) === false) {
				$this->addReadMaybeDependences($op_node, $parent_block, count($parent_block->children) - 1, $mask, $path);
			}
		}
	}

	private function addWriteMaybeDependences(OpNode $op_node, Block $block, $offset, $mask = [], $path = []) {
		$children_ct = count($block->children);
		if ($children_ct > 0) {
			for ($i = $offset; $i < $children_ct; $i++) {
				$op = $block->children[$i];
				foreach ($op->getVariableNames() as $var_name) {
					$operands = is_array($op->$var_name) === true ? $op->$var_name : [$op->$var_name];
					if ($op->isWriteVariable($var_name) === false) {
						foreach ($operands as $operand) {
							$variable = $this->getOperandVariable($operand);
							if ($variable !== null && $this->isMasked($variable, $mask) === false) {
								$target_node = new OpNode($op);
								if ($this->target_graph->hasEdges($op_node, $target_node, ['type' => $this->edge_type]) === false) {
									$this->target_graph->addEdge($op_node, $target_node, ['type' => $this->edge_type]);
								}
							}
						}
					} else {
						foreach ($operands as $operand) {
							$variable = $this->getOperandVariable($operand);
							if ($variable !== null) {
								$mask = $this->mask($variable, $mask);
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
					$this->addWriteMaybeDependences($op_node, $target_block, 0, $mask, $path);
				}
			}
		}
	}

	private function getOperandVariable($operand) {
		if ($operand !== null) {
			assert($operand instanceof Operand);
			if ($operand instanceof Operand\Variable) {
				return $operand;
			}
			if ($operand instanceof Operand\Temporary && $operand->original !== null && $operand->original instanceof Operand\Variable) {
				return $operand->original;
			}
		}
		return null;
	}

	private function isMasked(Operand\Variable $variable, array &$mask) {
		if ($variable->name instanceof Operand\Literal === false) {
			return false;
		}
		return isset($mask[$variable->name->value]);
	}

	private function mask(Operand\Variable $variable, array $mask) {
		if ($variable->name instanceof Operand\Literal) {
			$mask[$variable->name->value] = 1;
		}
		return $mask;
	}
}