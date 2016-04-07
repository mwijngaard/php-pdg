<?php

namespace PhpPdg;

use PhpPdg\Graph\DirectionalGraph;

class Pdg {
	/** @var DirectionalGraph */
	public $control_dependences;
	/** @var DirectionalGraph */
	public $data_dependences;

	public function __construct() {
		$this->control_dependences = new DirectionalGraph();
		$this->data_dependences = new DirectionalGraph();
	}

	public function addControlDependence($from, $to) {
		$this->control_dependences->add($from, $to);
	}

	public function addDataDependence($from, $to) {
		$this->data_dependences->add($from, $to);
	}
}