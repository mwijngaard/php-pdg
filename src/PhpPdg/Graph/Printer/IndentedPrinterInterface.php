<?php

namespace PhpPdg\Graph\Printer;

use PhpPdg\Graph\GraphInterface;

interface IndentedPrinterInterface extends PrinterInterface {
	/**
	 * @param GraphInterface $graph
	 * @param int $indent
	 * @return string
	 */
	public function printGraph(GraphInterface $graph, $indent = 0);
}