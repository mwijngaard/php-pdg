<?php

namespace PhpPdg\Graph;

class Factory implements FactoryInterface {
	public function create() {
		return new Graph();
	}
}