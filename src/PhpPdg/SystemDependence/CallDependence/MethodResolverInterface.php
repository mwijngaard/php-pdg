<?php

namespace PhpPdg\SystemDependence\CallDependence;

use PHPTypes\State;

interface MethodResolverInterface {
	public function resolveMethod(State $state, $classname, $methodname, \SplObjectStorage $pdg_func_lookup);
}