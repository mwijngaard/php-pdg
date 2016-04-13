<?php

namespace PhpPdg\Pdt\BlockCfg;

use PHPCfg\Func;
use PhpPdg\CfgAdapter\Traverser;

class Generator implements GeneratorInterface {
	/**
	 * @param Func $func
	 * @return \PhpPdg\Graph\Graph
	 */
	public function generate(Func $func) {
		$traverser = new Traverser();
		$visitor = new GeneratingVisitor();
		$traverser->addVisitor($visitor);
		$traverser->traverseFunc($func);
		return $visitor->graph;
	}
}