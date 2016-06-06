<?php

namespace PhpPdg\Graph\Printer;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Printer\AbstractTextPrinter;
use PhpPdg\Graph\Node\Printer\PrinterInterface as NodePrinterInterface;

class TextPrinter extends AbstractTextPrinter implements IndentedPrinterInterface {
	/** @var NodePrinterInterface  */
	private $node_printer;

	public function __construct(NodePrinterInterface $node_printer) {
		$this->node_printer = $node_printer;
	}

	public function printGraph(GraphInterface $graph, $indent = 0) {
		$out = '';
		$next_indent = $indent + 4;
		$out .= $this->printWithIndent('Nodes:', $indent);
		foreach ($graph->getNodes() as $i => $node) {
			$out .= $this->printWithIndent($this->node_printer->printNode($node), $next_indent);
		}
		$edges = $graph->getEdges();
		if (count($edges) > 0) {
			$out .= $this->printWithIndent('Edges:', $indent);
			foreach ($graph->getEdges() as $i => $edge) {
				$from_node = $edge->getFromNode();
				$to_node = $edge->getToNode();
				$attribute_strings = [];
				foreach ($edge->getAttributes() as $key => $value) {
					$attribute_strings[] = sprintf('%s: %s', $key, $value);
				}
				$out .= $this->printWithIndent(sprintf('%s ==%s=> %s', $this->node_printer->printNode($from_node), json_encode($edge->getAttributes()), $this->node_printer->printNode($to_node)), $next_indent);
			}
		}
		return $out;
	}
}