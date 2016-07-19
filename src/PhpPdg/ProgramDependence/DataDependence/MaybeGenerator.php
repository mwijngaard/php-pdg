<?php

namespace PhpPdg\ProgramDependence\DataDependence;

use PHPCfg\Func;
use PHPCfg\Traverser;
use PhpPdg\Graph\GraphInterface;

class MaybeGenerator implements GeneratorInterface {
	/** @var string  */
	private $edge_type;

	/**
	 * Factory constructor.
	 * @param string $edge_type
	 */
	public function __construct($edge_type = 'maybe data') {
		$this->edge_type = $edge_type;
	}

	public function addFuncDataDependenceEdgesToGraph(Func $func, GraphInterface $target_graph) {
		$traverser = new Traverser();
		$traverser->addVisitor(new MaybeGeneratingVisitor($target_graph, $this->edge_type));
		$traverser->traverseFunc($func);
	}
}