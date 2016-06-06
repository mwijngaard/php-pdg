<?php

namespace PhpPdg\SystemDependence;

interface FactoryInterface {
	/**
	 * @param CfgSystem $cfg_system
	 * @return System
	 */
	public function create(CfgSystem $cfg_system);
}