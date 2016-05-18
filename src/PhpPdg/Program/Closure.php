<?php

namespace PhpPdg\Program;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;

class Closure extends FunctionLike {
	/** @var string  */
	private $identifier;

	/**
	 * Closure constructor.
	 * @param string $identifier
	 * @param NodeInterface $entry_node
	 * @param GraphInterface $dependence_graph
	 */
	public function __construct($identifier, NodeInterface $entry_node, GraphInterface $dependence_graph) {
		$this->identifier = $identifier;
		parent::__construct($entry_node, $dependence_graph);
	}

	public function getIdentifier() {
		return $this->identifier;
	}
}