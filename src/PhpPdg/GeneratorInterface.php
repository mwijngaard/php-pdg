<?php

namespace PhpPdg;

use PHPCfg\Func as CfgFunc;
use PhpPdg\Func as PdgFunc;

interface GeneratorInterface {
	/**
	 * @param CfgFunc $cfg_func
	 * @return PdgFunc
	 */
	public function generate(CfgFunc $cfg_func);
}