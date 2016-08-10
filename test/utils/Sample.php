<?php
namespace ufds;

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

	public function jsonEncode(array $data) {
		$data['date'] = Date::parse('01-11-2011');
		return $data;
	}
}