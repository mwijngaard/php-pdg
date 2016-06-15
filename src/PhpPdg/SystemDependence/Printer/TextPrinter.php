<?php

namespace PhpPdg\SystemDependence\Printer;

use PhpPdg\Printer\AbstractTextPrinter;
use PhpPdg\ProgramDependence\Func;
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
		$out .= $this->printFuncsWithPrefix('Script', $system->scripts, $indent);
		$out .= $this->printFuncsWithPrefix('Function', $system->functions, $indent);
		$out .= $this->printFuncsWithPrefix('Method', $system->methods, $indent);
		$out .= $this->printFuncsWithPrefix('Closure', $system->closures, $indent);
		$out .= $this->printWithIndent('Graph:', $indent);
		$out .= $this->graph_printer->printGraph($system->sdg, $indent + 4);
		return $out;
	}

	/**
	 * @param string $prefix
	 * @param Func[] $funcs
	 * @param int $indent
	 * @return string
	 */
	private function printFuncsWithPrefix($prefix, $funcs, $indent) {
		$out = '';
		foreach ($funcs as $func) {
			$out .= $this->printWithIndent(sprintf('%s %s:', $prefix, $func->getId()), $indent);
			$out .= $this->pdg_printer->printFunc($func, $indent + 4);
		}
		return $out;
	}
}