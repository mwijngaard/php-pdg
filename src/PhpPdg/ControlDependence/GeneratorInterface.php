<?php

namespace PhpPdg\ControlDependence;

use PHPCfg\Func;
use PhpPdg\Graph\GraphInterface;

interface GeneratorInterface {
	/**
	 * Add control dependences edges to a graph (must already contain nodes)
	 *
	 * @param Func $func
	 * @param GraphInterface $graph
	 * @param string $edge_type
	 */
	public function addControlDependencesToGraph(Func $func, GraphInterface $graph, $edge_type = '');
}