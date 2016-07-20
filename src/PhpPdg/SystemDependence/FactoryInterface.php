<?php

namespace PhpPdg\SystemDependence;

use PhpPdg\CfgBridge\System as CfgSystem;

interface FactoryInterface {
	/**
	 * Creates an SDG from a collection of CFG Scripts
	 *
	 * @param CfgSystem $cfg_system
	 * @return System
	 */
	public function create(CfgSystem $cfg_system);
}