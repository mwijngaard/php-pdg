<?php

namespace PhpPdg\Graph\Printer;

use PhpPdg\Graph\GraphInterface;

interface PrinterInterface {
	/**
	 * @param GraphInterface $graph
	 * @return string
	 */
	public function printGraph(GraphInterface $graph);
}