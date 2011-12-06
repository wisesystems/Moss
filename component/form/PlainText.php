<?php
namespace component\form;

use \component\form\Button;

/**
 * Plain text
 * Allows for text insertion into form structure
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class PlainText extends Button {

	/**
	 * Constructor
	 *
	 * @param string $text field value
	 * @param null|string $label field label
	 * @param array $attributes additional attributes
	 */
	public function __construct($text, $label = null, $attributes = array()) {
		$this->value($text);
		$this->label($label, false);
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
		if(!$this->label) {
			return null;
		}

		$element = $DOM->createElement('span', $this->label);
		$element->setAttribute('class', 'label');

		return $element;
	}

	/**
	 * Renders field
	 *
	 * @param \DOMDocument $DOM
	 * @return \DOMElement
	 */
	protected function renderField(\DOMDocument $DOM) {
		$element = $DOM->createElement('span');

		$element->appendChild($DOM->createTextNode($this->value));

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
