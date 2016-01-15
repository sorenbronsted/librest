<?php
require_once 'test/settings.php';

class RestTest extends PHPUnit_Framework_TestCase {

	public static function setUpBeforeClass() {
		Sample::createSchema();
	}

	protected function setUp() {
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
		$result = json_decode($json);

		$server['REQUEST_METHOD'] = 'GET';
		$server['REQUEST_URI'] = '/rest/Sample/'.$result->uid;
		$json = Rest::run($server, array());
		$this->assertStringStartsWith('{"uid":', $json);
		$object = json_decode($json);
		$this->assertEquals('kurt', $object->name);
	}

  public function testObjectMethod() {
	  $rest = new Rest('/rest/Sample', array());
	  $result = $rest->post();

	  $rest = new Rest('/rest/Sample/'.$result->uid, array('method' => 'objectEcho', 'mesg' => 'Goodbye'));
	  $result = $rest->get();
	  $this->assertEquals('Goodbye', $result->mesg);
  }
  
  public function testStaticMethod() {
	  $rest = new Rest('/rest/Sample', array('method' => 'staticEcho', 'mesg' => 'Hello'));
	  $result = $rest->get();
	  $this->assertEquals('Hello', $result->mesg);
  }
  
  public function testGetByUid() {
	  $rest = new Rest('/rest/Sample', array('name' => 'kurt'));
	  $result = $rest->post();

	  $rest = new Rest('/rest/Sample/'.$result->uid);
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
	  for($i = 1; $i <= 10; $i++) {
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
		$rest = new Rest('/rest/Sample/'.$result->uid, array('name' => 'Yrsa'));
		$result = $rest->post();

		$rest = new Rest('/rest/Sample/'.$result->uid);
		$object = $rest->get();
		$this->assertEquals('Yrsa', $object->name);

		$rest->delete();
	}

	public function testDelete() {
		$rest = new Rest('/rest/Sample', array('name' => 'kurt'));
		$result = $rest->post();

		$rest = new Rest('/rest/Sample/'.$result->uid);
		$rest->delete();

		try {
			$rest->get();
			$this->fail('Exptected an exception');
		}
		catch(NotFoundException $e) {
			$this->assertEquals('Sample not found', $e->getMessage());
		}
	}
}
