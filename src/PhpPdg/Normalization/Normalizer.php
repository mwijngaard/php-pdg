<?php

namespace PhpPdg\Normalization;

use PhpPdg\Func;
use PhpPdg\Graph\NodeInterface;
use PhpPdg\Graph\Normalization\Normalizer as GraphNormalizerInterface;

class Normalizer implements NormalizerInterface {
	private $graph_normalizer;

	public function __construct(GraphNormalizerInterface $graph_normalizer) {
		$this->graph_normalizer = $graph_normalizer;
	}

	public function normalizeFunc(Func $func) {
		$struct = [];
		$struct['Name'] = $func->name;
		$struct['Class'] = $func->class;
		$struct['EntryNode'] = $func->entry_node->toString();
		$struct['CallNodes'] = $this->normalizeNodes($func->call_nodes);
		$struct['ReturnNodes'] = $this->normalizeNodes($func->return_nodes);
		$struct['DependenceGraph'] = $this->graph_normalizer->normalizeGraph($func->dependence_graph);
		return $struct;
	}

	private function normalizeNodes($nodes) {
		return array_map(function (NodeInterface $return_node) {
			return $return_node->toString();
		}, $nodes);
	}
}