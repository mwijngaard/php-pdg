<?php

namespace PhpPdg\ControlDependence\Block\Cdg;

use PhpPdg\Graph\GraphInterface;

interface GeneratorInterface {
	/**
	 * @param GraphInterface $cfg
	 * @param GraphInterface $pdt
	 * @return GraphInterface
	 */
	public function generate(GraphInterface $cfg, GraphInterface $pdt);
}