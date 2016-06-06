<?php

namespace PhpPdg\CfgBridge;

class System {
	/** @var  Script[] */
	private $scripts = [];

	/**
	 * CfgSystem constructor.
	 * @param Script[] $cfg_bridge_scripts
	 */
	public function __construct($cfg_bridge_scripts) {
		foreach ($cfg_bridge_scripts as $i => $file) {
			if (($file instanceof Script) === false) {
				throw new \InvalidArgumentException("Expected cfg_bridge_scripts[$i] to be instance of Script");
			}
			if (isset($this->scripts[$file->getFilePath()]) === true) {
				throw new \InvalidArgumentException("Duplicate file path {$file->getFilePath()} at cfg_bridge_scripts[$i]");
			}
			$this->scripts[$file->getFilePath()] = $file;
		}
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