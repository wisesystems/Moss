<?php
namespace component\form;

use \component\form\Element;

/**
 * Hidden form field
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Hidden extends Element {

	/**
	 * Constructor
	 *
	 * @param string $name field name
	 * @param null $value field value
	 * @param array $attributes additional attributes as associative array
	 */
	public function __construct($name, $value = null, $attributes = array()) {
		$this->name = $name;
		$this->value($value);
		$this->attributes = $attributes;
	}

	/**
	 * Returns true if field is visible
	 *
	 * @return bool
	 */
	public function isVisible() {
		return false;
	}

	/**
	 * Renders label
	 * Hidden field has no label
	 *
	 * @param \DOMDocument $DOM
	 * @return null
	 */
	protected function renderLabel(\DOMDocument $DOM) {
		return null;
	}

	/**
	 * Render field
	 *
	 * @param \DOMDocument $DOM
	 * @return \DOMElement
	 */
	protected function renderField(\DOMDocument $DOM) {
		$element = $DOM->createElement('input');
		$element->setAttribute('type', 'hidden');
		$element->setAttribute('name', $this->name);
		$element->setAttribute('value', $this->value);
		$element->setAttribute('id', $this->identify());

		foreach($this->attributes as $name => $value) {
			$element->setAttribute($name, $value);
		}

		return $element;
	}

	/**
	 * Render errors
	 * Hidden does not generate errors
	 *
	 * @param \DOMDocument $DOM
	 * @return null
	 */
	protected function getError(\DOMDocument $DOM) {
		return null;
	}
}
