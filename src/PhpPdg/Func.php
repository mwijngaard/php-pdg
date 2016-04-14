<?php

namespace PhpPdg;

use PHPCfg\Op;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;

class Func {
	/** @var  string */
	private $name;
	/** @var  string */
	private $class;
	/** @var NodeInterface  */
	private $entry_node;
	/** @var NodeInterface */
	private $stop_node;
	/** @var GraphInterface */
	private $pdg;

	/**
	 * Func constructor.
	 * @param string $name
	 * @param string $class
	 * @param NodeInterface $entry_node
	 * @param NodeInterface $stop_node
	 * @param GraphInterface $pdg
	 */
	public function __construct($name, $class, NodeInterface $entry_node, NodeInterface $stop_node, GraphInterface $pdg) {
		$this->name = $name;
		$this->class = $class;
		$this->entry_node = $entry_node;
		$this->stop_node = $stop_node;
		$this->pdg = $pdg;
	}
}