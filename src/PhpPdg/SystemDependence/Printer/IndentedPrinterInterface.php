<?php

namespace PhpPdg\SystemDependence\Printer;

use PhpPdg\SystemDependence\System;

interface IndentedPrinterInterface extends PrinterInterface {
	/**
	 * @param System $system
	 * @param int $indent
	 * @return string
	 */
	public function printSystem(System $system, $indent = 0);
}