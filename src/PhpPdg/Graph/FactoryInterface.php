<?php

namespace PhpPdg\Graph;

interface FactoryInterface {
	/**
	 * @return GraphInterface
	 */
	public function create();
}