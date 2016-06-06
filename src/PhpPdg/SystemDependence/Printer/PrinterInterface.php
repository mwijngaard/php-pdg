<?php

namespace PhpPdg\SystemDependence\Printer;

use PhpPdg\SystemDependence\System;

interface PrinterInterface {
	/**
	 * @param System $system
	 * @return string
	 */
	public function printSystem(System $system);
}