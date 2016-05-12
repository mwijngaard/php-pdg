<?php

namespace PhpPdg\Normalization;

use PhpPdg\Func;
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
		$struct['DependenceGraph'] = $this->graph_normalizer->normalize($func->dependence_graph);
		return $struct;
	}
}