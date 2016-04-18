<?php

namespace PhpPdg\ControlDependence\Block\Cdg;

use PhpPdg\Graph\FactoryInterface;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;

class Generator implements GeneratorInterface {
	private $graph_factory;

	public function __construct(FactoryInterface $graph_factory) {
		$this->graph_factory = $graph_factory;
	}

	public function generate(GraphInterface $cfg, GraphInterface $pdt) {
		$cdg = $this->graph_factory->create();
		foreach ($cfg->getNodes() as $node) {
			$cdg->addNode($node);
		}
		foreach ($cfg->getNodes() as $node_a) {
			foreach ($cfg->getOutgoingEdgeNodes($node_a) as $node_b) {
				// Evaluate all CFG edges as A-B where B does not post-dominate A
				if ($pdt->hasEdge($node_a, $node_b) === false) {
					$this->addNodeControlDependences($cdg, $pdt, $node_a, $node_b);
				}
			};
		}
		return $cdg;
	}

	private function addNodeControlDependences(GraphInterface $cdg, GraphInterface $pdt, NodeInterface $node_a, NodeInterface $node_from_b_to_l) {
		$cdg->addEdge($node_from_b_to_l, $node_a);
		if ($node_a->getHash() !== $node_from_b_to_l->getHash()) {
			$node_from_b_to_l_parent = $pdt->getOutgoingEdgeNodes($node_from_b_to_l)[0];
			if ($pdt->hasEdge($node_a, $node_from_b_to_l_parent) === false) {
				$this->addNodeControlDependences($cdg, $pdt, $node_a, $node_from_b_to_l_parent);
			}
		}
	}
}