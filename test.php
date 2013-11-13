<?php

interface X {
	public function go();
}

interface Y {
	public function go();
}

class Z implements X, Y {

	public function go() {

	}

}

new Z();