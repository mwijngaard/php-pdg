<?php

namespace PhpPdg\ProgramDependence;

use PHPCfg\Parser;
use PhpParser\ParserFactory;
use PhpPdg\ProgramDependence\Factory as PdgFactory;
use PhpPdg\ProgramDependence\Printer\TextPrinter as PdgPrinter;
use PhpPdg\Graph\Printer\TextPrinter as GraphPrinter;
use PhpPdg\Graph\Node\Printer\TextPrinter as NodePrinter;

class FactoryTest extends \PHPUnit_Framework_TestCase {
	/** @var  Parser */
	private $cfg_parser;
	/** @var  Factory */
	private $factory;
	/** @var  PdgPrinter */
	private $printer;

	protected function setUp() {
		$this->cfg_parser = new Parser((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
		$this->factory = PdgFactory::createDefault();
		$node_printer = new NodePrinter();
		$this->printer = new PdgPrinter(new GraphPrinter($node_printer), $node_printer);
	}

	/** @dataProvider getCreateAndDumpCases */
	public function testCreateAndDump($contents, $expected) {
		$script = $this->cfg_parser->parse($contents, 'foo.php');
		if (count($script->functions) > 0) {
			$this->fail("PDG script should not have functions");
		}
		$func = $this->factory->create($script->main);
		$actual = $this->printer->printFunc($func);
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