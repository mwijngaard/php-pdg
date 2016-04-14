<?php

namespace PhpPdg\BaseGraph;

use PHPCfg\Func;
use PhpPdg\Graph\GraphInterface;

interface GeneratorInterface {
	public function addOpNodesToGraph(Func $func, GraphInterface $graph);
}