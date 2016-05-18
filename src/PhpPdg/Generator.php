<?php

namespace PhpPdg;

use PHPCfg\Traverser;
use PhpPdg\Program\ClassMethod;
use PhpPdg\Program\Closure;
use PhpPdg\Program\Function_;
use PhpPdg\Program\Script;
use PhpPdg\Graph\FactoryInterface;
use PhpPdg\ControlDependence\GeneratorInterface as ControlDependenceGeneratorInterface;
use PhpPdg\DataDependence\GeneratorInterface as DataDependenceGeneratorInterface;
use PhpPdg\Nodes\EntryNode;
use PhpPdg\Nodes\OpNode;
use PHPCfg\Func;

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

	public function generate(Func $func, $script_path = null) {
		$graph = $this->graph_factory->create();
		$entry_node = new EntryNode();
		$graph->addNode($entry_node);
		if ($func->name === '{main}') {
			$program = new Script($script_path, $entry_node, $graph);
		} else {
			if ($func->class !== null) {
				$program = new ClassMethod($func->name, $func->class->value, $entry_node, $graph);
			} else if (strpos($func->name, '{anonymous}#') === 0) {
				$program = new Closure($func->name, $entry_node, $graph);
			} else {
				$program = new Function_(implode("\\", $func->name->parts), $entry_node, $graph);
			}
			foreach ($func->params as $param) {
				$param_node = new OpNode($param);
				$graph->addNode($param_node);
				$program->param_nodes[] = $param_node;
			}
		}

		$traverser = new Traverser();
		$traverser->addVisitor(new FuncInitializationVisitor($program, $graph));
		$traverser->traverseFunc($func);
		$this->control_dependence_generator->addControlDependencesToGraph($func, $graph);
		$this->data_dependence_generator->addDataDependencesToGraph($func, $graph);
		return $program;
	}
}