<?php

namespace Phalcon\Translate\Adapter;

use Phalcon\Translate\Adapter,
	Phalcon\Translate\AdapterInterface,
	Phalcon\Translate\Exception;

class Mongo extends Adapter implements AdapterInterface {

	protected $_options;

	/**
	 * Phalcon\Translate\Adapter\Mongo constructor
	 * @param array $options
	 * @throws \Phalcon\Translate\Exception
	 */
	public function __construct($options) {

		if (!is_array($options)) {
			throw new Exception("Translate options must be an array");
		}

		if (!isset($options['db'])) {
			throw new Exception("Parameter 'db' is required");
		}

		if (!isset($options['collection'])) {
			throw new Exception("Parameter 'collection' is required");
		}

		if (!isset($options['lang'])) {
			throw new Exception("Parameter 'lang' is required");
		}

		$this->_options = $options;
	}

	/**
	 * Returns a mongo collection
	 * @return \MongoCollection
	 */
	protected function _getCollection() {
		return $this->_options['db']->selectCollection($this->_options['collection']);
	}

	/**
	 * Returns the translation related to the given key
	 * @param    string $index
	 * @param    array  $placeholders
	 * @return    string
	 */
	public function query($index, $placeholders = null) {
		$query = array(
			'key'  => $index,
			'lang' => $this->_options['lang']
		);
		$translation = $this->_getCollection()->findOne($query, array('value'));
		$text = $translation ? $translation['value'] : $index;

		if ($placeholders == null) {
			return $text;
		}

		if (is_array($placeholders)) {
			foreach($placeholders as $key => $value) {
				$text = str_replace("%$key%", $value, $text);
			}
		}

		return $text;
	}

	/**
	 * Check whether is defined a translation key in the database
	 * @param string $index
	 * @return bool
	 */
	public function exists($index) {
		$exists = $this->_getCollection()->count(array('key' => $index, 'lang' => $this->_options['lang']));
		return $exists > 0;
	}
}