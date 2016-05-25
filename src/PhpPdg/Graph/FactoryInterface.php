<?php

namespace PhpPdg\Graph;

use PHPCfg\Block;

interface FactoryInterface {
	/**
	 * @return GraphInterface
	 */
	public function create();
}