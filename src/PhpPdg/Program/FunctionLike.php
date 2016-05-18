<?php

namespace PhpPdg\Program;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;

abstract class FunctionLike extends Program {
	/** @var NodeInterface[] */
	public $param_nodes = [];

	public function __construct(NodeInterface $entry_node, GraphInterface $dependence_graph) {
		parent::__construct($entry_node, $dependence_graph);
	}
}