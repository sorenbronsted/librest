<?php

class Store {
	private static $cache = array();
	private static $counter = 1;

	public static function getBy(array $qbe) {
		$result = array();
		foreach(self::$cache as $object) {
			$match = 0;
			foreach($qbe as $name => $value) {
				if ($object->$name === $value) {
					$match++;
				}
			}
			if ($match == count(array_keys($qbe))) {
				$result[] = $object;
			}
		}
		return $result;
	}

	public static function getAll() {
		return array_values(self::$cache);
	}

	public static function destroy($uid) {
		unset(self::$cache[$uid]);
	}

	public static function save($uid, $object) {
		self::$cache[$uid] = $object;
	}

	public static function clear() {
		self::$cache = array();
	}

	public static function nextUid() {
		return self::$counter++;
	}
}