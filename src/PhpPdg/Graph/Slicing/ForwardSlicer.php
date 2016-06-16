<?php

namespace PhpPdg\Graph\Slicing;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\Graph;

class ForwardSlicer implements SlicerInterface {
	public function slice(GraphInterface $source, array $slicing_criterion) {
		return Graph::reachable($source, $slicing_criterion);
	}
}