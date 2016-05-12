<?php

function foo($a, $b) {
	return $a + $b;
}

$a = 0;
if (true) {
	$a++;
} else {
	$a--;
}
echo $a;