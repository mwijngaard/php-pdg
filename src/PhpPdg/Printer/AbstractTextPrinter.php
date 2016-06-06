<?php

namespace PhpPdg\Printer;

abstract class AbstractTextPrinter {
	protected function printWithIndent($str, $indent) {
		return str_repeat(' ', $indent) . $str . "\n";
	}
}