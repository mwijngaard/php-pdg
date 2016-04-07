<?php

namespace PhpPdg;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;

class Func {
	/** @var Node  */
	public $entry;
	/** @var Node */
	public $exit;
	/** @var Pdg  */
	public $pdg;

	public function __construct() {
		$this->entry = new String_("ENTRY");
		$this->exit = new String_("EXIT");
		$this->pdg = new Pdg();
	}
}