<?php

namespace PhpPdg;

use PHPCfg\Func as CfgFunc;
use PHPCfg\Script;
use PHPCfg\Traverser;
use PhpPdg\Graph\FactoryInterface as GraphFactoryInterface;
use PhpPdg\ControlDependence\GeneratorInterface as ControlDependenceGeneratorInterface;
use PhpPdg\DataDependence\GeneratorInterface as DataDependenceGeneratorInterface;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\EntryNode;
use PHPTypes\State;
use PHPTypes\TypeReconstructor;

class Factory implements FactoryInterface {
	/** @var GraphFactoryInterface  */
	private $graph_factory;
	/** @var ControlDependenceGeneratorInterface  */
	private $control_dependence_generator;
	/** @var DataDependenceGeneratorInterface  */
	private $data_dependence_generator;
	/** @var  TypeReconstructor */
	private $type_reconstructor;

	public function __construct(GraphFactoryInterface $graph_factory, ControlDependenceGeneratorInterface $control_dependence_generator, DataDependenceGeneratorInterface $data_dependence_generator) {
		$this->graph_factory = $graph_factory;
		$this->control_dependence_generator = $control_dependence_generator;
		$this->data_dependence_generator = $data_dependence_generator;
		$this->type_reconstructor = new TypeReconstructor();
	}

	public function create(array $scripts_by_path) {
		$graph = $this->graph_factory->create();
		$system = new System($graph);
		$state = new State($scripts_by_path);
		$this->type_reconstructor->resolve($state);     // add type information to cfg's

		/** @var Script $script */
		foreach ($scripts_by_path as $path => $script) {
			$main_entry_node = new EntryNode("script[$path]");
			$system->scripts[$path] = new Func(null, null, $main_entry_node);
			$this->initFunc($script->main, $main_entry_node, $graph);

			foreach ($script->functions as $cfg_func) {
				$scoped_name = $cfg_func->getScopedName();
				$entry_node = new EntryNode("func[$scoped_name]");
				$func = new Func($cfg_func->name, $cfg_func->class, $entry_node);
				if ($cfg_func->class !== null) {
					$system->methods[$scoped_name] = $func;
				} else if (strpos($cfg_func->name, '{anonymous}#') === 0) {
					$system->closures[$scoped_name] = $func;
				} else {
					$system->functions[$scoped_name] = $func;
				}
				$this->initFunc($cfg_func, $entry_node, $graph);
			}
		}

		return $system;
	}

	private function initFunc(CfgFunc $cfg_func, $entry_node, GraphInterface $graph) {
		$pdg_func = new Func($cfg_func->name, $cfg_func->class, $entry_node);
		$graph->addNode($entry_node);
		$traverser = new Traverser();
		$traverser->addVisitor(new InitializingVisitor($graph));
		$traverser->traverseFunc($cfg_func);
		$this->control_dependence_generator->addFuncControlDependenceEdgesToGraph($cfg_func, $graph, $entry_node);
		$this->data_dependence_generator->addFuncDataDependenceEdgesToGraph($cfg_func, $graph);
		return $pdg_func;
	}
}