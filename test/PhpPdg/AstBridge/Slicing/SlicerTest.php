<?php

namespace PhpPdg\AstBridge\Slicing;

use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpPdg\AstBridge\System;
use PhpPdg\CfgBridge\SystemFactory as CfgSystemFactory;
use PhpPdg\SystemDependence\Factory as PdgSystemFactory;
use PhpPdg\SystemDependence\Slicing\BackwardSlicer as PdgSystemBackwardSlicer;

class SlicerTest extends \PHPUnit_Framework_TestCase {
	/** @var  Parser */
	private $ast_parser;
	/** @var  Slicer */
	private $ast_slicer;
	/** @var  Standard */
	private $ast_pretty_printer;

	protected function setUp() {
		$this->ast_parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
		$this->ast_slicer = new Slicer(new CfgSystemFactory(), PdgSystemFactory::createDefault(), new PdgSystemBackwardSlicer());
		$this->ast_pretty_printer = new Standard();
	}

	/** @dataProvider getSliceAndDumpCases */
	public function testSliceAndDump($contents, $slice_line_nr, $expected) {
		$file_path = '/foo/bar/baz.php';
		$original_ast = $this->ast_parser->parse(trim($contents));
		$expected_ast = $this->ast_parser->parse(trim($expected));
		$original_ast_system = new System();
		$original_ast_system->addAst($file_path, $original_ast);
		$sliced_ast_system = $this->ast_slicer->slice($original_ast_system, $file_path, (int) $slice_line_nr);
		$sliced_ast = $sliced_ast_system->getAst($file_path);
		$this->assertEquals($this->ast_pretty_printer->prettyPrintFile($expected_ast), $this->ast_pretty_printer->prettyPrintFile($sliced_ast));
	}

	public function getSliceAndDumpCases() {
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