<?php

namespace PhpPdg\AstBridge;

use PhpParser\Node;

class System {
	private $asts = [];

	/**
	 * @param string $file_path
	 * @param Node[] $ast
	 */
	public function addAst($file_path, array $ast) {
		if (isset($this->asts[$file_path]) === true) {
			throw new \InvalidArgumentException("file path `$file_path` already exists");
		}
		foreach ($ast as $node) {
			if (is_object($node) === false || ($node instanceof Node) === false) {
				throw new \InvalidArgumentException("ast must be an array of Node objects");
			}
		}
		$this->asts[$file_path] = $ast;
	}

	public function getFilePaths() {
		return array_keys($this->asts);
	}

	public function getAst($file_path) {
		if (isset($this->asts[$file_path]) === false) {
			throw new \InvalidArgumentException("no ast with file path `$file_path`");
		}
		return $this->asts[$file_path];
	}
}