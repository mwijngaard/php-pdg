<?php

namespace PhpPdg\ProgramDependence\DataDependence;

use PHPCfg\Func;
use PhpPdg\Graph\GraphInterface;

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

	public function addFuncDataDependenceEdgesToGraph(Func $func, GraphInterface $target_graph) {
		foreach ($this->generators as $generator) {
			$generator->addFuncDataDependenceEdgesToGraph($func, $target_graph);
		}
	}
}