<?php

namespace PhpPdg\Graph\Normalization;

use PhpPdg\Graph\GraphInterface;

interface NormalizerInterface {
	/**
	 * Normalizes a graph into arrays, which can be used in serialization.
	 *
	 * @param GraphInterface $graph
	 * @return array
	 */
	public function normalize(GraphInterface $graph);
}