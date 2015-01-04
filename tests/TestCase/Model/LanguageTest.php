<?php

namespace Data\Test\TestCase\Model;

use Data\Model\Language;
use Tools\TestSuite\MyCakeTestCase;
class LanguageTest extends MyCakeTestCase {

	public $fixtures = array('plugin.data.language');

	public $Language;

	public function setUp() {
		parent::setUp();

		$this->Language = ClassRegistry::init('Data.Language');
	}

	public function testObject() {
		$this->assertTrue(is_object($this->Language));
		$this->assertInstanceOf('Language', $this->Language);
	}

	//TODO
}
