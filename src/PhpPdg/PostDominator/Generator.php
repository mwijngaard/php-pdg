<?php

namespace PhpPdg\PostDominator;

use PhpPdg\Graph\FactoryInterface;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;

class Generator implements GeneratorInterface {
	/** @var FactoryInterface  */
	private $graph_factory;

	public function __construct(FactoryInterface $graph_factory) {
		$this->graph_factory = $graph_factory;
	}

	public function generate(GraphInterface $graph, NodeInterface $stop_node) {
		$nodes_by_hash = [];
		$all_hashes = array_keys($nodes_by_hash);
		foreach ($graph->getNodes() as $node) {
			$hash = $node->getHash();
			$nodes_by_hash[$hash] = $node;
			$all_hashes[] = $hash;
		}
		// initialize all node postdominators
		$post_dominators = array_fill_keys($all_hashes, $all_hashes);
		$stop_node_hash = $stop_node->getHash();
		$post_dominators[$stop_node_hash] = [$stop_node_hash];

		// Iteratively determine post-dominators.
		// This is probably not the fastest way to do this, but it is simple, and should be enough for now.
		do {
			$changes = false;
			foreach ($nodes_by_hash as $hash => $node) {
				// A node's post-dominators consist of the intersection of the post dominators of all outgoing edge nodes, and the node itself.
				$new_post_dominators = null;
				foreach ($graph->getOutgoingEdgeNodes($node) as $to_node) {
					$to_node_post_dominator_hashes = $post_dominators[$to_node->getHash()];
					$new_post_dominators = $new_post_dominators === null ? $to_node_post_dominator_hashes : array_intersect($new_post_dominators, $to_node_post_dominator_hashes);
				}
				$new_post_dominators[] = $hash;

				// If changes, store new post dominators and ensure we do another iteration
				if (count($new_post_dominators) !== count($post_dominators[$hash])) {
					$post_dominators[$hash] = $new_post_dominators;
					$changes = true;
				}
			}
		} while ($changes === true);

		$pd_graph = $this->graph_factory->create();
		foreach ($nodes_by_hash as $node) {
			$pd_graph->addNode($node);
		}
		foreach ($post_dominators as $node_hash => $post_dominator_hashes) {
			foreach ($post_dominator_hashes as $post_dominator_hash) {
				$pd_graph->addEdge($nodes_by_hash[$node_hash], $nodes_by_hash[$post_dominator_hash]);
			}
		}
		return $pd_graph;
	}
}