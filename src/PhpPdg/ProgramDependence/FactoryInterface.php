<?php

namespace PhpPdg\ProgramDependence;

use PHPCfg\Func as CfgFunc;

interface FactoryInterface {
	/**
	 * @param CfgFunc $cfg_func
	 * @return Func
	 */
	public function create(CfgFunc $cfg_func);
}