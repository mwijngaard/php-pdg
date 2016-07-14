<?php

namespace PhpPdg\SystemDependence;

interface FactoryInterface {
	/**
	 * @param string[] $filenames
	 * @return System
	 */
	public function create(array $filenames);
}