<?php

namespace PhpPdg;

use PHPCfg\Script;

interface FactoryInterface {
	/**
	 * @param Script[] $scripts_by_path
	 * @return System
	 */
	public function create(array $scripts_by_path);
}