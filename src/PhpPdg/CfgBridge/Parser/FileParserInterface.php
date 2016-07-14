<?php

namespace PhpPdg\CfgBridge\Parser;

use PHPCfg\Script;

interface FileParserInterface {
	/**
	 * @param string $filename
	 * @return Script
	 */
	public function parse($filename);
}