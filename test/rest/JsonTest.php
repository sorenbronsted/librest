<?php
require 'test/settings.php';

class JsonTest extends PHPUnit_Framework_TestCase {

	public function testArray() {
		$a = array('t1', 't2', null);
		$v = Json::encode($a);
		$this->assertEquals('["t1","t2",null]', $v);
	}

	public function testDefault() {
		$this->assertEquals('10', Json::encode(10));
	}

	public function testStdclass() {
		$o = new stdClass();
		$o->p1 = 'test';
		$o->p2 = 10;
		$o->p3 = null;
		$this->assertEquals('{"p1":"test","p2":10,"p3":null}', Json::encode($o));
	}

	public function testJsonEnable() {
		$o = new Sample();
		$this->assertEquals('{"Sample":{"uid":0,"name":null,"date":"2011-11-01"}}', Json::encode($o));
	}
}