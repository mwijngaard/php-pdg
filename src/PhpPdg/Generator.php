<?php

namespace PhpPdg;

use PHPCfg\Traverser;
use PhpPdg\Graph\FactoryInterface;
use PhpPdg\ControlDependence\GeneratorInterface as ControlDependenceGeneratorInterface;
use PhpPdg\DataDependence\GeneratorInterface as DataDependenceGeneratorInterface;
use PhpPdg\Nodes\EntryNode;
use PhpPdg\Nodes\OpNode;
use PhpPdg\Func as PdgFunc;
use PHPCfg\Func as CfgFunc;

class Generator implements GeneratorInterface {
	/** @var FactoryInterface  */
	private $graph_factory;
	/** @var ControlDependenceGeneratorInterface  */
	private $control_dependence_generator;
	/** @var DataDependenceGeneratorInterface  */
	private $data_dependence_generator;

	public function __construct(FactoryInterface $graph_factory, ControlDependenceGeneratorInterface $control_dependence_generator, DataDependenceGeneratorInterface $data_dependence_generator) {
		$this->graph_factory = $graph_factory;
		$this->control_dependence_generator = $control_dependence_generator;
		$this->data_dependence_generator = $data_dependence_generator;
	}

	public function generate(CfgFunc $cfg_func) {
		$graph = $this->graph_factory->create();
		$entry_node = new EntryNode();
		$pdg_func = new PdgFunc($cfg_func->name, $cfg_func->class, $entry_node, $graph);
		$graph->addNode($entry_node);
		foreach ($cfg_func->params as $param) {
			$param_node = new OpNode($param);
			$graph->addNode($param_node);
			$pdg_func->param_nodes[] = $param_node;
		}
		$traverser = new Traverser();
		$traverser->addVisitor(new FuncInitializationVisitor($pdg_func, $graph));
		$traverser->traverseFunc($cfg_func);
		$this->control_dependence_generator->addControlDependencesToGraph($cfg_func, $graph);
		$this->data_dependence_generator->addDataDependencesToGraph($cfg_func, $graph);
		return $pdg_func;
	}
}