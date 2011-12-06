<?php
namespace component\form;

use \component\form\Button;

/**
 * Submit form button
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Submit extends Button {

	/**
	 * Constructor
	 *
	 * @param string $name field name
	 * @param null $value field value
	 * @param array $attributes additional attributes
	 */
	public function __construct($name, $value = null, $attributes = array()) {
		$this->name = $name;
		$this->value($value);
		$this->attributes = $attributes;
	}

	/**
	 * Renders label
	 * Submit has no label
	 *
	 * @param \DOMDocument $DOM
	 * @return null
	 */
	protected function renderLabel(\DOMDocument $DOM) {
		return null;
	}

	/**
	 * Renders field
	 *
	 * @param \DOMDocument $DOM
	 * @return \DOMElement
	 */
	protected function renderField(\DOMDocument $DOM) {
		$element = $DOM->createElement('input');
		$element->setAttribute('type', 'submit');
		$element->setAttribute('name', $this->name);
		$element->setAttribute('value', $this->value);
		$element->setAttribute('id', $this->identify());

		foreach($this->attributes as $name => $value) {
			$element->setAttribute($name, $value);
		}

		return $element;
	}

	/**
	 * Renders field errors
	 * Submit does not generate errors
	 *
	 * @param \DOMDocument $DOM
	 * @return null
	 */
	protected function renderError(\DOMDocument $DOM) {
		return null;
	}

}
