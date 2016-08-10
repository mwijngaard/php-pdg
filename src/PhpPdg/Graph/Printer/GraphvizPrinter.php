<?php

namespace PhpPdg\Graph\Printer;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\GraphvizConverterInterface;

class GraphvizPrinter implements PrinterInterface {
	/** @var GraphvizConverterInterface */
	private $graphviz_converter;

	public function __construct(GraphvizConverterInterface $graphviz_converter) {
		$this->graphviz_converter = $graphviz_converter;
	}

	public function printGraph(GraphInterface $graph) {
		return (string) $this->graphviz_converter->toGraphviz($graph);
	}
}