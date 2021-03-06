<?php
App::uses('DataAppModel', 'Data.Model');

class MimeTypeImage extends DataAppModel {

	public $order = ['MimeTypeImage.modified' => 'DESC'];

	public $validate = [
		'name' => [ # e.g. "exe"
			'isUnique' => [
				'rule' => ['isUnique'],
				'message' => 'valErrMandatoryField',
				//'required' => true
			],
			'notEmpty' => [
				'rule' => ['notEmpty'],
				'message' => 'valErrMandatoryField',
				//'required' => true
			],
		],
		'ext' => [], # e.g. "jpg" on a file "exe.jpg"
		'active' => ['numeric']
	];

	public $hasMany = [
		'MimeType' => [
			'className' => 'Data.MimeType',
			'foreignKey' => 'mime_type_image_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
		]
	];

	public function beforeSave($options = []) {
		parent::beforeSave($options);
		if (isset($this->data[$this->alias]['name'])) {
			$this->data[$this->alias]['name'] = mb_strtolower($this->data[$this->alias]['name']);
		}
		if (isset($this->data[$this->alias]['ext'])) {
			$this->data[$this->alias]['ext'] = mb_strtolower($this->data[$this->alias]['ext']);
		}

		return true;
	}

	public function afterSave($created, $options = []) {
		# clean up!
		$this->cleanUp();
		return true;
	}

	public function beforeDelete($cascade = true) {
		# retrieve infos
		$this->_del = $this->find('first', ['conditions' => [$this->alias . '.id' => $this->id]]);

		return true;
	}

	public function afterDelete() {
		if (!empty($this->_del)) {
			# todo: ...
			$image = $this->_del[$this->alias]['name'] . '.' . $this->_del[$this->alias]['ext'];

			# delete image (right now: move to archive)
			if (file_exists(PATH_MIMETYPES . $image)) {
				if (!rename(PATH_MIMETYPES . $image, PATH_MIMETYPES . 'archive' . DS . $image)) {
					return false;
				}
			}

			# remove id from mime_types table

			$types = $this->MimeType->find('all', ['fields' => ['id'], 'conditions' => ['mime_type_image_id' => $this->_del[$this->alias]['id']]]);
			foreach ($types as $type) {
				$this->MimeType->id = $type[$this->MimeType->alias]['id'];
				$this->MimeType->saveField('mime_type_image_id', 0);
				//pr ($type[$this->MimeType->alias]['id'].' del success');
			}
		}

		# clean up!
		$this->cleanUp();
		return true;
	}

	public function cleanUp() {
		App::uses('File', 'Utility');
		$handle = new File(FILES . 'mime_types.txt');
		$handle->delete();
	}

	public function findList() {
		$list = [];
		$images = $this->find('all', ['conditions' => ['active' => 1]]); // ,'contain'=>'MimeType.id'
		foreach ($images as $image) {
			//$count = count($image['MimeType']);
			$list[$image['MimeTypeImage']['id']] = $image['MimeTypeImage']['name'] . '.' . (!empty($image['MimeTypeImage']['ext']) ? $image['MimeTypeImage']['ext'] : '?');
		}
		return $list;
	}

	public function allocate($id = null, $fileName = null, $ext = null) {
		if (empty($fileName) && empty($id) || empty($ext)) {
			return false;
		}

		if (empty($id)) {
			# new entry
			$this->create();
			$data = ['name' => $fileName, 'ext' => $ext, 'active' => 1];
			if ($this->save($data)) {
				return true;
			}
		} else {
			$this->id = $id;
			$data = ['ext' => $ext];
			if ($this->save($data)) {
				return true;
			}
		}
		return false;
	}

/** Static Enums **/

	/**
	 * Static Model::method()
	 * ALLOWED EXTENSIONS
	 */
	public function extensions($value = null) {
		$options = [
			'gif' => __('GIF (*.gif)'),
			'png' => __('PNG (*.png)'),
			'jpg' => __('JPG (*.jpg)'),
		];

		if ($value !== null) {
			if (array_key_exists($value, $options)) {
				return $options[$value];
			}
			return '';
		}
		return $options;
	}
}
