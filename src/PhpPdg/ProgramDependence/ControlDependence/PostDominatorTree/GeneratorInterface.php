<?php

namespace PhpPdg\ProgramDependence\ControlDependence\PostDominatorTree;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\Node\NodeInterface;

interface GeneratorInterface {
	/**
	 * @param GraphInterface $graph
	 * @param NodeInterface $stop_node
	 * @return GraphInterface
	 */
	public function generate(GraphInterface $graph, NodeInterface $stop_node);
}