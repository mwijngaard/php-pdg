<?php

namespace PhpPdg\ControlDependence\Block;

use PHPCfg\Func;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;

interface GeneratorInterface {
	/**
	 * @param Func $func
	 * @param NodeInterface $entry_node
	 * @param NodeInterface $stop_node
	 * @return GraphInterface
	 */
	public function generateControlDependenceGraph(Func $func, NodeInterface $entry_node, NodeInterface $stop_node);
}