<?php

namespace PhpPdg\SystemDependence;

use PhpParser\ParserFactory;
use PhpPdg\CfgBridge\Parser\FileParserInterface;
use PhpPdg\AstBridge\Parser\WrappedParser as AstWrappedParser;
use PhpPdg\CfgBridge\Parser\WrappedParser as CfgWrappedParser;
use PhpPdg\CfgBridge\System as CfgSystem;

class FilesystemFactory implements FilesystemFactoryInterface {
	/** @var FileParserInterface  */
	private $cfg_parser;
	/** @var  FactoryInterface */
	private $factory;

	public function __construct(FileParserInterface $cfg_parser, FactoryInterface $factory) {
		$this->cfg_parser = $cfg_parser;
		$this->factory = $factory;
	}

	public static function createDefault() {
		return new self(new CfgWrappedParser((new AstWrappedParser((new ParserFactory())->create(ParserFactory::PREFER_PHP7)))), Factory::createDefault());
	}

	public function create($dirname) {
		if (is_dir($dirname) === false) {
			throw new \InvalidArgumentException("No such system: `$dirname`");
		}

		$cfg_system = new CfgSystem();
		/** @var \SplFileInfo $fileinfo */
		foreach (new \RegexIterator(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirname)), "/.*\\.php$/i") as $fileinfo) {
			$filename = $fileinfo->getRealPath();
			$cfg_system->addScript($filename, $this->cfg_parser->parse($filename));
		}

		return $this->factory->create($cfg_system);
	}
}