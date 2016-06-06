<?php

namespace PhpPdg\ProgramDependence\Printer;

use PhpPdg\Printer\AbstractTextPrinter;
use PhpPdg\ProgramDependence\Func;
use PhpPdg\Graph\Node\Printer\PrinterInterface as NodePrinterInterface;
use PhpPdg\Graph\Printer\IndentedPrinterInterface as GraphIndentedPrinterInterface;

class TextPrinter extends AbstractTextPrinter implements IndentedPrinterInterface {
	/** @var GraphIndentedPrinterInterface  */
	private $graph_printer;
	/** @var NodePrinterInterface  */
	private $node_printer;

	public function __construct(GraphIndentedPrinterInterface $graph_printer, NodePrinterInterface $node_printer) {
		$this->graph_printer = $graph_printer;
		$this->node_printer = $node_printer;
	}

	public function printFunc(Func $func, $indent = 0) {
		$out = '';
		$next_indent = $indent + 4;
		$out .= $this->printWithIndent(sprintf('Entry Node: %s', $this->node_printer->printNode($func->entry_node)), $indent);
		if (count($func->param_nodes) > 0) {
			$out .= $this->printWithIndent('Param Nodes:', $indent);
			foreach ($func->param_nodes as $param_node) {
				$out .= $this->printWithIndent($this->node_printer->printNode($param_node), $next_indent);
			}
		}
		if (count($func->return_nodes) > 0) {
			$out .= $this->printWithIndent('Return Nodes:', $indent);
			foreach ($func->return_nodes as $return_node) {
				$out .= $this->printWithIndent($this->node_printer->printNode($return_node), $next_indent);
			}
		}
		$out .= $this->printWithIndent('Pdg:', $indent);
		$out .= $this->graph_printer->printGraph($func->pdg, $indent + 4);
		return $out;
	}
}