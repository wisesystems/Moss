<?php
namespace component\form;

use \component\form\Element;

/**
 * Textarea form field
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Textarea extends Element {

	protected $cols = 20;
	protected $rows = 5;

	/**
	 * Constructor
	 *
	 * @param string $name field name
	 * @param null $value field value
	 * @param null $label field label
	 * @param bool $required if true "required" tag will be inserted into field label
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
	 * @return Textarea
	 */
	public function value($value) {
		$this->value = $value;

		return $this;
	}

	/**
	 * Sets field width in columns
	 *
	 * @param int $cols number of columns
	 * @return Textarea
	 */
	public function cols($cols = 20) {
		$this->cols = (int) $cols;

		return $this;
	}

	/**
	 * Sets field height in rows
	 *
	 * @param int $rows number of rows
	 * @return Textarea
	 */
	public function rows($rows = 5) {
		$this->rows = (int) $rows;

		return $this;
	}

	/**
	 * Renders field
	 *
	 * @param \DOMDocument $DOM
	 * @return \DOMElement
	 */
	protected function renderField(\DOMDocument $DOM) {
		$element = $DOM->createElement('textarea');
		$element->setAttribute('name', $this->name);
		$element->setAttribute('cols', $this->cols);
		$element->setAttribute('rows', $this->rows);
		$element->setAttribute('id', $this->identify());

		if($this->required) {
			$element->setAttribute('required', 'required');
		}

		$value = $DOM->createTextNode($this->value);
		$element->appendChild($value);

		foreach($this->attributes as $name => $value) {
			$element->setAttribute($name, $value);
		}

		return $element;
	}
}
