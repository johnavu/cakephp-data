<?php
App::uses('DataAppModel', 'Data.Model');

class Address extends DataAppModel {

	public $displayField = 'formatted_address';

	public $actsAs = ['Tools.Geocoder' => ['real' => false, 'override' => true, 'allow_inconclusive' => true]]; //'before'=>'validate'

	public $order = ['Address.type_id' => 'ASC', 'Address.formatted_address' => 'ASC'];

	public $config = [];

	public $validate = [
		'country_province_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'valErrMandatoryField',
				'last' => true
			],
			'fitsToCountry' => [
				'rule' => ['fitsToCountry'],
				'message' => 'The province seems not be fit to the chosen country'
			],
		],
		'country_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'valErrMandatoryField',
				'last' => true
			],

		],
		'address_type_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				'message' => 'valErrMandatoryField',
				'last' => true
			],
			'primaryUnique' => [
				'rule' => ['primaryUnique'],
				'message' => 'Es darf nur eine Adresse "Haupt-Wohnsitz" sein, bitte einen anderen Typ wählen',
				'last' => true
			],
		],
		'foreign_id' => [
			'validateKey' => [
				'rule' => ['validateKey'],
				'message' => 'valErrInvalidKey'
			],
		],
		'postal_code' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				'message' => 'valErrMandatoryField',
				'last' => true
			],
			'correspondsWithCountry' => [
				'rule' => ['correspondsWithCountry'],
				'message' => 'The zip code seems not to have the correct length',
			],
		],
		'city' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				'message' => 'valErrMandatoryField'
			],
		],
		'street' => [
		],
		'formatted_address' => [
			'validateUnique' => [
				'rule' => ['validateUnique', ['foreign_id', 'model']],
				'allowEmpty' => true,
				'message' => 'valErrRecordNameExists'
			],
		],
		'lat' => [
			'decimal' => [
				'rule' => ['decimal', 6],
				'allowEmpty' => true,
				'message' => 'not a valid geografic number for latitude',
			],
		],
		'lng' => [
			'decimal' => [
				'rule' => ['decimal', 6],
				'allowEmpty' => true,
				'message' => 'not a valid geografic number for longitude',
			],
		],
	];

	public $belongsTo = [
		'Country' => [
			'className' => 'Data.Country',
			'foreignKey' => 'country_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		# redundant:
		'CountryProvince' => [
			'className' => 'Data.CountryProvince',
			'foreignKey' => 'country_province_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'User' => [
			'className' => CLASS_USER,
			'foreignKey' => 'foreign_id',
			'conditions' => ['model' => 'User'],
			'fields' => ['id', 'username'],
			'order' => ''
		],
	];

	public function __construct($id = false, $table = false, $ds = null) {

		if ($config = Configure::read('Address')) {
			$vars = ['displayField', 'order', 'actsAs', 'validate', 'belongsTo'];
			foreach ($vars as $var) {
				if (isset($config[$var])) {
					$this->{$var} = $config[$var];
				}
			}
			if (isset($config['CountryProvince']) && $config['CountryProvince'] === false && isset($this->belongsTo['CountryProvince'])) {
				unset($this->belongsTo['CountryProvince']);
			} else {
				$config['CountryProvince'] = true;
			}
			if (!empty($config['debug'])) {
				$this->actsAs['Tools.Jsonable'] = ['fields' => ['debug'], 'map' => ['geocoder_result']];
			}

			$this->config = $config;
		}

		parent::__construct($id, $table, $ds);
	}

	public function primaryUnique(&$data) {
		if (empty($this->data[$this->alias]['foreign_id']) || $this->data[$this->alias]['address_type_id'] != self::TYPE_MAIN) {
			return true;
		}
		$conditions = ['foreign_id' => $this->data[$this->alias]['foreign_id'], 'address_type_id' => self::TYPE_MAIN];
		if (!empty($this->data[$this->alias]['id'])) {
			$conditions['user_id !='] = $this->data[$this->alias]['id'];
		}
		if ($this->find('first', ['conditions' => $conditions])) {
			return false;
		}
		return true;
	}

	/**
	 * Zip Codes
	 */
	public function correspondsWithCountry(&$data) {
		if (!empty($this->data[$this->alias]['postal_code'])) {
			$res = $this->Country->find('first', [
				'conditions' => ['Country.id' => $this->data[$this->alias]['country_id']]
			]);
			if (empty($res)) {
				return true;
			}
			if (!empty($res['Country']['zip_length']) && $res['Country']['zip_length'] != mb_strlen($this->data[$this->alias]['postal_code'])) {
				return false;
			}

		}
		return true;
	}

	/**
	 * Validation of country_province_id
	 */
	public function fitsToCountry(&$data) {
		if (!$this->config['CountryProvince'] || !isset($this->data[$this->alias]['country_id']) || !isset($this->data[$this->alias]['country_province_id'])) {
			return true;
		}
		$res = $this->Country->CountryProvince->find('list', [
			'conditions' => ['country_id' => $this->data[$this->alias]['country_id']]
		]);
		if (empty($res) || array_shift($data) == 0) {
			$this->data[$this->alias]['country_province_id'] = 0;
		} elseif (empty($this->data[$this->alias]['country_province_id']) || !array_key_exists($this->data[$this->alias]['country_province_id'], $res)) {
			return false;
		}
		return true;
	}

	public function beforeValidate($options = []) {
		parent::beforeValidate($options);

		# add country name for geocoder
		if (!empty($this->data[$this->alias]['country_id'])) {
			$this->data[$this->alias]['country'] = $this->Country->field('name', ['id' => $this->data[$this->alias]['country_id']]);
		}
		if (!empty($this->data[$this->alias]['postal_code'])) {
			unset($this->validate['city']['notEmpty']);
		} elseif (!empty($this->data[$this->alias]['city'])) {
			unset($this->validate['postal_code']['notEmpty']);
		}

		if (isset($this->data[$this->alias]['foreign_id']) && empty($this->data[$this->alias]['foreign_id'])) {
			$this->data[$this->alias]['foreign_id'] = 0;
		}

		# prevents NULL inserts into DB
		if (isset($this->data[$this->alias]['lat'])) {
			$this->data[$this->alias]['lat'] = number_format((float)$this->data[$this->alias]['lat'], 6);
		}
		if (isset($this->data[$this->alias]['lng'])) {
			$this->data[$this->alias]['lng'] = number_format((float)$this->data[$this->alias]['lng'], 6);
		}

		return true;
	}

	public function beforeSave($options = []) {
		parent::beforeSave($options);

		if (!empty($this->data[$this->alias]['formatted_address']) && !empty($this->data[$this->alias]['geocoder_result'])) {
			# fix city/plz?
			if (isset($this->data[$this->alias]['postal_code']) && empty($this->data[$this->alias]['postal_code'])) {
				$this->data[$this->alias]['postal_code'] = $this->data[$this->alias]['geocoder_result']['postal_code'];
			}
			if (isset($this->data[$this->alias]['city']) && empty($this->data[$this->alias]['city'])) {
				# use sublocality too?
				$this->data[$this->alias]['city'] = $this->data[$this->alias]['geocoder_result']['locality'];
			}
			if (isset($this->data[$this->alias]['postal_code']) && empty($this->data[$this->alias]['postal_code']) && !empty($this->data[$this->alias]['geocoder_result']['postal_code'])) {
				# use postal_code
				$this->data[$this->alias]['postal_code'] = $this->data[$this->alias]['geocoder_result']['postal_code'];
			}

			# ensure province is correct
			if ($this->config['CountryProvince']) {
				if (isset($this->data[$this->alias]['country_province_id']) && !empty($this->data[$this->alias]['country_province_id']) && !empty($this->data[$this->alias]['geocoder_result']['country_province_code'])) {
					//$this->data[$this->alias]['country_province_id']
					$countryProvince = $this->Country->CountryProvince->find('first', ['conditions' => ['CountryProvince.id' => $this->data[$this->alias]['country_province_id']]]);
					if (!empty($countryProvince) && strlen($countryProvince['CountryProvince']['abbr']) === strlen($this->data[$this->alias]['geocoder_result']['country_province_code']) && $countryProvince['CountryProvince']['abbr'] != $this->data[$this->alias]['geocoder_result']['country_province_code']) {
						$this->invalidate('country_province_id', 'Als Bundesland wurde für diese Adresse \'' . h($this->data[$this->alias]['geocoder_result']['country_province']) . '\' erwartet - du hast aber \'' . h($countryProvince['CountryProvince']['name']) . '\' angegeben. Liegt denn deine Adresse tatsächlich in einem anderen Bundesland? Dann gebe bitte die genaue PLZ und Ort an, damit das Bundesland dazu auch korrekt identifiziert werden kann.');
						return false;
					}

				# enter new id
				} elseif (isset($this->data[$this->alias]['country_province_id']) && !empty($this->data[$this->alias]['geocoder_result']['country_province_code'])) {
					$countryProvince = $this->Country->CountryProvince->find('first', ['conditions' => ['OR' => ['CountryProvince.abbr' => $this->data[$this->alias]['geocoder_result']['country_province_code'], 'CountryProvince.name' => $this->data[$this->alias]['geocoder_result']['country_province']]]]);
					if (!empty($countryProvince)) {
						$this->data[$this->alias]['country_province_id'] = $countryProvince['CountryProvince']['id'];
					}
				}
			}

			# enter new id
			if (isset($this->data[$this->alias]['country_id']) && empty($this->data[$this->alias]['country_id']) && !empty($this->data[$this->alias]['geocoder_result']['country_code'])) {
				$country = $this->Country->find('first', ['conditions' => ['Country.iso2' => $this->data[$this->alias]['geocoder_result']['country_code']]]);
				if (!empty($country)) {
					$this->data[$this->alias]['country_id'] = $country['Country']['id'];
				}
			}

		}

		if (isset($this->data[$this->alias]['geocoder_result']) && !$this->Behaviors->loaded('Jsonable')) {
			unset($this->data[$this->alias]['geocoder_result']);
		}
		return true;
	}

	/**
	 * Update last used timestamp
	 */
	public function touch($addressId) {
		$this->updateAll([$this->alias . '.last_used' => 'NOW()'], [$this->alias . '.id' => $addressId]);
	}

	/**
	 * @param findType (defaults to all)
	 * @param id (foreign id)
	 * @param addressType (defaults to MAIN)
	 */
	public function getByType($type = 'all', $id = null, $addressType = null) {
		if ($id === null) {
			$id = UID;
		}
		if ($addressType === null) {
			$addressType = self::TYPE_MAIN;
		}
		return $this->find($type, ['conditions' => ['foreign_id' => $id, 'address_type_id' => $addressType]]);
	}

	/**
	 * Static Model::method()
	 */
	public static function addressTypes($value = null) {
		$options = [
			self::TYPE_MAIN => __('Main Residence'),
			self::TYPE_OTHER => __('Other'),

		];
		return parent::enum($value, $options);
	}

	const TYPE_MAIN = 1;
	const TYPE_OTHER = 9;

}
