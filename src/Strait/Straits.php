<?php

namespace Strait;

function debug($var) {
    // if (!defined("DEBUG_MODE")) return;

    $is_cli = php_sapi_name() == 'cli' || empty($_SERVER['REMOTE_ADDR']);
    $prefix = $is_cli ? "" : '<pre>';
    $suffix = $is_cli ? "\n" : '</pre>';

    echo $prefix;
    if (is_string($var)) {
        echo $var;
    } else {
        print_r($var);
    }
    echo $suffix;
}

function req() {
	$classes = func_get_args();
}

function pluck($object, $property) {
	$reflection = XReflection::i($object);

	if ($reflection->hasProperty($property)) {
		$property = $reflection->getProperty($property);
		$property->setAccessible(true);

		return $property->getValue($object);
	} else {
		trigger_error('Undefined property: ' . get_class($object) . '::$' . $property, E_USER_ERROR);
	}

}

function invoke($object, $method, $args) {
	$reflection = XReflection::i($object);

	if ($reflection->hasMethod($method)) {
		$method = $reflection->getMethod($method);
		$method->setAccessible(true);

		return $method->invokeArgs(is_object($object) ? $object : null, $args);
	} else {
		trigger_error('Call to undefined method ' . get_class($object)  . '::'. $method, E_USER_ERROR);
	}
}

class XReflection {

	private $object;
	private static $objects = array();

	public static function i($object) {
		if (is_object($object)) {
			$id = spl_object_hash($object);
			if (empty(self::$objects[$id])) {
				self::$objects[$id] = new \ReflectionObject($object);
			}
		} else {
			$id = $object;
			if (empty(self::$objects[$id])) {
				self::$objects[$id] = new \ReflectionClass($object);
			}
		}

		return self::$objects[$id];
	}

	public function object() {
		return $this->object;
	}
}

abstract class Straits {

	private $_traits = null;
	private static $_trait_classes = null;

	public function __call($method, $args) {
		foreach (self::_traits() as $t) {
			if (!method_exists($t, $method)) continue;

			return invoke($t, $method, $args);
		}

		trigger_error('Fatal error: Call to undefined method ' . get_called_class()  . '::'. $method . '()', E_USER_ERROR);
	}

	public static function __callStatic($method, $args) {
		foreach (self::_trait_classes() as $c) {
			if (!method_exists($c, $method)) continue;

			return invoke($c, $method, $args);
		}

		trigger_error('Fatal error: Call to undefined method ' . get_called_class()  . '::'. $method . '()', E_USER_ERROR);
	}

	public function __get($property) {
		foreach (self::_traits() as $t) {
			if (!property_exists($t, $key)) continue;

			return pluck($t, $property);
		}

		trigger_error('Undefined property: ' . get_called_class() . '::$' . $property, E_USER_ERROR);
	}

	private function _traits() {
		if ($this->_traits === null) {
			$this->_traits = array();
			foreach (self::_trait_classes() as $t) {
				$this->_traits[] = new $t($this);
			}
		}

		return $this->_traits;
	}

	private static function _trait_classes() {
		if (self::$_trait_classes === null) {
			$traits = array();
			static::traits(function($t) use (&$traits) {
				return $traits[] = $t;
			});
			self::$_trait_classes = $traits;
		}

		return self::$_trait_classes;
	}

	// abstract static traits($use) {};

}
