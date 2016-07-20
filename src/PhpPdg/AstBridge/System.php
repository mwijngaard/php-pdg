<?php

namespace PhpPdg\AstBridge;

use PhpParser\Node;

class System {
	private $asts = [];

	/**
	 * @param string $filename
	 * @param Node[] $ast
	 */
	public function addAst($filename, array $ast) {
		if (isset($this->asts[$filename]) === true) {
			throw new \InvalidArgumentException("AST with filename `$filename` already exists");
		}
		foreach ($ast as $node) {
			if (is_object($node) === false || ($node instanceof Node) === false) {
				throw new \InvalidArgumentException("AST must be an array of Node objects");
			}
		}
		$this->asts[$filename] = $ast;
	}

	public function getFilenames() {
		return array_keys($this->asts);
	}

	public function getAst($filename) {
		if (isset($this->asts[$filename]) === false) {
			throw new \InvalidArgumentException("No AST with filename `$filename`");
		}
		return $this->asts[$filename];
	}
}