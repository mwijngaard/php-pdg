<?php

namespace PhpPdg\SystemDependence;

interface FactoryInterface {
	/**
	 * @param string $systempath
	 * @return System
	 */
	public function create($systempath);
}