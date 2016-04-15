<?php

namespace PhpPdg\ControlDependence;

use PHPCfg\Func;
use PhpPdg\CfgAdapter\Traverser;
use PhpPdg\ControlDependence\Block\GeneratorInterface as BlockControlDependenceGeneratorInterface;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\EntryNode;
use PhpPdg\Nodes\StopNode;

class Generator implements GeneratorInterface {
	/** @var BlockControlDependenceGeneratorInterface  */
	private $block_control_dependence_generator;
	/** @var string  */
	private $edge_type;

	/**
	 * Generator constructor.
	 * @param BlockControlDependenceGeneratorInterface $block_control_dependence_generator
	 * @param string $edge_type
	 */
	public function __construct(BlockControlDependenceGeneratorInterface $block_control_dependence_generator, $edge_type = 'control') {
		$this->block_control_dependence_generator = $block_control_dependence_generator;
		$this->edge_type = $edge_type;
	}

	public function addControlDependencesToGraph(Func $func, GraphInterface $target_graph) {
		$block_dependence_graph = $this->block_control_dependence_generator->generateControlDependenceGraph($func, new EntryNode(), new StopNode());
		$traverser = new Traverser();
		$traverser->addVisitor(new GeneratingVisitor($target_graph, $block_dependence_graph, $this->edge_type));
		$traverser->traverseFunc($func);
	}
}