<?php
namespace component\form;

use \component\form\Element;

/**
 * Button
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Button extends Element {

	/**
	 * Constructor
	 *
	 * @param string $name field name
	 * @param null $value field label
	 * @param array $attributes additional attributes as associative array
	 */
	public function __construct($name, $value = null, $attributes = array()) {
		$this->name = $name;
		$this->value($value);
		$this->attributes = $attributes;
	}

	/**
	 * Renders label
	 * Button has no label
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
		$element->setAttribute('type', 'button');
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
	 * Button does not generate errors
	 *
	 * @param \DOMDocument $DOM
	 * @return null
	 */
	protected function renderError(\DOMDocument $DOM) {
		return null;
	}
}
