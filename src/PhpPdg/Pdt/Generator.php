<?php

namespace PhpPdg\Pdt;

use PHPCfg\Func;
use PhpPdg\Graph\Graph;
use PhpPdg\Pdt\BlockCfg\GeneratorInterface as BlockCfgGeneratorInterface;

class Generator {
	/** @var BlockCfgGeneratorInterface  */
	private $block_cfg_generator;

	public function __construct(BlockCfgGeneratorInterface $block_cfg_generator) {
		$this->block_cfg_generator = $block_cfg_generator;
	}

	public function generate(Func $func) {
		$cfg = $this->block_cfg_generator->generate($func);
		$base_nodes = $cfg->getNodes();

		// Augment cfg with entry and stop nodes
		$entry = new \stdClass();
		$stop = new \stdClass();
		$cfg->addNode($entry);
		$cfg->addNode($stop);
		$cfg->addEdge($entry, $stop);
		$cfg->addEdge($entry, $func->cfg);
		foreach ($base_nodes as $block) {
			if (count($cfg->getOutgoingEdgeNodes($block)) === 0) {
				$cfg->addEdge($block, $stop);
			}
		}

		$nodes_with_entry = array_merge($base_nodes, array($entry));

		// Unfortunately, php's array_intersect function works using string equality... :-(
		// Using array_intersect with spl_object_hash is probably faster than constructing an new intersect function for objects.
		$nodes_to_hashes = new \SplObjectStorage();
		$hashes_to_nodes = [];
		foreach (array_merge($nodes_with_entry, array($stop)) as $node) {
			$hash = spl_object_hash($node);
			$nodes_to_hashes[$node] = $hash;
			$hashes_to_nodes[$hash] = $node;
		}
		$all_hashes = array_keys($hashes_to_nodes);

		$post_dominators = [];
		foreach ($nodes_with_entry as $node) {
			$post_dominators[$nodes_to_hashes[$node]] = $all_hashes;
		}
		$post_dominators[$nodes_to_hashes[$stop]] = [$nodes_to_hashes[$stop]];

		// Iteratively determine post-dominators.
		// This is probably not the fastest way to do this, but it is simple, and should be enough for now.
		do {
			$changes = false;
			foreach ($nodes_with_entry as $node) {
				$node_hash = $nodes_to_hashes[$node];

				// A node's post-dominators consist of the intersection of the post dominators of all outgoing edge nodes, and the node itself.
				$new_post_dominators = null;
				foreach ($cfg->getOutgoingEdgeNodes($node) as $to_node) {
					$to_node_post_dominator_hashes = $post_dominators[$nodes_to_hashes[$to_node]];
					if ($new_post_dominators === null) {
						$new_post_dominators = $to_node_post_dominator_hashes;
					} else {
						$new_post_dominators = array_intersect($new_post_dominators, $to_node_post_dominator_hashes);
					}
				}
				$new_post_dominators[] = $node_hash;

				// If changes, store new post dominators and ensure we do another iteration
				if (count($new_post_dominators) !== count($post_dominators[$node_hash])) {
					$post_dominators[$node_hash] = $new_post_dominators;
					$changes = true;
				}
			}
		} while ($changes === true);

		$pdt = new Graph();
		foreach ($post_dominators as $hash => $post_dominator_hashes) {
			$pdt->addNode($hashes_to_nodes[$hash]);
		}
		foreach ($post_dominators as $hash => $post_dominator_hashes) {
			foreach ($post_dominator_hashes as $post_dominator_hash) {
				$pdt->addEdge($hashes_to_nodes[$hash], $hashes_to_nodes[$post_dominator_hash]);
			}
		}
		return new Pdt($entry, $stop, $pdt);
	}
}