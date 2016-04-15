<?php

namespace PhpPdg\ControlDependence;

use PHPCfg\Func;
use PhpPdg\Graph\GraphInterface;

interface GeneratorInterface {
	/**
	 * Add control dependence edges to a graph (must already contain nodes)
	 *
	 * @param Func $func
	 * @param GraphInterface $target_graph
	 */
	public function addControlDependencesToGraph(Func $func, GraphInterface $target_graph);
}