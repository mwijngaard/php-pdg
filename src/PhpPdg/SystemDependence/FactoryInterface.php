<?php

namespace PhpPdg\SystemDependence;

interface FactoryInterface {
	/**
	 * @param string $systemdir
	 * @return System
	 */
	public function create($systemdir);
}