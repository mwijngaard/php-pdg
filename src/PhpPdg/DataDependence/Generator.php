<?php

namespace PhpPdg\DataDependence;

use PHPCfg\Func;
use PhpPdg\CfgAdapter\Traverser;
use PhpPdg\Graph\GraphInterface;

class Generator implements GeneratorInterface {
	public function addDataDependencesToGraph(Func $func, GraphInterface $graph, $edge_type = '') {
		$traverser = new Traverser();
		$traverser->addVisitor(new GeneratingVisitor($graph, $edge_type));
		$traverser->traverseFunc($func);
	}
}