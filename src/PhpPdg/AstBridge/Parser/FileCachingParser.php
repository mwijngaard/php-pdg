<?php

namespace PhpPdg\AstBridge\Parser;

use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Parser;

class FileCachingParser implements FileParserInterface {
	private $cache_dir;
	/** @var FileParserInterface */
	private $wrapped_parser;
	/** @var  Error[] */
	private $errors = [];

	/**
	 * FileCachingParser constructor.
	 * @param string $cache_dir
	 * @param FileParserInterface $wrapped_parser
	 * @param boolean $auto_create
	 */
	public function __construct($cache_dir, FileParserInterface $wrapped_parser, $auto_create = false) {
		$this->cache_dir = $cache_dir;
		$this->wrapped_parser = $wrapped_parser;
		if ($auto_create === true && is_dir($this->cache_dir) === false) {
			mkdir($this->cache_dir, 0777, true);
		}
	}

	public function parse($filename) {
		if (file_exists($filename) === false) {
			throw new \InvalidArgumentException("No such file: `$filename`");
		}
		$mtime = filemtime($filename);
		$cache_file = $this->cache_dir . '/' . strtr($filename, [
			'/' => '_',
			'\\' => '_',
			':' => '_',
		]) . '.cache';
		if (file_exists($cache_file) === true) {
			list($ast, $errors, $cached_mtime) = unserialize(file_get_contents($cache_file));
			if ($mtime === $cached_mtime) {
				$this->errors = $errors;
				return $ast;
			}
		}
		$ast = $this->wrapped_parser->parse($filename);
		$this->errors = $this->wrapped_parser->getErrors();
		file_put_contents($cache_file, serialize([$ast, $this->errors, $mtime]));
		return $ast;
	}

	public function getErrors() {
		return $this->errors;
	}
}