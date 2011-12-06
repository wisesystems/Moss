<?php
namespace component\form;

use \component\form\Radio;

/**
 * Select form field
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Select extends Radio {

	protected $options = array();

	/**
	 * Constructor
	 *
	 * @param string $name field name
	 * @param null|string $value field value (selected option value)
	 * @param null|string $label field label
	 * @param bool $required if true "required" tag will be inserted into label
	 * @param array $attributes additional attributes as associative array
	 * @param array $options available options as array, each option as array('name' => X, 'value' => Y)
	 */
	public function __construct($name, $value = null, $label = null, $required = false, $attributes = array(), $options = array()) {
		$this->name = $name;
		$this->value = $value;
		$this->label($label, $required);
		$this->attributes = (array) $attributes;

		$this->options = (array) $options;
	}

	/**
	 * Renders label
	 *
	 * @param \DOMDocument $DOM
	 * @return \DOMElement|null
	 */
	protected function renderLabel(\DOMDocument $DOM) {
		if(!$this->label) {
			return null;
		}

		$element = $DOM->createElement('label', $this->label);
		$element->setAttribute('for', $this->identify());
		if($this->required) {
			$element->appendChild($DOM->createElement('sup', '*'));
		}

		return $element;
	}

	/**
	 * Renders field
	 *
	 * @param \DOMDocument $DOM
	 * @return \DOMElement
	 */
	protected function renderField(\DOMDocument $DOM) {
		$element = $DOM->createElement('select');
		$element->setAttribute('name', $this->name);
		$element->setAttribute('id', $this->identify());

		if($this->required) {
			$element->setAttribute('required', 'required');
		}

		foreach($this->attributes as $name => $value) {
			$element->setAttribute($name, $value);
		}

		if(empty($this->options)) {
			$this->options = array(array('name' => '--- No options defined ---', 'value' => 0));
		}

		$elementPrototype = $DOM->createElement('option');
		$this->renderOptions($DOM, $this->options, $elementPrototype, $element);

		return $element;
	}

	/**
	 * Renders field options
	 *
	 * @param \DOMDocument $DOM
	 * @param array $options
	 * @param \DOMNode $elementPrototype
	 * @param \DOMNode $container
	 * @param string $depth
	 * @return void
	 */
	protected function renderOptions(\DOMDocument $DOM, $options, \DOMNode $elementPrototype, \DOMNode $container, $depth = '') {
		foreach($options as $option) {
			$element = clone($elementPrototype);
			$element->setAttribute('value', $option['value']);

			$option['name'] = $depth.$option['name'];

			$element->appendChild($DOM->createTextNode($option['name']));

			if($option['value'] == $this->value) {
				$element->setAttribute('selected', 'selected');
			}

			if(isset($option['disabled']) && $option['disabled']) {
				$element->setAttribute('disabled', 'disabled');
			}

			$container->appendChild($element);

			if(isset($option['content']) && is_array($option['content']) && !empty($option['content'])) {
				$this->renderOptions($DOM, $option['content'], $elementPrototype, $container, $depth.'- ');
			}
		}
	}

	/**
	 * Renders element
	 *
	 * @param \DOMDocument $DOM
	 * @param \DOMNode $Container
	 * @return \DOMNode
	 */
	public function render(\DOMDocument $DOM, \DOMNode $Container) {
		if($label = $this->renderLabel($DOM)) {
			$Container->appendChild($label);
		}

		if($field = $this->renderField($DOM)) {
			$Container->appendChild($field);
		}

		if($error = $this->renderError($DOM)) {
			$Container->appendChild($error);
		}

		return $Container;
	}
}
