<?php

namespace Strait;

function pluck($object, $property) {
	$reflection = XReflectionObject::i($object);

	if ($reflection->hasProperty($property)) {
		$property = $reflection->getProperty($property);
		$property->setAccessible(true);

		return $property->getValue($object);
	} else {
		trigger_error('Undefined property: ' . get_class($object) . '::$' . $property, E_USER_ERROR);
	}

}

function invoke($object, $method, $args) {
	$reflection = XReflectionObject::i($object);

	if ($reflection->hasMethod($method)) {
		$method = $reflection->getMethod($method);
		$method->setAccessible(true);

		return $method->invokeArgs($object, $args);
	} else {
		trigger_error('Call to undefined method ' . get_class($object)  . '::'. $method, E_USER_ERROR);
	}
}

class XReflectionObject extends \ReflectionObject {

	private $object;

	private static $objects = array();

	public function __construct($object) {
		parent::__construct($object);

		$this->object = $object;
	}

	public static function i($object) {
		$id = spl_object_hash($object);
		if (empty(self::$objects[$id])) {
			self::$objects[$id] = new self($object);
		}

		return self::$objects[$id];
	}

	public function object() {
		return $this->object;
	}
}


abstract class Straits {

	private $classes;

	protected function __construct(/* classes */) {
		$classes = func_get_args();
		$self = $this;
		$this->classes = array_map(function($c) use ($self) {
			return new $c($self);
		}, $classes);

	}

	public function __call($method, $args) {
		foreach ($this->classes as $c) {
			if (!method_exists($c, $method)) continue;

			return invoke($c, $method, $args);
		}

		trigger_error('Fatal error: Call to undefined method ' . get_called_class()  . '::'. $method . '()', E_USER_ERROR);
	}

	// public function __callStatic($method, $args) {
	// 	foreach ($this->classes as $c) {
	// 		if (!method_exists($c, $method)) continue;

	// 		return invoke($c, $method, $args);
	// 	}

	// 	trigger_error('Fatal error: Call to undefined method ' . get_called_class()  . '::'. $method . '()', E_USER_ERROR);
	// }

	public function __get($property) {
		foreach ($this->classes as $c) {
			if (!property_exists($c, $key)) continue;

			return pluck($c, $property);
		}

		trigger_error('Undefined property: ' . get_called_class() . '::$' . $property, E_USER_ERROR);
	}

}
