<?php

namespace PhpPdg\Normalization;

use PhpPdg\Program\Program;

interface NormalizerInterface {
	/**
	 * Normalizes a program into arrays, which can be used in serialization.
	 *
	 * @param \PhpPdg\Program\Program $program
	 * @return array
	 */
	public function normalizeProgram(Program $program);
}