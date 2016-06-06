<?php

namespace PhpPdg\ProgramDependence\DataDependence;

use PHPCfg\Func;
use PHPCfg\Traverser;
use PhpPdg\Graph\GraphInterface;

class Generator implements GeneratorInterface {
	/** @var string  */
	private $edge_type;

	/**
	 * Factory constructor.
	 * @param string $edge_type
	 */
	public function __construct($edge_type = 'data') {
		$this->edge_type = $edge_type;
	}

	public function addFuncDataDependenceEdgesToGraph(Func $func, GraphInterface $target_graph, $edge_type = 'data') {
		$traverser = new Traverser();
		$traverser->addVisitor(new GeneratingVisitor($target_graph, $edge_type));
		$traverser->traverseFunc($func);
	}
}