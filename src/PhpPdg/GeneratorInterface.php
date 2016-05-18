<?php

namespace PhpPdg;

use PHPCfg\Func;
use PhpPdg\Program\Program;

interface GeneratorInterface {
	/**
	 * @param Func $func
	 * @return Program
	 */
	public function generate(Func $func);
}