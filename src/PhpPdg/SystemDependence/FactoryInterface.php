<?php

namespace PhpPdg\SystemDependence;

use PhpPdg\CfgBridge\System as CfgBridgeSystem;

interface FactoryInterface {
	/**
	 * @param CfgBridgeSystem $cfg_system
	 * @return System
	 */
	public function create(CfgBridgeSystem $cfg_system);
}