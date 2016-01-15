<?php

class Sample extends DbObject implements JsonEnable, RestEnable {
	private static $properties = array(
		'uid' => Property::INT,
		'name' => Property::STRING,
	);

	public function jsonEncode() {
		return json_encode($this->getData());
	}

	public static function createSchema() {
		$sql = "create table sample(uid integer primary key autoincrement, name varchar(20))";
		Db::exec(static::$db, $sql);
	}

	public static function objectEcho($mesg) {
		return (object)array('mesg' => $mesg);
	}

	public static function staticEcho($mesg) {
		return (object)array('mesg' => $mesg);
	}

	protected function getProperties() {
		return self::$properties;
	}
}