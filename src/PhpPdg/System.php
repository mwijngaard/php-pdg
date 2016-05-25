<?php

namespace PhpPdg;

use PhpPdg\Graph\GraphInterface;

class System {
	/** @var Func[] */
	public $scripts = [];
	/** @var Func[] */
	public $functions = [];
	/** @var Func[] */
	public $methods = [];
	/** @var Func[] */
	public $closures = [];
	/** @var GraphInterface  */
	public $graph;

	public function __construct(GraphInterface $graph) {
		$this->graph = $graph;
	}
}