<?php

namespace PhpPdg\CfgBridge;

use PhpPdg\AstBridge\System as AstSystem;

interface SystemFactoryInterface {
	/**
	 * @param AstSystem $ast_system
	 * @return System
	 */
	public function create(AstSystem $ast_system);
}