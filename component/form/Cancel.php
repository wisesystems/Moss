<?php
namespace component\form;

use \component\form\Button;

/**
 * Cancel button
 * Implemented as link
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Cancel extends Button {

	/**
	 * Constructor
	 *
	 * @param string $address url to redirect to
	 * @param null $value field value
	 * @param array $attributes additional attributes
	 */
	public function __construct($address, $value = null, $attributes = array()) {
		$this->name = $address;
		$this->value($value);
		$this->attributes = $attributes;

		if(!isset($this->attributes['class'])) {
			$this->attributes['class'] = 'button cancel';
		}
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
		$element = $DOM->createElement('a');
		$element->setAttribute('href', $this->name);

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
