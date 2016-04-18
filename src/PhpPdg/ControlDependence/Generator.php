<?php

namespace PhpPdg\ControlDependence;

use PhpPdg\ControlDependence\Block\Cfg\GeneratorInterface as BlockCfgGeneratorInterface;
use PhpPdg\PostDominatorTree\GeneratorInterface as PdtGeneratorInterface;
use PhpPdg\ControlDependence\Block\Cdg\GeneratorInterface as BlockCdgGeneratorInterface;
use PHPCfg\Func;
use PhpPdg\Graph\GraphInterface;
use PhpPdg\Nodes\EntryNode;
use PhpPdg\Nodes\StopNode;
use PhpPdg\CfgAdapter\Traverser;

class Generator implements GeneratorInterface {
	/** @var BlockCfgGeneratorInterface  */
	private $block_cfg_generator;
	/** @var PdtGeneratorInterface  */
	private $pdt_generator;
	/** @var BlockCdgGeneratorInterface  */
	private $cdg_generator;
	/** @var string  */
	private $edge_type;

	/**
	 * Generator constructor.
	 * @param BlockCfgGeneratorInterface $block_cfg_generator
	 * @param PdtGeneratorInterface $pdt_generator
	 * @param BlockCdgGeneratorInterface $cdg_generator
	 * @param string $edge_type
	 */
	public function __construct(BlockCfgGeneratorInterface $block_cfg_generator, PdtGeneratorInterface $pdt_generator, BlockCdgGeneratorInterface $cdg_generator, $edge_type = 'control') {
		$this->block_cfg_generator = $block_cfg_generator;
		$this->pdt_generator = $pdt_generator;
		$this->cdg_generator = $cdg_generator;
		$this->edge_type = $edge_type;
	}

	public function addControlDependencesToGraph(Func $func, GraphInterface $target_graph) {
		$entry_node = new EntryNode();
		$stop_node = new StopNode();
		$block_cfg = $this->block_cfg_generator->generate($func, $entry_node, $stop_node);
		$block_cfg->addEdge($entry_node, $stop_node);
		$block_pdt = $this->pdt_generator->generate($block_cfg, $stop_node);
		$block_cdg = $this->cdg_generator->generate($block_cfg, $block_pdt);

		$traverser = new Traverser();
		$traverser->addVisitor(new GeneratingVisitor($target_graph, $block_cdg, $this->edge_type));
		$traverser->traverseFunc($func);
	}
}