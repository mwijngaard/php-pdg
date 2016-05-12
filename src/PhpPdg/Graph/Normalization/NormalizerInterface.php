<?php

namespace PhpPdg\Graph\Normalization;

use PhpPdg\Graph\GraphInterface;

interface NormalizerInterface {
	/**
	 * @param GraphInterface $graph
	 * @return array
	 */
	public function normalizeGraph(GraphInterface $graph);
}