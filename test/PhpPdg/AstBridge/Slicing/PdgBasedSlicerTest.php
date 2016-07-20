<?php

namespace PhpPdg\AstBridge\Slicing;

use PhpParser\Parser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard as AstPrettyPrinter;
use PhpPdg\AstBridge\System;
use PhpPdg\CfgBridge\SystemFactory as CfgSystemFactory;
use PhpPdg\SystemDependence\Factory as PdgSystemFactory;
use PhpPdg\SystemDependence\Slicing\BackwardSlicer as PdgSystemBackwardSlicer;
use PhpPdg\SystemDependence\Slicing\ForwardSlicer as PdgSystemForwardSlicer;

class PdgBasedSlicerTest extends \PHPUnit_Framework_TestCase {
	/** @var Parser */
	private $ast_parser;
	/** @var PdgBasedSlicer */
	private $backward_slicer;
	/** @var PdgBasedSlicer */
	private $forward_slicer;
	/** @var AstPrettyPrinter */
	private $ast_pretty_printer;

	protected function setUp() {
		$this->ast_parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
		$cfg_system_factory = CfgSystemFactory::createDefault();
		$sdg_system_factory = PdgSystemFactory::createDefault();
		$this->backward_slicer = new PdgBasedSlicer($cfg_system_factory, $sdg_system_factory, new PdgSystemBackwardSlicer());
		$this->forward_slicer = new PdgBasedSlicer($cfg_system_factory, $sdg_system_factory, new PdgSystemForwardSlicer());
		$this->ast_pretty_printer = new AstPrettyPrinter();
	}

	/** @dataProvider getSliceAndDumpWithBackwardSlicerCases */
	public function testSliceAndDumpWithBackwardSlicer($contents, $slice_line_nr, $expected) {
		$file_path = '/foo/bar/baz.php';
		$original_ast = $this->ast_parser->parse(trim($contents));
		$expected_ast = $this->ast_parser->parse(trim($expected));
		$original_ast_system = new System();
		$original_ast_system->addAst($file_path, $original_ast);
		$sliced_ast_system = $this->backward_slicer->slice($original_ast_system, $file_path, (int) $slice_line_nr);
		$sliced_ast = $sliced_ast_system->getAst($file_path);
		$this->assertEquals($this->ast_pretty_printer->prettyPrintFile($expected_ast), $this->ast_pretty_printer->prettyPrintFile($sliced_ast));
	}

	public function getSliceAndDumpWithBackwardSlicerCases() {
		/** @var \SplFileInfo $fileInfo */
		foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/code/backward')), '/\.test$/') as $fileInfo) {
			yield $fileInfo->getBasename() => explode('-----', file_get_contents($fileInfo));
		}
	}

	/** @dataProvider getSliceAndDumpWithForwardSlicerCases */
	public function testSliceAndDumpWithForwardSlicer($contents, $slice_line_nr, $expected) {
		$this->markTestSkipped('forward slicing does not work yet');
		$file_path = '/foo/bar/baz.php';
		$original_ast = $this->ast_parser->parse(trim($contents));
		$expected_ast = $this->ast_parser->parse(trim($expected));
		$original_ast_system = new System();
		$original_ast_system->addAst($file_path, $original_ast);
		$sliced_ast_system = $this->backward_slicer->slice($original_ast_system, $file_path, (int) $slice_line_nr);
		$sliced_ast = $sliced_ast_system->getAst($file_path);
		$this->assertEquals($this->ast_pretty_printer->prettyPrintFile($expected_ast), $this->ast_pretty_printer->prettyPrintFile($sliced_ast));
	}

	public function getSliceAndDumpWithForwardSlicerCases() {
		/** @var \SplFileInfo $fileInfo */
		foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(__DIR__ . '/code/forward')), '/\.test$/') as $fileInfo) {
			yield $fileInfo->getBasename() => explode('-----', file_get_contents($fileInfo));
		}
	}
}