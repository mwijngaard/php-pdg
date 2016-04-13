<?php

namespace PhpPdg\Pdt;

class Pdt {
	public $entry;
	public $stop;
	public $graph;

	public function __construct($entry, $stop, $graph) {
		$this->entry = $entry;
		$this->stop = $stop;
		$this->graph = $graph;
	}
}