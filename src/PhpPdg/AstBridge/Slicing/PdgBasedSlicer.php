<?php

namespace PhpPdg\AstBridge\Slicing;

use PhpParser\NodeTraverser;
use PhpPdg\AstBridge\System as AstSystem;
use PhpPdg\CfgBridge\SystemFactoryInterface as CfgSystemFactoryInterface;
use PhpPdg\ProgramDependence\Node\OpNode;
use PhpPdg\SystemDependence\FactoryInterface as PdgSystemFactoryInterface;
use PhpPdg\SystemDependence\Slicing\SlicerInterface as PdgSystemSlicerInterface;

class PdgBasedSlicer {
	private $cfg_system_factory;
	private $pdg_system_factory;
	private $pdg_system_slicer;

	/**
	 * PdgBasedSlicer constructor.
	 * @param CfgSystemFactoryInterface $cfg_system_factory
	 * @param PdgSystemFactoryInterface $pdg_system_factory
	 * @param PdgSystemSlicerInterface $pdg_system_slicer
	 */
	public function __construct(CfgSystemFactoryInterface $cfg_system_factory, PdgSystemFactoryInterface $pdg_system_factory, PdgSystemSlicerInterface $pdg_system_slicer) {
		$this->cfg_system_factory = $cfg_system_factory;
		$this->pdg_system_factory = $pdg_system_factory;
		$this->pdg_system_slicer = $pdg_system_slicer;
	}

	/**
	 * @param AstSystem $ast_system
	 * @param string $slice_file_path
	 * @param int $slice_line_nr
	 * @return AstSystem
	 */
	public function slice(AstSystem $ast_system, $slice_file_path, $slice_line_nr) {
		$cfg_system = $this->cfg_system_factory->create($ast_system);
		$pdg_system = $this->pdg_system_factory->create($cfg_system);
		$sliced_pdg_system = $this->pdg_system_slicer->slice($pdg_system, $slice_file_path, $slice_line_nr);
		$file_line_nrs = [];
		foreach ($sliced_pdg_system->getFuncs() as $func) {
			foreach ($func->pdg->getNodes() as $node) {
				if ($node instanceof OpNode) {
					$file_line_nrs[$node->op->getFile()][$node->op->getLine()] = 1;
				}
			}
		}
		$sliced_ast_system = new AstSystem();
		foreach ($ast_system->getFilenames() as $file_path) {
			if (isset($file_line_nrs[$file_path]) === true) {
				$traverser = new NodeTraverser();
				$traverser->addVisitor(new SlicingVisitor($file_line_nrs[$file_path]));
				$sliced_ast = $traverser->traverse($ast_system->getAst($file_path));
				$sliced_ast_system->addAst($file_path, $sliced_ast);
			}
		}
		return $sliced_ast_system;
	}
}