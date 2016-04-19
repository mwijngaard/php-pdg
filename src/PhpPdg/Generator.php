<?php

namespace PhpPdg;

use PhpPdg\Graph\FactoryInterface;
use PhpPdg\BaseGraph\GeneratorInterface as BaseGraphGeneratorInterface;
use PhpPdg\ControlDependence\GeneratorInterface as ControlDependenceGeneratorInterface;
use PhpPdg\DataDependence\GeneratorInterface as DataDependenceGeneratorInterface;
use PhpPdg\Nodes\EntryNode;
use PhpPdg\Nodes\StopNode;
use PhpPdg\Func as PdgFunc;
use PhpPdg\Script as PdgScript;
use PHPCfg\Func as CfgFunc;
use PHPCfg\Script as CfgScript;

class Generator implements GeneratorInterface {
	/** @var FactoryInterface  */
	private $graph_factory;
	/** @var BaseGraphGeneratorInterface */
	private $base_graph_generator;
	/** @var ControlDependenceGeneratorInterface  */
	private $control_dependence_generator;
	/** @var DataDependenceGeneratorInterface  */
	private $data_dependence_generator;

	public function __construct(FactoryInterface $graph_factory, BaseGraphGeneratorInterface $base_graph_generator, ControlDependenceGeneratorInterface $control_dependence_generator, DataDependenceGeneratorInterface $data_dependence_generator) {
		$this->graph_factory = $graph_factory;
		$this->base_graph_generator = $base_graph_generator;
		$this->control_dependence_generator = $control_dependence_generator;
		$this->data_dependence_generator = $data_dependence_generator;
	}

	public function generate(CfgScript $cfg_script) {
		$pdg_script = new PdgScript();
		$pdg_script->main = $this->generateFunc($cfg_script->main);
		foreach ($cfg_script->functions as $cfg_func) {
			$pdg_script->functions[] = $this->generateFunc($cfg_func);
		}
		return $pdg_script;
	}

	private function generateFunc(CfgFunc $cfg_func) {
		$graph = $this->graph_factory->create();
		$entry_node = new EntryNode();
		$stop_node = new StopNode();
		$graph->addNode($entry_node);
		$graph->addNode($stop_node);
		$this->base_graph_generator->addOpNodesToGraph($cfg_func, $graph);
		$this->control_dependence_generator->addControlDependencesToGraph($cfg_func, $graph);
		$this->data_dependence_generator->addDataDependencesToGraph($cfg_func, $graph);
		return new PdgFunc($cfg_func->name, $cfg_func->class, $entry_node, $stop_node, $graph);
	}
}