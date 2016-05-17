<?php

namespace PhpPdg\Graph;

class Edge {
	private $from_node;
	private $to_node;
	private $attributes;

	public function __construct(NodeInterface $from_node, NodeInterface $to_node, $attributes = []) {
		$this->from_node = $from_node;
		$this->to_node = $to_node;
		$this->attributes = $attributes;
	}

	/**
	 * @return NodeInterface
	 */
	public function getFromNode() {
		return $this->from_node;
	}

	/**
	 * @return NodeInterface
	 */
	public function getToNode() {
		return $this->to_node;
	}

	/**
	 * @return array
	 */
	public function getAttributes() {
		return $this->attributes;
	}
}