<?php

namespace PhpPdg\SystemDependence;

use PHPCfg\Parser;
use PhpParser\ParserFactory;
use PhpPdg\CfgBridge\System;
use PhpPdg\SystemDependence\Factory as SdgFactory;
use PhpPdg\ProgramDependence\Printer\TextPrinter as PdgPrinter;
use PhpPdg\SystemDependence\Printer\TextPrinter as SdgPrinter;
use PhpPdg\Graph\Printer\TextPrinter as GraphPrinter;
use PhpPdg\Graph\Node\Printer\TextPrinter as NodePrinter;

class FactoryTest extends \PHPUnit_Framework_TestCase {
	/** @var  Parser */
	private $cfg_parser;
	/** @var  SdgFactory */
	private $factory;
	/** @var  SdgPrinter */
	private $printer;

	protected function setUp() {
		$this->cfg_parser = new Parser((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
		$this->factory = SdgFactory::createDefault();
		$node_printer = new NodePrinter();
		$graph_printer = new GraphPrinter($node_printer);
		$this->printer = new SdgPrinter(new PdgPrinter($graph_printer, $node_printer), $graph_printer);
	}

	/** @dataProvider getCreateAndDumpCases */
	public function testCreateAndDump($contents, $expected) {
		$file_path = '/foo/bar/baz.php';
		$script = $this->cfg_parser->parse($contents, $file_path);
		$cfg_system = new System();
		$cfg_system->addScript($file_path, $script);
		$pdg_system = $this->factory->create($cfg_system);
		$actual = $this->printer->printSystem($pdg_system);
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