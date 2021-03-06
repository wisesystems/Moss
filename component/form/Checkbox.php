<?php
namespace component\form;

use \component\form\Element;

/**
 * Checkbox form field
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Checkbox extends Element {

	protected $options = array();

	/**
	 * Constructor
	 *
	 * @param string $name field name
	 * @param null|string|array $value field value (checked values)
	 * @param null|string $label field label
	 * @param bool $required if true "required" tag will be inserted into label
	 * @param array $attributes additional attributes as associative array
	 * @param array $options available options as array, each option as array('name' => X, 'value' => Y)
	 */
	public function __construct($name, $value = null, $label = null, $required = false, $attributes = array(), $options = array()) {
		$this->name = $name;
		$this->identifier = $this->identify($name);
		$this->value($value);
		$this->label($label, $required);
		$this->attributes = $attributes;
		$this->options = (array) $options;
	}

	/**
	 * Sets field value
	 *
	 * @param int|string|array $value
	 * @return Checkbox
	 */
	public function value($value) {
		$this->value = (array) $value;

		return $this;
	}

	/**
	 * Adds checkbox to collection
	 *
	 * @param int|string $value
	 * @param null|int|string $name
	 * @return Checkbox
	 */
	public function option($value, $name = null) {
		$this->options[] = array(
			'value' => $value,
			'name' => $name === null ? $value : $name
		);

		return $this;
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

		if(count($this->options) == 1) {
			$element = $DOM->createElement('label', $this->label);
			$element->setAttribute('for', $this->identify());
		}
		else {
			$element = $DOM->createElement('span', $this->label);
			$element->setAttribute('class', 'label');
		}

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
		$elementPrototype = $DOM->createElement('input');
		$elementPrototype->setAttribute('type', 'checkbox');

		foreach($this->attributes as $name => $value) {
			$elementPrototype->setAttribute($name, $value);
		}

		if(count($this->options) == 1) {
			$element = clone($elementPrototype);
			$element->setAttribute('name', $this->name);
			$element->setAttribute('value', $this->options[0]['value']);
			$element->setAttribute('id', $this->identifier);

			if($this->required) {
				$element->setAttribute('required', 'required');
			}

			if(in_array($this->options[0]['value'], $this->value)) {
				$element->setAttribute('checked', 'checked');
			}

			return $element;
		}
		else {
			$container = $DOM->createElement('ul');
			$container->setAttribute('class', 'options'.(isset($this->attributes['class']) && !empty($this->attributes['class']) ? ' '.$this->attributes['class'] : null));

			if(empty($this->options)) {
				$this->options = array(array('name' => '--- No options defined ---', 'value' => 0));
			}

			$this->renderOptions($DOM, $this->options, $elementPrototype, $container);

			return $container;
		}
	}

	/**
	 * Renders field options
	 *
	 * @param \DOMDocument $DOM
	 * @param $options
	 * @param \DOMNode $elementPrototype
	 * @param \DOMNode $container
	 * @return void
	 */
	protected function renderOptions(\DOMDocument $DOM, $options, \DOMNode $elementPrototype, \DOMNode $container) {
		foreach($options as $key => $option) {
			$element = clone($elementPrototype);
			$element->setAttribute('name', $this->name.'[]');
			$element->setAttribute('value', $option['value']);
			$element->setAttribute('id', $this->strip($this->identifier.'_'.$option['value']));

			if($key == 0 && $this->required) {
				$element->setAttribute('required', 'required');
			}

			if(in_array($option['value'], $this->value)) {
				$element->setAttribute('checked', 'checked');
			}

			$label = $DOM->createElement('label', $option['name']);
			$label->setAttribute('for', $this->strip($this->identifier.'_'.$option['value']));

			if(isset($option['disabled']) && $option['disabled']) {
				$element->setAttribute('disabled', 'disabled');
				$label->setAttribute('class', 'disabled');
			}

			$elementContainer = $DOM->createElement('li');
			$elementContainer->appendChild($element);
			$elementContainer->appendChild($label);
			$container->appendChild($elementContainer);

			if(isset($option['content']) && is_array($option['content']) && !empty($option['content'])) {
				$subContainer = $DOM->createElement('ul');
				$elementContainer->appendChild($subContainer);

				$this->renderOptions($DOM, $option['content'], $elementPrototype, $subContainer);
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
		if(count($this->options) == 1) {
			if($field = $this->renderField($DOM)) {
				$Container->appendChild($field);
			}

			if($label = $this->renderLabel($DOM)) {
				$Container->appendChild($label);
			}

			if($error = $this->renderError($DOM)) {
				$Container->appendChild($error);
			}
		}
		else {
			if($label = $this->renderLabel($DOM)) {
				$Container->appendChild($label);
			}

			if($error = $this->renderError($DOM)) {
				$Container->appendChild($error);
			}

			if($field = $this->renderField($DOM)) {
				$Container->appendChild($field);
			}
		}

		return $Container;
	}
}
