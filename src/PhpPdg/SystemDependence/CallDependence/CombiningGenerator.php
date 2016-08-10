<?php

namespace PhpPdg\SystemDependence\CallDependence;

use PhpPdg\SystemDependence\System;
use PHPTypes\State;

class CombiningGenerator implements GeneratorInterface {
	private $generators;

	/**
	 * CombiningGenerator constructor.
	 * @param GeneratorInterface[] $generators
	 */
	public function __construct(array $generators) {
		foreach ($generators as $i => $generator) {
			if (is_object($generator) === false || ($generator instanceof GeneratorInterface) === false) {
				throw new \InvalidArgumentException("Generator $i is not an instance of GeneratorInterface");
			}
			$this->generators = $generators;
		}
	}

	public function addCallDependencesToSystem(System $system, State $state, \SplObjectStorage $pdg_func_lookup) {
		foreach ($this->generators as $generator) {
			$generator->addCallDependencesToSystem($system, $state, $pdg_func_lookup);
		}
	}
}