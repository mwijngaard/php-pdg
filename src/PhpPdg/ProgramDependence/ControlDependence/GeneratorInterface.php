<?php

namespace PhpPdg\ProgramDependence\ControlDependence;

use PHPCfg\Func;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\Node\NodeInterface;

interface GeneratorInterface {
	/**
	 * Add control dependence edges to a graph (must already contain nodes)
	 *
	 * @param Func $func
	 * @param GraphInterface $target_graph
	 * @param NodeInterface $entry_node
	 */
	public function addFuncControlDependenceEdgesToGraph(Func $func, GraphInterface $target_graph, NodeInterface $entry_node);
}