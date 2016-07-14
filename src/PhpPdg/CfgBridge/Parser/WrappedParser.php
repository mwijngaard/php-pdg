<?php

namespace PhpPdg\CfgBridge\Parser;

use PHPCfg\Parser;
use PhpParser\ParserFactory;
use PhpPdg\AstBridge\Parser\FileParserInterface as AstFileParserInterface;

class WrappedParser implements FileParserInterface {
	private $ast_file_parser;
	private $cfg_parser;

	public function __construct(AstFileParserInterface $ast_file_parser) {
		$this->ast_file_parser = $ast_file_parser;
		$this->cfg_parser = new Parser((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
	}

	public function parse($filename) {
		return $this->cfg_parser->parseAst($this->ast_file_parser->parse($filename), $filename);
	}
}