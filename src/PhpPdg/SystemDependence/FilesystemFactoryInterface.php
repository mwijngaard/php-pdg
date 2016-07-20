<?php

namespace PhpPdg\SystemDependence;

interface FilesystemFactoryInterface {
	/**
	 * Creates an SDG from a filesystem directory path
	 *
	 * @param string $dirname
	 * @return System
	 */
	public function create($dirname);
}