<?php

namespace PhpPdg\SystemDependence\CallDependence;

use PHPCfg\Func;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\SystemDependence\System;
use PHPTypes\State;

interface GeneratorInterface {
	/**
	 * @param System $system
	 * @param State $state
	 * @param \SplObjectStorage $pdg_func_lookup
	 */
	public function addCallDependencesToSystem(System $system, State $state, \SplObjectStorage $pdg_func_lookup);
}