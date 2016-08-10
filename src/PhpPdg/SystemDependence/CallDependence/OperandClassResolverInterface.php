<?php

namespace PhpPdg\SystemDependence\CallDependence;

use PHPCfg\Operand;

interface OperandClassResolverInterface {
	/**
	 * @param Operand $operand
	 * @return string[]
	 */
	public function resolveClassNames(Operand $operand);
}