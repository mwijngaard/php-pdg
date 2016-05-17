<?php

namespace PhpPdg\Graph\Normalization;

use PhpPdg\Graph\GraphInterface;

class Normalizer implements NormalizerInterface {
	public function normalizeGraph(GraphInterface $graph) {
		$nodes = [];
		foreach ($graph->getNodes() as $node) {
			$nodes[] = $node->toString();
		}
		$edges = [];
		foreach ($graph->getEdges() as $edge) {
			$edges[] = [
				'From' => $edge->getFromNode()->toString(),
				'To' => $edge->getToNode()->toString(),
				'Attributes' => $edge->getAttributes(),
			];
		}
		return [
			'Nodes' => $nodes,
			'Edges' => $edges,
		];
	}
}