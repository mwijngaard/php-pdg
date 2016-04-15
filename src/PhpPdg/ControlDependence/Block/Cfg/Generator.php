<?php

namespace PhpPdg\ControlDependence\Block\Cfg;

use PHPCfg\Func;
use PhpPdg\CfgAdapter\Traverser;
use PhpPdg\Graph\FactoryInterface;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;
use PhpPdg\Nodes\BlockNode;

class Generator implements GeneratorInterface {
	/** @var FactoryInterface  */
	private $graph_factory;

	public function __construct(FactoryInterface $graph_factory) {
		$this->graph_factory = $graph_factory;
	}

	public function generate(Func $func, NodeInterface $entry_node, NodeInterface $stop_node) {
		$traverser = new Traverser();
		$graph = $this->graph_factory->create();
		$graph->addNode($entry_node);
		$graph->addNode($stop_node);
		$visitor = new GeneratingVisitor($graph, $entry_node, $stop_node);
		$traverser->addVisitor($visitor);
		$traverser->traverseFunc($func);
		return $graph;
	}
}