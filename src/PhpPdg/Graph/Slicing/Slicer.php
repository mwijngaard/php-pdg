<?php

namespace PhpPdg\Graph\Slicing;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\Node\NodeInterface;

class Slicer implements SlicerInterface {
	public function slice(GraphInterface $source, array $slicing_criterion, GraphInterface $target) {
		$nodes_seen = [];
		$worklist = [];
		/** @var NodeInterface $node */
		foreach ($slicing_criterion as $node) {
			$nodes_seen[$node->getHash()] = 1;
			$worklist[] = $node;
			$target->addNode($node);
		}
		while (empty($worklist) === false) {
			$to_node = array_shift($worklist);
			foreach ($source->getEdges(null, $to_node) as $incoming_edge) {
				$from_node = $incoming_edge->getFromNode();
				if (isset($nodes_seen[$from_node->getHash()]) === false) {
					$nodes_seen[$from_node->getHash()] = 1;
					$worklist[] = $from_node;
					$target->addNode($from_node);
				}
				$target->addEdge($from_node, $to_node, $incoming_edge->getAttributes());
			}
		}
	}
}