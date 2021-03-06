<?php

App::uses('District', 'Data.Model');
App::uses('MyCakeTestCase', 'Tools.TestSuite');

class DistrictTest extends MyCakeTestCase {

	public $fixtures = ['plugin.data.district'];

	public $District;

	public function setUp() {
		parent::setUp();

		$this->District = ClassRegistry::init('Data.District');
	}

	public function testObject() {
		$this->assertTrue(is_object($this->District));
		$this->assertInstanceOf('District', $this->District);
	}

}
