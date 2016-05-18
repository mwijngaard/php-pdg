<?php

namespace Foo;

class Baz {
	private $b = 1;
	public function quux() {
		echo $this->b;
	}
}

function bar() {
	$a = 1;
	$foo = function () use ($a) {
		echo $a;
	};
	$bar = function () use ($a) {
		echo $a;
	};
	$foo();
	$bar();
}

bar();
