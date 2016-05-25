<?php

namespace PhpPdg;

use PHPCfg\Parser;
use PhpParser\ParserFactory;
use PhpPdg\Printer\Text;

class FactoryTest extends \PHPUnit_Framework_TestCase {
	/** @var  Parser */
	private $cfg_parser;
	/** @var  Factory */
	private $factory;
	/** @var  Text */
	private $printer;

	protected function setUp() {
		$this->cfg_parser = new Parser((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
		$graph_factory = new Graph\Factory();
		$block_cfg_generator = new ControlDependence\Block\Cfg\Generator($graph_factory);
		$block_cdg_generator = new ControlDependence\Block\Cdg\Generator($graph_factory);
		$pdt_generator = new PostDominatorTree\Generator($graph_factory);
		$control_dependence_generator = new ControlDependence\Generator($block_cfg_generator, $pdt_generator, $block_cdg_generator);
		$data_dependence_generator = new DataDependence\Generator();
		$this->factory = new Factory($graph_factory, $control_dependence_generator, $data_dependence_generator);
		$this->printer = new Text();
	}

	/** @dataProvider getCreateAndDumpCases */
	public function testCreateAndDump($contents, $expected) {
		$filename = 'foo.php';
		$script = $this->cfg_parser->parse($contents, 'foo.php');
		$system = $this->factory->create([
			$filename => $script
		]);
		$actual = $this->printer->printSystem($system);
		$this->assertEquals($this->canonicalize($expected), $this->canonicalize($actual));
	}

	public function getCreateAndDumpCases() {
		/** @var \SplFileInfo $fileInfo */
		foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/../code')), '/\.test$/') as $fileInfo) {
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