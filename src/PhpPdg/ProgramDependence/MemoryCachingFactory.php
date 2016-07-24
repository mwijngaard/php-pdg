<?php

namespace PhpPdg\ProgramDependence;

use PHPCfg\Func as CfgFunc;

class MemoryCachingFactory implements FactoryInterface {
	/** @var FactoryInterface */
	private $wrapped_factory;
	private $cache;

	public function __construct(FactoryInterface $wrapped_factory) {
		$this->wrapped_factory = $wrapped_factory;
		$this->cache = new \SplObjectStorage();
	}

	public function create(CfgFunc $cfg_func, $filename = null) {
		if (isset($this->cache[$cfg_func]) === true) {
			return $this->cache[$cfg_func];
		}
		$pdg = $this->wrapped_factory->create($cfg_func, $filename);
		$this->cache[$cfg_func] = $pdg;
		return $pdg;
	}

	public function clear() {
		$this->cache = new \SplObjectStorage();
	}
}