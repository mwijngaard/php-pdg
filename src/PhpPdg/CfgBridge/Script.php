<?php

namespace PhpPdg\CfgBridge;

use PHPCfg\Script as CfgScript;

class Script {
	/** @var string */
	private $filepath;
	/** @var Script  */
	private $script;

	/**
	 * CfgScript constructor.
	 * @param string $filepath
	 * @param CfgScript $script
	 */
	public function __construct($filepath, CfgScript $script) {
		$this->filepath = $filepath;
		$this->script = $script;
	}

	/**
	 * @return string
	 */
	public function getFilePath() {
		return $this->filepath;
	}

	/**
	 * @return CfgScript
	 */
	public function getScript() {
		return $this->script;
	}
}