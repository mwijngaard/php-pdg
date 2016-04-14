<?php

namespace PhpPdg\BaseGraph;

use PHPCfg\Func;
use PhpPdg\CfgAdapter\Traverser;
use PhpPdg\Graph\GraphInterface;

class Generator implements GeneratorInterface {
	public function addOpNodesToGraph(Func $func, GraphInterface $graph) {
		$traverser = new Traverser();
		$traverser->addVisitor(new GeneratingVisitor($graph));
		$traverser->traverseFunc($func);
	}
}