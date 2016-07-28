<?php

class Sample extends DbObject implements RestEnable, JsonEnable {
	private static $properties = array(
		'uid' => Property::INT,
		'name' => Property::STRING,
	);

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

	public function onJsonEncode(array $data) {
		$data['xtra'] = 'added';
		return $data;
	}
}