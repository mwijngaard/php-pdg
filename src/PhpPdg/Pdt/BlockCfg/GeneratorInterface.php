<?php

namespace PhpPdg\Pdt\BlockCfg;

use PHPCfg\Func;

interface GeneratorInterface {
	/**
	 * @param Func $func
	 * @return \PhpPdg\Graph\Graph
	 */
	public function generate(Func $func);
}