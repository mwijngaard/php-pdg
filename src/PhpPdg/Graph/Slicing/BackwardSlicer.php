<?php

namespace PhpPdg\Graph\Slicing;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\Graph;

class BackwardSlicer implements SlicerInterface {
	public function slice(GraphInterface $source, array $slicing_criterion) {
		return Graph::reachableInv($source, $slicing_criterion);
	}
}