<?php

namespace PhpPdg;

use PHPCfg\Script as CfgScript;
use PhpPdg\Func as PdgFunc;

interface GeneratorInterface {
	/**
	 * @param CfgScript $cfg_script
	 * @return PdgFunc
	 */
	public function generate(CfgScript $cfg_script);
}