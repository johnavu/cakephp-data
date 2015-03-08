<?php
App::uses('DataAppController', 'Data.Controller');

class LanguagesController extends DataAppController {

	public $paginate = ['order' => ['Language.name' => 'ASC']];

	public function beforeFilter() {
		parent::beforeFilter();
	}

	/**
	 * LanguagesController::admin_index()
	 *
	 * @return void
	 */
	public function admin_index() {
		$this->Language->recursive = 0;

		if (CakePlugin::loaded('Search')) {
			$this->Language->Behaviors->load('Search.Searchable');
			$this->Common->loadComponent(['Search.Prg']);

			$this->Prg->commonProcess();
			$this->paginate['conditions'] = $this->Language->parseCriteria($this->Prg->parsedParams());
		}

		$languages = $this->paginate();
		$this->set(compact('languages'));
	}

	public function admin_view($id = null) {
		if (empty($id) || !($language = $this->Language->find('first', ['conditions' => ['Language.id' => $id]]))) {
			$this->Flash->message(__('invalid record'), 'error');
			return $this->Common->autoRedirect(['action' => 'index']);
		}
		$this->set(compact('language'));
	}

	public function admin_add() {
		if ($this->Common->isPosted()) {
			$this->Language->create();
			if ($this->Language->save($this->request->data)) {
				$var = $this->request->data['Language']['name'];
				$this->Flash->message(__('record add %s saved', h($var)), 'success');
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->message(__('formContainsErrors'), 'error');
			}
		}
	}

	public function admin_edit($id = null) {
		if (empty($id) || !($language = $this->Language->find('first', ['conditions' => ['Language.id' => $id]]))) {
			$this->Flash->message(__('invalid record'), 'error');
			return $this->Common->autoRedirect(['action' => 'index']);
		}
		if ($this->Common->isPosted()) {
			if ($this->Language->save($this->request->data)) {
				$var = $this->request->data['Language']['name'];
				$this->Flash->message(__('record edit %s saved', h($var)), 'success');
				return $this->redirect(['action' => 'index']);
			} else {
				$this->Flash->message(__('formContainsErrors'), 'error');
			}
		}
		if (empty($this->request->data)) {
			$this->request->data = $language;
		}
	}

	public function admin_delete($id = null) {
		$this->request->allowMethod('post');
		if (empty($id) || !($language = $this->Language->find('first', ['conditions' => ['Language.id' => $id], 'fields' => ['id', 'name']]))) {
			$this->Flash->message(__('invalid record'), 'error');
			return $this->Common->autoRedirect(['action' => 'index']);
		}
		if ($this->Language->delete($id)) {
			$var = $language['Language']['name'];
			$this->Flash->message(__('record del %s done', h($var)), 'success');
			return $this->redirect(['action' => 'index']);
		}
		$this->Flash->message(__('record del %s not done exception', h($var)), 'error');
		return $this->Common->autoRedirect(['action' => 'index']);
	}

	/**
	 * Should only be done once at the very beginning
	 */
	public function admin_import_from_core() {
		if (!empty($this->request->params['named']['reset'])) {
			$this->Language->truncate();
		}
		//$languages = $this->Language->iso3ToIso2();
		$languages = $this->Language->catalog();
		$count = 0;
		$errors = [];
		foreach ($languages as $language) {
			if (!($code = $this->Language->iso3ToIso2($language['localeFallback']))) {
				$code = '';
			}
			$data = [
				'name' => $language['language'],
				'ori_name' => $language['language'],
				'code' => $code,
				'locale' => $language['locale'],
				'locale_fallback' => $language['localeFallback'],
				'direction' => $language['direction']
			];
			$this->Language->create();
			if ($this->Language->save($data)) {
				$count++;
			} else {
				$errors[] = ['data' => $language, 'errors' => $this->Language->validationErrors];
			}
		}

		$this->Flash->message($count . ' of ' . count($languages) . ' ' . __('languages added'), 'success');

		$errorMessage = [];
		foreach ($errors as $error) {
			$errorMessage[] = $error['data']['language'] . ' (' . returns($error['errors']) . ')';
		}
		$this->Flash->message(__('not added') . ' ' . implode(', ', $errorMessage), 'warning');
		return $this->redirect(['action' => 'index']);
		//pr($errors);
	}

	/**
	 * http://www.loc.gov/standards/iso639-2/php/code_list.php
	 */
	public function admin_compare_to_iso_list() {
		$isoList = $this->Language->getOfficialIsoList();

		$languages = $this->Language->find('all', []);

		$this->set(compact('isoList', 'languages'));
	}

	/**
	 * http://www.loc.gov/standards/iso639-2/php/code_list.php
	 */
	public function admin_compare_iso_list_to_core() {
		$isoList = $this->Language->getOfficialIsoList();

		$languages = $this->Language->catalog();
		$locales = [];
		foreach ($languages as $key => $value) {
			if (strlen($key) === 2) {
				$locales[$key] = $value;
				$locales[$key]['regional'] = [];
				continue;
			}
			if (strlen($key) === 1) {
				//$locales[$key] = $value;
				//$locales[$key]['deprecated'] = 1;
				continue;
			}
			$baseLocale = substr($key, 0, 2);
			if (!isset($locales[$baseLocale])) {
				$locales[$baseLocale] = ['missing_base' => 1];
			}
			$locales[$baseLocale]['regional'][] = $value;
		}

		//die(debug($locales));

		$this->set(compact('isoList', 'languages', 'locales'));
	}

	public function admin_set_primary_languages_active() {
		$languages = $this->Language->getPrimaryLanguages('list');
		$this->Language->updateAll(['status' => Language::STATUS_ACTIVE], ['id' => array_keys($languages)]);

		$this->Flash->message(__('%s of %s set active', $this->Language->getAffectedRows(), count($languages)), 'success');
		return $this->redirect(['action' => 'index']);
	}

/* probs:

Array
(
	[0] => Array
		(
			[data] => Array
				(
					[language] => Greek
					[locale] => gre
					[localeFallback] => gre
					[charset] => utf-8
					[direction] => ltr
				)

			[errors] => Array
				(
					[locale] => valErrRecordNameExists
				)

		)

	[1] => Array
		(
			[data] => Array
				(
					[language] => Indonesian
					[locale] => ind
					[localeFallback] => ind
					[charset] => utf-8
					[direction] => ltr
				)

			[errors] => Array
				(
					[locale] => valErrRecordNameExists
				)

		)

	[2] => Array
		(
			[data] => Array
				(
					[language] => Dutch (Standard)
					[locale] => dut
					[localeFallback] => dut
					[charset] => utf-8
					[direction] => ltr
				)

			[errors] => Array
				(
					[locale] => valErrRecordNameExists
				)

		)

	[3] => Array
		(
			[data] => Array
				(
					[language] => Polish
					[locale] => pol
					[localeFallback] => pol
					[charset] => utf-8
					[direction] => ltr
				)

			[errors] => Array
				(
					[locale] => valErrRecordNameExists
				)

		)

)

*/

}
