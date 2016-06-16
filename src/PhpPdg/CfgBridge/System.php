<?php

namespace PhpPdg\CfgBridge;

use PHPCfg\Script;

class System {
	/** @var  Script[] */
	private $scripts = [];

	public function addScript($file_path, Script $script) {
		if (isset($this->scripts[$file_path]) === true) {
			throw new \InvalidArgumentException("file path `$file_path` already exists");
		}
		$this->scripts[$file_path] = $script;
	}

	/**
	 * @return string[]
	 */
	public function getFilePaths() {
		return array_keys($this->scripts);
	}

	/**
	 * @param string $file_path
	 * @return Script
	 * @throws \InvalidArgumentException
	 */
	public function getScript($file_path) {
		if (isset($this->scripts[$file_path]) === false) {
			throw new \InvalidArgumentException("No such file");
		}
		return $this->scripts[$file_path];
	}
}