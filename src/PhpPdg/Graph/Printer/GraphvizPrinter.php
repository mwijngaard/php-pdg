<?php

namespace PhpPdg\Graph\Printer;

use phpDocumentor\GraphViz\Graph as GvGraph;
use phpDocumentor\GraphViz\Node as GvNode;
use phpDocumentor\GraphViz\Edge as GvEdge;
use PhpPdg\Graph\GraphInterface;

class GraphvizPrinter implements PrinterInterface {
	public function printGraph(GraphInterface $graph) {
		$gv_graph = GvGraph::create();
		/** @var \SplObjectStorage|GvNode[] $node_map */
		$node_map = [];
		foreach ($graph->getNodes() as $node) {
			$gv_node = new GvNode($node->getHash(), $node->toString());
			$node_map[$node->getHash()] = $gv_node;
			$gv_graph->setNode($gv_node);
		}
		foreach ($graph->getEdges() as $edge) {
			$attributes = $edge->getAttributes();
			$gv_edge = new GvEdge($node_map[$edge->getFromNode()->getHash()], $node_map[$edge->getToNode()->getHash()]);
			if (empty($attributes) === false) {
				$gv_edge->setLabel(json_encode($edge->getAttributes()));
			}
			$gv_graph->link($gv_edge);
		}
		return (string) $gv_graph;
	}
}