<?php

namespace PhpPdg\ProgramDependence\Printer;

use PhpPdg\ProgramDependence\Func;

interface IndentedPrinterInterface extends PrinterInterface {
	/**
	 * @param Func $func
	 * @param int $indent
	 * @return string
	 */
	public function printFunc(Func $func, $indent = 0);
}