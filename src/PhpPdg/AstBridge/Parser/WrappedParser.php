<?php

namespace PhpPdg\AstBridge\Parser;

use PhpParser\Node;
use PhpParser\Parser;

class WrappedParser implements FileParserInterface {
	private $string_parser;

	public function __construct(Parser $string_parser) {
		$this->string_parser = $string_parser;
	}

	public function parse($filename) {
		if (file_exists($filename) === false) {
			throw new \InvalidArgumentException("No such file: `$filename`");
		}
		return $this->string_parser->parse(file_get_contents($filename));
	}

	public function getErrors() {
		return $this->string_parser->getErrors();
	}
}