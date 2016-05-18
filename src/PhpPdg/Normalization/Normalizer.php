<?php

namespace PhpPdg\Normalization;

use PhpPdg\Graph\NodeInterface;
use PhpPdg\Graph\Normalization\Normalizer as GraphNormalizerInterface;
use PhpPdg\Program\FunctionLike;
use PhpPdg\Program\Program;

class Normalizer implements NormalizerInterface {
	private $graph_normalizer;

	public function __construct(GraphNormalizerInterface $graph_normalizer) {
		$this->graph_normalizer = $graph_normalizer;
	}

	public function normalizeProgram(Program $program) {
		$struct = [];
		$struct['Type'] = get_class($program);
		$struct['Identifier'] = $program->getIdentifier();
		$struct['EntryNode'] = $program->entry_node->toString();
		if ($program instanceof FunctionLike) {
			$struct['ParamNodes'] = $this->normalizeNodes($program->param_nodes);
		}
		$struct['CallNodes'] = $this->normalizeNodes($program->call_nodes);
		$struct['ReturnNodes'] = $this->normalizeNodes($program->return_nodes);
		$struct['ClosureNodes'] = $this->normalizeNodes($program->closure_nodes);
		$struct['DependenceGraph'] = $this->graph_normalizer->normalizeGraph($program->dependence_graph);
		return $struct;
	}

	private function normalizeNodes($nodes) {
		return array_map(function (NodeInterface $return_node) {
			return $return_node->toString();
		}, $nodes);
	}
}