<?php

namespace PhpPdg\Pdt\BlockCfg;

use PHPCfg\Func;
use PhpPdg\CfgAdapter\Traverser;
use PhpPdg\Graph\Graph;
use PhpPdg\Graph\GraphInterface;

class Generator implements GeneratorInterface {
	/**
	 * @param Func $func
	 * @return GraphInterface
	 */
	public function generate(Func $func) {
		$traverser = new Traverser();
		$graph = new Graph();
		$visitor = new GeneratingVisitor($graph);
		$traverser->addVisitor($visitor);
		$traverser->traverseFunc($func);
		return $graph;
	}
}