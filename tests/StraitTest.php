<?php

require_once(dirname(__FILE__) . '/bootstrap.php');

class StraitTest extends PHPUnit_Framework_TestCase {

	public function test_strait() {
		$c = new C();
		$this->assertEquals(
			$c->talk_a(),  'A is talking'
		);
		$this->assertEquals(
			$c->talk_b(),  'B is talking'
		);
		$this->assertEquals(
			$c->hello(),   'Hello C'
		);
		$this->assertEquals(
			C::walk_a(),   'A is walking'
		);

	}

}

class TraitA extends Strait\Strait {

	private $a_is_talking = 'A is talking';

	private static $a_is_walking = 'A is walking';

	public function talk_a() {
		return $this->a_is_talking;
	}

	public static function walk_a() {
		return self::$a_is_walking;
	}

	public function hello() {
		return 'Hello ' . $this->name;
	}

}

class TraitB extends Strait\Strait {

	private $b_is_talking = 'B is talking';

	public static $b_is_jumping = 'A is jumping';

	public function talk_b() {
		return $this->b_is_talking;
	}

	public function hello() {
		return "B's hello";
	}
}

class C extends Strait\Straits {

	protected static function traits($use) {
		$use('TraitA');
		$use('TraitB');
	}

	protected $name = 'C';

	public function go() {
		return 'go ' . $this->private_a;
	}

}

