<?php

namespace PhpPdg\DataDependence;

use PHPCfg\Func;
use PhpPdg\Graph\GraphInterface;

interface GeneratorInterface {
	/**
	 * Add data dependence edges to a graph (must already contain nodes)
	 *
	 * @param Func $func
	 * @param GraphInterface $graph
	 * @param string $edge_type
	 */
	public function addDataDependencesToGraph(Func $func, GraphInterface $graph, $edge_type = '');
}