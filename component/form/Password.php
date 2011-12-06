<?php
namespace component\form;

use \component\form\Element;

/**
 * Password form field
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Password extends Element {

	/**
	 * Constructor
	 *
	 * @param string $name field name
	 * @param null $value field label
	 * @param null $label field value
	 * @param bool $required if true "required" tag will be inserted into label
	 * @param array $attributes additional attributes as associative array
	 */
	public function __construct($name, $value = null, $label = null, $required = false, $attributes = array()) {
		$this->name = $name;
		$this->value($value);
		$this->label($label, $required);
		$this->attributes = $attributes;
	}

	/**
	 * Sets field value
	 *
	 * @param string $value field value
	 * @return Password
	 */
	public function value($value) {
		$this->value = $value;
		return $this;
	}

	/**
	 * Renders field
	 *
	 * @param \DOMDocument $DOM
	 * @return \DOMElement
	 */
	protected function renderField(\DOMDocument $DOM) {
		$element = $DOM->createElement('input');
		$element->setAttribute('type', 'password');
		$element->setAttribute('name', $this->name);
		$element->setAttribute('value', $this->value);
		$element->setAttribute('id', $this->identify());

		if($this->required) {
			$element->setAttribute('required', 'required');
		}

		foreach($this->attributes as $name => $value) {
			$element->setAttribute($name, $value);
		}

		return $element;
	}
}
