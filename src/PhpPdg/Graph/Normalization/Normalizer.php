<?php

namespace PhpPdg\Graph\Normalization;

use PhpPdg\Graph\GraphInterface;

class Normalizer implements NormalizerInterface {
	public function normalize(GraphInterface $graph) {
		$struct = [];
		foreach ($graph->getNodes() as $node) {
			$out = [];
			$in = [];
			foreach ($graph->getOutgoingEdgeTypes($node) as $edge_type) {
				foreach ($graph->getOutgoingEdgeNodes($node, $edge_type) as $to_node) {
					$out[$edge_type][$to_node->getHash()] = $to_node->toString();
				}
			}
			foreach ($graph->getIncomingEdgeTypes($node) as $edge_type) {
				foreach ($graph->getIncomingEdgeNodes($node, $edge_type) as $from_node) {
					$in[$edge_type][$from_node->getHash()] = $from_node->toString();
				}
			}
			$struct[$node->getHash()] = [
				'Node' => $node->toString(),
				'Out' => $out,
				'In' => $in,
			];
		}
		return $struct;
	}
}