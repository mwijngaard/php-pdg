<?php

namespace PhpPdg\SystemDependence;

use PHPCfg\Parser;
use PhpParser\ParserFactory;
use PhpPdg\CfgBridge\Script;
use PhpPdg\CfgBridge\System;
use PhpPdg\Graph\Factory as GraphFactory;
use PhpPdg\ProgramDependence\Factory as PdgFactory;
use PhpPdg\SystemDependence\Factory as SdgFactory;
use PhpPdg\ProgramDependence\Printer\TextPrinter as PdgPrinter;
use PhpPdg\SystemDependence\Printer\TextPrinter as SdgPrinter;
use PhpPdg\Graph\Printer\TextPrinter as GraphPrinter;
use PhpPdg\Graph\Node\Printer\TextPrinter as NodePrinter;
use PhpPdg\ProgramDependence\ControlDependence\BlockFlowGraph\Generator as BlockCfgGenerator;
use PhpPdg\ProgramDependence\ControlDependence\BlockDependenceGraph\Generator as BlockCdgGenerator;
use PhpPdg\ProgramDependence\ControlDependence\PostDominatorTree\Generator as PdgGenerator;
use PhpPdg\ProgramDependence\ControlDependence\Generator as ControlDependenceGenerator;
use PhpPdg\ProgramDependence\DataDependence\Generator as DataDependenceGenerator;

class FactoryTest extends \PHPUnit_Framework_TestCase {
	/** @var  Parser */
	private $cfg_parser;
	/** @var  Factory */
	private $factory;
	/** @var  SdgPrinter */
	private $printer;

	protected function setUp() {
		$this->cfg_parser = new Parser((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
		$graph_factory = new GraphFactory();
		$block_cfg_generator = new BlockCfgGenerator($graph_factory);
		$block_cdg_generator = new BlockCdgGenerator($graph_factory);
		$pdt_generator = new PdgGenerator($graph_factory);
		$control_dependence_generator = new ControlDependenceGenerator($block_cfg_generator, $pdt_generator, $block_cdg_generator);
		$data_dependence_generator = new DataDependenceGenerator();
		$pdg_factory = new PdgFactory($graph_factory, $control_dependence_generator, $data_dependence_generator);
		$this->factory = new SdgFactory($graph_factory, $pdg_factory);
		$node_printer = new NodePrinter();
		$graph_printer = new GraphPrinter($node_printer);
		$this->printer = new SdgPrinter(new PdgPrinter($graph_printer, $node_printer), $graph_printer);
	}

	/** @dataProvider getCreateAndDumpCases */
	public function testCreateAndDump($contents, $expected) {
		$file_path = '/foo/bar/baz.php';
		$script = $this->cfg_parser->parse($contents, pathinfo($file_path, PATHINFO_FILENAME));
		$system = $this->factory->create(new System([new Script($file_path, $script)]));
		$actual = $this->printer->printSystem($system);
		$this->assertEquals($this->canonicalize($expected), $this->canonicalize($actual));
	}

	public function getCreateAndDumpCases() {
		/** @var \SplFileInfo $fileInfo */
		foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/code')), '/\.test$/') as $fileInfo) {
			yield $fileInfo->getBasename() => explode('-----', file_get_contents($fileInfo));
		}
	}

	private function canonicalize($str) {
		// trim from both sides
		$str = trim($str);

		// normalize EOL to \n
		$str = str_replace(["\r\n", "\r"], "\n", $str);

		// trim right side of all lines
		return implode("\n", array_map('rtrim', explode("\n", $str)));
	}
}