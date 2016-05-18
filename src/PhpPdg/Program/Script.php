<?php

namespace PhpPdg\Program;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;

class Script extends Program {
	private $script_path;

	/**
	 * Script constructor.
	 * @param string $script_path
	 * @param NodeInterface $entry_node
	 * @param GraphInterface $dependence_graph
	 */
	public function __construct($script_path, NodeInterface $entry_node, GraphInterface $dependence_graph) {
		$this->script_path = $script_path;
		parent::__construct($entry_node, $dependence_graph);
	}

	public function getScriptPath() {
		return $this->script_path;
	}

	public function getIdentifier() {
		return $this->script_path;
	}
}