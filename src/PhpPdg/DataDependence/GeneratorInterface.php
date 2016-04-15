<?php

namespace PhpPdg\DataDependence;

use PHPCfg\Func;
use PhpPdg\Graph\GraphInterface;

interface GeneratorInterface {
	/**
	 * Add data dependence edges to a graph (must already contain nodes)
	 *
	 * @param Func $func
	 * @param GraphInterface $target_graph
	 */
	public function addDataDependencesToGraph(Func $func, GraphInterface $target_graph);
}