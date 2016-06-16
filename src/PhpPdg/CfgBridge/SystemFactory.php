<?php

namespace PhpPdg\CfgBridge;

use PHPCfg\Parser;
use PhpParser\ParserFactory;
use PhpPdg\AstBridge\System as AstSystem;

class SystemFactory implements SystemFactoryInterface {
	/** @var Parser */
	private $parser;

	public function __construct() {
		$this->parser = new Parser((new ParserFactory())->create(ParserFactory::PREFER_PHP7));
	}

	public function create(AstSystem $ast_system) {
		$cfg_system = new System();
		foreach ($ast_system->getFilePaths() as $file_path) {
			$script = $this->parser->parseAst($ast_system->getAst($file_path), $file_path);
			$cfg_system->addScript($file_path, $script);
		}
		return $cfg_system;
	}
}