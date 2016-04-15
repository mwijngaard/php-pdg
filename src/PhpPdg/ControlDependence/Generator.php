<?php

namespace PhpPdg\ControlDependence;

use PhpPdg\ControlDependence\Block\Cfg\GeneratorInterface as BlockCfgGeneratorInterface;
use PhpPdg\ControlDependence\Block\Cdg\GeneratorInterface as BlockCdgGeneratorInterface;
use PhpPdg\PostDominator\GeneratorInterface as PdGeneratorInterface;
use PHPCfg\Func;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\EntryNode;
use PhpPdg\Nodes\StopNode;
use PhpPdg\CfgAdapter\Traverser;

class Generator implements GeneratorInterface {
	/** @var BlockCfgGeneratorInterface  */
	private $block_cfg_generator;
	/** @var PdGeneratorInterface  */
	private $pd_generator;
	/** @var string  */
	private $edge_type;

	/**
	 * Generator constructor.
	 * @param BlockCfgGeneratorInterface $block_cfg_generator
	 * @param PdGeneratorInterface $pdt_generator
	 * @param string $edge_type
	 */
	public function __construct(BlockCfgGeneratorInterface $block_cfg_generator, PdGeneratorInterface $pdt_generator, $edge_type = 'control') {
		$this->block_cfg_generator = $block_cfg_generator;
		$this->pd_generator = $pdt_generator;
		$this->edge_type = $edge_type;
	}

	public function addControlDependencesToGraph(Func $func, GraphInterface $target_graph) {
		$entry_node = new EntryNode();
		$stop_node = new EntryNode();
		$block_cfg = $this->block_cfg_generator->generate($func, $entry_node, $stop_node);
		$block_pd = $this->pd_generator->generate($block_cfg, $stop_node);

		foreach ($block_cfg->getNodes() as $node) {
			foreach ($block_cfg->getOutgoingEdgeNodes($node) as $to_node) {
				// evaluate all CFG edges as A-B where B does not post-dominate A
				if ($block_pd->hasEdge($to_node, $node) === false) {
					
				}
			};
		}

		$block_dependence_graph = $this->block_cfg_generator->generateControlDependenceGraph($func, new EntryNode(), new StopNode());
		$traverser = new Traverser();
		$traverser->addVisitor(new GeneratingVisitor($target_graph, $block_dependence_graph, $this->edge_type));
		$traverser->traverseFunc($func);
	}
}