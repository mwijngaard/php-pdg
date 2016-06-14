<?php

namespace PhpPdg\Slicing;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\Node\NodeInterface;

class Slicer implements SlicerInterface {
	public function slice(GraphInterface $source, NodeInterface $slicing_criterion, GraphInterface $target) {
		$worklist = [$slicing_criterion];
		while (empty($worklist) === false) {
			$to_node = array_shift($worklist);
			foreach ($source->getEdges(null, $to_node) as $incoming_edge) {
				$from_node = $incoming_edge->getFromNode();
				$target->addNode($from_node);
				$target->addEdge($from_node, $to_node, $incoming_edge->getAttributes());
				$worklist[] = $from_node;
			}
		}
	}
}