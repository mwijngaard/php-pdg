<?php

namespace PhpPdg\Normalization;

use PhpPdg\Func;
use PhpPdg\Graph\NodeInterface;
use PhpPdg\Graph\Normalization\Normalizer as GraphNormalizerInterface;
use PhpPdg\System;

class Normalizer implements NormalizerInterface {
	private $graph_normalizer;

	public function __construct(GraphNormalizerInterface $graph_normalizer) {
		$this->graph_normalizer = $graph_normalizer;
	}

	public function normalizeSystem(System $system) {
		return [
			'Scripts' => $this->normalizeFuncs($system->scripts),
			'Functions' => $this->normalizeFuncs($system->functions),
			'Methods' => $this->normalizeFuncs($system->methods),
			'Closures' => $this->normalizeFuncs($system->closures),
			'Graph' => $this->graph_normalizer->normalizeGraph($system->graph)
		];
	}

	public function normalizeFunc(Func $func) {
		return [
			'Name' => $func->name,
			'Class Name' => $func->class_name,
			'Entry Node' => $func->entry_node->toString(),
			'Return Nodes' => $this->normalizeNodes($func->return_nodes),
			'Exceptional Return Nodes' => $this->normalizeNodes($func->exceptional_return_nodes)
		];
	}

	private function normalizeFuncs($funcs) {
		return array_map(function (Func $func) {
			return $this->normalizeFunc($func);
		}, $funcs);
	}

	private function normalizeNodes($nodes) {
		return array_map(function (NodeInterface $return_node) {
			return $return_node->toString();
		}, $nodes);
	}
}