<?php

namespace PhpPdg\ProgramDependence;

use PHPCfg\Func as CfgFunc;
use PHPCfg\Traverser;
use PhpPdg\Graph\FactoryInterface as GraphFactoryInterface;
use PhpPdg\ProgramDependence\ControlDependence\GeneratorInterface as ControlDependenceGeneratorInterface;
use PhpPdg\ProgramDependence\DataDependence\GeneratorInterface as DataDependenceGeneratorInterface;
use PhpPdg\ProgramDependence\Node\EntryNode;
use PhpPdg\ProgramDependence\Node\OpNode;

class Factory implements FactoryInterface {
	private $graph_factory;
	private $control_dependence_generator;
	private $data_dependence_generator;

	public function __construct(GraphFactoryInterface $graph_factory, ControlDependenceGeneratorInterface $control_dependence_generator, DataDependenceGeneratorInterface $data_dependence_generator) {
		$this->graph_factory = $graph_factory;
		$this->control_dependence_generator = $control_dependence_generator;
		$this->data_dependence_generator = $data_dependence_generator;
	}

	public function create(CfgFunc $cfg_func) {
		$pdg = $this->graph_factory->create();
		$entry_node = new EntryNode();
		$pdg->addNode($entry_node);
		$func = new Func($cfg_func->name, $cfg_func->class, $entry_node, $pdg);

		foreach ($cfg_func->params as $param) {
			$param_node = new OpNode($param);
			$func->pdg->addNode($param_node);
			$func->param_nodes[] = $param_node;
		}
		$traverser = new Traverser();
		$traverser->addVisitor(new InitializingVisitor($func));
		$traverser->traverseFunc($cfg_func);

		$this->control_dependence_generator->addFuncControlDependenceEdgesToGraph($cfg_func, $pdg, $entry_node);
		$this->data_dependence_generator->addFuncDataDependenceEdgesToGraph($cfg_func, $pdg);

		return $func;
	}
}