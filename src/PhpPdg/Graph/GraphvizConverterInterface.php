<?php

namespace PhpPdg\Graph;

use phpDocumentor\GraphViz\Graph as GvGraph;

interface GraphvizConverterInterface {
	/**
	 * @param GraphInterface $graph
	 * @return GvGraph
	 */
	public function toGraphviz(GraphInterface $graph);
}