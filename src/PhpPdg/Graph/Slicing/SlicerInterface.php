<?php

namespace PhpPdg\Graph\Slicing;

use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\Node\NodeInterface;

interface SlicerInterface {
	/**
	 * @param GraphInterface $source
	 * @param NodeInterface[] $slicing_criterion
	 * @return GraphInterface $target
	 */
	public function slice(GraphInterface $source, array $slicing_criterion);
}