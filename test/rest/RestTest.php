<?php

namespace sbronsted;

use PHPUnit\Framework\TestCase;
use RuntimeException;

require_once 'test/settings.php';

class RestTest extends TestCase {

	public static function setUpBeforeClass(): void {
		Sample::createSchema();
	}

	protected function setUp(): void {
		Db::exec(Sample::$db, 'delete from sample');
	}

	public function testRun() {
		$server = array(
			'SERVER_PROTOCOL' => 'HTTP 1.1',
			'REQUEST_METHOD' => 'POST',
			'REQUEST_URI' => '/rest/Sample?name=kurt',
		);
		$request = array('name' => 'kurt');
		$json = Rest::run($server, $request);
		$this->assertStringStartsWith('{"uid":', $json);
		$dic = DiContainer::instance();
		$this->assertStringEndsWith('application/json', $dic->header->sent);
		$result = json_decode($json);

		$server['REQUEST_METHOD'] = 'GET';
		$uris = ['/rest/Sample/' . $result->uid, '/a/b/c/rest/Sample/' . $result->uid];
		foreach ($uris as $uri) {
			$server['REQUEST_URI'] = $uri;
			$json = Rest::run($server, array());
			$this->assertStringStartsWith('{"class":"Sample",', $json);
			$object = json_decode($json);
			$this->assertEquals('kurt', $object->name);
			$this->assertEquals('Sample', $object->class);
		}

		try {
			$server['REQUEST_URI'] = '/not/a/valid/url';
			Rest::run($server, array());
			$this->fail('Exception exptected');
		}
		catch (RuntimeException $e) {
			$this->assertStringContainsString('rest', $e->getMessage());
		}
	}

	public function testObjectMethod() {
		$rest = new Rest('/rest/Sample', array());
		$result = $rest->post();

		$rest = new Rest('/rest/Sample/' . $result->uid . '/objectEcho', array('mesg' => 'Goodbye'));
		$result1 = $rest->get();
		$this->assertEquals('Goodbye', $result1->mesg);

		$rest = new Rest('/rest/Sample/' . $result->uid . '/objectEcho', array('mesg' => 'Goodbye'));
		$result2 = $rest->post();
		$this->assertEquals('Goodbye', $result2->mesg);
	}

	public function testStaticMethod() {
		$rest = new Rest('/rest/Sample/staticEcho', array('mesg' => 'Hello'));
		$result = $rest->get();
		$this->assertEquals('Hello', $result->mesg);

		$rest = new Rest('/rest/Sample/staticEcho', array('mesg' => 'Hello'));
		$result = $rest->post();
		$this->assertEquals('Hello', $result->mesg);
	}

	public function testGetByUid() {
		$rest = new Rest('/rest/Sample', array('name' => 'kurt'));
		$result = $rest->post();

		$rest = new Rest('/rest/Sample/' . $result->uid);
		$object = $rest->get();
		$this->assertTrue(is_object($object));
		$this->assertEquals('kurt', $object->name);
	}

	public function testGetBy() {
		$rest = new Rest('/rest/Sample', array('name' => 'kurt'));
		$result = $rest->post();

		$objects = $rest->get();
		$this->assertEquals(1, count($objects));
		$this->assertEquals('kurt', $objects[0]->name);
	}

	public function testGetAll() {
		$rest = new Rest('/rest/Sample', array('name' => 'kurt'));
		for ($i = 1; $i <= 10; $i++) {
			$rest->post();
		}

		$rest = new Rest('/rest/Sample');
		$objects = $rest->get();
		$this->assertEquals(10, count($objects));
	}

	public function testCrud() {
		// Create
		$rest = new Rest('/rest/Sample', array('name' => 'kurt'));
		$result = $rest->post();
		$this->assertGreaterThan(0, $result->uid);

		// Update
		$rest = new Rest('/rest/Sample/' . $result->uid, array('name' => 'Yrsa'));
		$result = $rest->post();

		$rest = new Rest('/rest/Sample/' . $result->uid);
		$object = $rest->get();
		$this->assertEquals('Yrsa', $object->name);

		$rest->delete();
	}

	public function testDelete() {
		$rest = new Rest('/rest/Sample', array('name' => 'kurt'));
		$result = $rest->post();

		$rest = new Rest('/rest/Sample/' . $result->uid);
		$rest->delete();

		try {
			$rest->get();
			$this->fail('Exptected an exception');
		}
		catch (NotFoundException $e) {
			$this->assertEquals('Sample not found', $e->getMessage());
		}
	}

	public function testAuthentification() {
		$dic = DiContainer::instance();
		$this->assertTrue($dic->restAuthenticator->hasAccess());

		$server = array(
			'SERVER_PROTOCOL' => 'HTTP 1.1',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_URI' => '/rest/Sample',
		);
		Rest::run($server, array());

		$dic->restAuthenticator->retval = false;
		try {
			Rest::run($server, array());
		}
		catch (RuntimeException $e) {
			$this->assertEquals('Access denied', $e->getMessage());
		}
	}
}
