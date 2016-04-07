<?php

namespace PhpPdg\Graph;

use PhpParser\Node;

class DirectionalGraph {
	/** @var \SplObjectStorage  */
	private $outgoing;
	/** @var \SplObjectStorage  */
	private $incoming;

	public function __construct() {
		$this->incoming = new \SplObjectStorage();
		$this->outgoing = new \SplObjectStorage();
	}

	/**
	 * @param object $from
	 * @param object $to
	 */
	public function add($from, $to) {
		if ($this->incoming->contains($from) === false) {
			$this->incoming->attach($from, new \SplObjectStorage());
		}
		$this->incoming[$from]->attach($to);
		if ($this->outgoing->contains($to) === false) {
			$this->outgoing->attach($to, new \SplObjectStorage());
		}
		$this->outgoing[$to]->attach($from);
	}

	/**
	 * @param object $from
	 * @return object[]|false
	 */
	public function getOutgoing($from) {
		if ($this->incoming->contains($from) === false) {
			return false;
		}
		return $this->incoming[$from];
	}

	/**
	 * @param object $to
	 * @return object[]|false
	 */
	public function getIncoming($to) {
		if ($this->outgoing->contains($to) === false) {
			return false;
		}
		return $this->outgoing[$to];
	}
}