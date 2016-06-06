<?php

namespace PhpPdg\SystemDependence\Printer;

use PhpPdg\Printer\AbstractTextPrinter;
use PhpPdg\ProgramDependence\Printer\TextPrinter as PdgIndentedPrinterInterface;
use PhpPdg\Graph\Printer\IndentedPrinterInterface as GraphIndentedPrinterInterface;

use PhpPdg\SystemDependence\System;

class TextPrinter extends AbstractTextPrinter implements IndentedPrinterInterface {
	/** @var PdgIndentedPrinterInterface  */
	private $pdg_printer;
	/** @var GraphIndentedPrinterInterface  */
	private $graph_printer;

	public function __construct(PdgIndentedPrinterInterface $pdg_printer, GraphIndentedPrinterInterface $graph_printer) {
		$this->pdg_printer = $pdg_printer;
		$this->graph_printer = $graph_printer;
	}

	public function printSystem(System $system, $indent = 0) {
		$out = '';
		foreach ($system->scripts as $path => $func) {
			$out .= $this->printWithIndent(sprintf('Script %s:', $path), $indent);
			$out .= $this->pdg_printer->printFunc($func, $indent + 4);
		}
		foreach ($system->functions as $scoped_name => $func) {
			$out .= $this->printWithIndent(sprintf('Function %s:', $scoped_name), $indent);
			$out .= $this->pdg_printer->printFunc($func, $indent + 4);
		}
		foreach ($system->methods as $scoped_name => $func) {
			$out .= $this->printWithIndent(sprintf('Method %s:', $scoped_name), $indent);
			$out .= $this->pdg_printer->printFunc($func, $indent + 4);
		}
		foreach ($system->closures as $scoped_name => $func) {
			$out .= $this->printWithIndent(sprintf('Closure %s:', $scoped_name), $indent);
			$out .= $this->pdg_printer->printFunc($func, $indent + 4);
		}
		$out .= $this->printWithIndent('Graph:', $indent);
		$out .= $this->graph_printer->printGraph($system->sdg, $indent + 4);
		return $out;
	}
}