<?php
/* City Fixture generated on: 2011-11-20 21:58:46 : 1321822726 */

/**
 * CityFixture
 *
 */
class CityFixture extends CakeTestFixture {

	/**
	 * Fields
	 *
	 * @var array
	 */
	public $fields = [
		'id' => ['type' => 'integer', 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary', 'collate' => null, 'comment' => ''],
		'name' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 30, 'collate' => 'utf8_unicode_ci', 'comment' => '', 'charset' => 'utf8'],
		'slug' => ['type' => 'string', 'null' => false, 'default' => null, 'length' => 40, 'collate' => 'utf8_unicode_ci', 'comment' => '', 'charset' => 'utf8'],
		'lat' => ['type' => 'float', 'null' => false, 'default' => '0.000000', 'length' => '10,6', 'collate' => null, 'comment' => ''],
		'lng' => ['type' => 'float', 'null' => false, 'default' => '0.000000', 'length' => '10,6', 'collate' => null, 'comment' => ''],
		'active' => ['type' => 'boolean', 'null' => false, 'default' => '0', 'collate' => null, 'comment' => ''],
		'created' => ['type' => 'datetime', 'null' => false, 'default' => null, 'collate' => null, 'comment' => ''],
		'modified' => ['type' => 'datetime', 'null' => false, 'default' => null, 'collate' => null, 'comment' => ''],
		'indexes' => ['PRIMARY' => ['column' => 'id', 'unique' => 1]],
		'tableParameters' => ['charset' => 'utf8', 'collate' => 'utf8_unicode_ci', 'engine' => 'MyISAM']
	];

	/**
	 * Records
	 *
	 * @var array
	 */
	public $records = [
		[
			'id' => '1',
			'name' => 'München',
			'slug' => 'muenchen',
			'lat' => '48.139126',
			'lng' => '11.580186',
			'active' => 1,
			'created' => '0000-00-00 00:00:00',
			'modified' => '0000-00-00 00:00:00'
		],
		[
			'id' => '3',
			'name' => 'Stuttgart',
			'slug' => 'Stuttgart',
			'lat' => '48.777107',
			'lng' => '9.180769',
			'active' => 1,
			'created' => '2011-10-07 16:48:05',
			'modified' => '0000-00-00 00:00:00'
		],
	];
}
