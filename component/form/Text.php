<?php
namespace component\form;

use \component\form\Element;

/**
 * Text form field
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Text extends Element {

	/**
	 * Constructor
	 *
	 * @param string $name field name
	 * @param null $value field value
	 * @param null $label field label
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
	 * Renders field
	 *
	 * @param \DOMDocument $DOM
	 * @return \DOMElement
	 */
	protected function renderField(\DOMDocument $DOM) {
		$element = $DOM->createElement('input');
		$element->setAttribute('type', 'text');
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

