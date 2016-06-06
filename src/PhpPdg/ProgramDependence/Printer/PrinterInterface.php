<?php

namespace PhpPdg\ProgramDependence\Printer;

use PhpPdg\ProgramDependence\Func;

interface PrinterInterface {
	/**
	 * @param Func $func
	 * @return string
	 */
	public function printFunc(Func $func);
}