<?php

namespace PhpPdg\SystemDependence;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\ProgramDependence\Func;

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
	public $sdg;

	public function __construct(GraphInterface $sdg) {
		$this->sdg = $sdg;
	}

	/**
	 * @return Func[]
	 */
	public function getFuncs() {
		return array_merge($this->scripts, $this->functions, $this->methods, $this->closures);
	}
}