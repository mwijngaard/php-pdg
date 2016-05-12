<?php

namespace PhpPdg\Normalization;

use PhpPdg\Func;

interface NormalizerInterface {
	/**
	 * Normalizes a func into arrays, which can be used in serialization.
	 *
	 * @param Func $func
	 * @return array
	 */
	public function normalizeFunc(Func $func);
}