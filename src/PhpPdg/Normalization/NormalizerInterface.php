<?php

namespace PhpPdg\Normalization;

use PhpPdg\Func;
use PhpPdg\System;

interface NormalizerInterface {
	/*
	 * @param System $system
	 * @return array
	 */
	public function normalizeSystem(System $system);

	/**
	 * @param Func $func
	 * @return array
	 */
	public function normalizeFunc(Func $func);
}