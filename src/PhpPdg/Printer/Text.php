<?php

namespace PhpPdg\Printer;

use PhpPdg\Func;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Graph\NodeInterface;
use PhpPdg\System;

class Text {
	private $node_id_map = [];

	public function printSystem(System $system, $indent = 0) {
		$out = '';
		foreach ($system->scripts as $path => $func) {
			$out .= $this->printWithIndent(sprintf('Script %s:', $path), $indent);
			$out .= $this->printFunc($func, $indent + 4);
		}
		foreach ($system->functions as $scoped_name => $func) {
			$out .= $this->printWithIndent(sprintf('Function %s:', $scoped_name), $indent);
			$out .= $this->printFunc($func, $indent + 4);
		}
		foreach ($system->methods as $scoped_name => $func) {
			$out .= $this->printWithIndent(sprintf('Method %s:', $scoped_name), $indent);
			$out .= $this->printFunc($func, $indent + 4);
		}
		foreach ($system->closures as $scoped_name => $func) {
			$out .= $this->printWithIndent(sprintf('Closure %s:', $scoped_name), $indent);
			$out .= $this->printFunc($func, $indent + 4);
		}
		$out .= $this->printWithIndent('Graph:', $indent);
		$out .= $this->printGraph($system->graph, $indent + 4);
		return $out;
	}

	public function printFunc(Func $func, $indent = 0) {
		$out = '';
		$next_indent = $indent + 4;
		$out .= $this->printWithIndent(sprintf('Entry Node: %s', $this->printNode($func->entry_node)), $indent);
		if (count($func->return_nodes) > 0) {
			$out .= $this->printWithIndent('Return Nodes:', $indent);
			foreach ($func->return_nodes as $return_node) {
				$out .= $this->printWithIndent($this->printNode($return_node), $next_indent);
			}
		}
		if (count($func->exceptional_return_nodes) > 0) {
			$out .= $this->printWithIndent('Exceptional Return Nodes:', $indent);
			foreach ($func->exceptional_return_nodes as $exceptional_return_node) {
				$out .= $this->printWithIndent($this->printNode($exceptional_return_node), $next_indent);
			}
		}
		return $out;
	}

	public function printGraph(GraphInterface $graph, $indent = 0) {
		$out = '';
		$next_indent = $indent + 4;
		$out .= $this->printWithIndent('Nodes:', $indent);
		foreach ($graph->getNodes() as $i => $node) {
			$out .= $this->printWithIndent($this->printNode($node), $next_indent);
		}
		$edges = $graph->getEdges();
		if (count($edges) > 0) {
			$out .= $this->printWithIndent('Edges:', $indent);
			foreach ($graph->getEdges() as $i => $edge) {
				$from_node = $edge->getFromNode();
				$to_node = $edge->getToNode();
				$attribute_strings = [];
				foreach ($edge->getAttributes() as $key => $value) {
					$attribute_strings[] = sprintf('%s: %s', $key, $value);
				}
				$out .= $this->printWithIndent(sprintf('%s ==%s=> %s', $this->printNode($from_node), json_encode($edge->getAttributes()), $this->printNode($to_node)), $next_indent);
			}
		}
		return $out;
	}

	private function printWithIndent($str, $indent) {
		return str_repeat(' ', $indent) . $str . "\n";
	}

	private function printNode(NodeInterface $node) {
		$hash = $node->getHash();
		if (isset($this->node_id_map[$hash]) === false) {
			$this->node_id_map[$hash] = count($this->node_id_map);
		}
		return sprintf('#%d %s', $this->node_id_map[$hash], $node->toString());
	}
}