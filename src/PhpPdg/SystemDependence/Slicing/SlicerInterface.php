<?php

namespace PhpPdg\SystemDependence\Slicing;

use PhpPdg\SystemDependence\System;

interface SlicerInterface {
	/**
	 * @param System $system
	 * @param string $slice_file_path
	 * @param int $slice_line_nr
	 * @return System
	 */
	public function slice(System $system, $slice_file_path, $slice_line_nr);
}