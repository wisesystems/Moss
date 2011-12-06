<?php
namespace component\form;

use \component\form\FieldsetInterface;

use component\form\Element;

/**
 * Object oriented fieldset representation
 * Fieldset is represented as unordered lists
 * If fieldset is nested in existing form - list will be also nested
 * 
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Fieldset extends \ArrayObject implements FieldsetInterface {

	protected $label;
	protected $attributes = array();

	/**
	 * Constructor
	 *
	 * @param null $label fieldset label, if set - will be used as form field key
	 * @param array $fields array containing fields
	 * @param array $attributes additional attributes as associative array
	 */
	public function __construct($label = null, $fields = array(), $attributes = array()) {
		parent::__construct($fields, 2);
		$this->label = $label;
		$this->attributes = $attributes;
	}

	/**
	 * Checks if fieldset is valid
	 * Fieldset is valid if all fields in it are valid
	 *
	 * @return bool
	 */
	public function isValid() {
		foreach($this as $element) {
			if(!$element->isValid()) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Renders entire fieldset
	 *
	 * @param \DOMDocument $DOM
	 * @param \DOMElement|null $parent
	 * @return \DOMElement|null
	 */
	protected function renderFieldset(\DOMDocument $DOM, \DOMElement $parent) {
		if(!count($this)) {
			return $DOM->createTextNode('');
		}

		if($this->label) {
			$label = $DOM->createElement('span', $this->label);
			$label->setAttribute('class', 'label'.(isset($this->attributes['class']) && !empty($this->attributes['class']) ? ' '.$this->attributes['class'] : null));
			$parent->appendChild($label);
		}

		if(isset($this->attributes['class']) && !empty($this->attributes['class'])) {
			$parent->setAttribute('class', $this->attributes['class']);
		}

		foreach($this as $element) {
			if(!$element instanceof Element || $element instanceof Fieldset ||$element->isVisible()) {
				continue;
			}

			$element->render($DOM, $parent);
		}

		$list = $DOM->createElement('ul');

		foreach($this->attributes as $name => $value) {
			$list->setAttribute($name, $value);
		}

		foreach($this as $key => $element) {
			if(!$element instanceof Element && !$element instanceof Fieldset) {
				continue;
			}
			
			if($element instanceof Fieldset) {
				$node = $DOM->createElement('li');
				$node->appendChild($element->renderFieldset($DOM, $node));
				$list->appendChild($node);
				continue;
			}

			if(!$element->isVisible()) {
				continue;
			}

			$node = $DOM->createElement('li');
			$list->appendChild($node);

			if(!$element->isValid()) {
				$this->error = true;
				$node->setAttribute('class', 'error');
			}


			$element->render($DOM, $node);
		}

		return $list;
	}
}
