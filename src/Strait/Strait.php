<?php 

namespace Strait;

class Strait {

	private $strait;

	public function __construct($strait) {
		$this->strait = $strait;
	}

	function __get($property) {
		return pluck($this->strait, $property);
	}
}