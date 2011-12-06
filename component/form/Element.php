<?php
namespace component\form;

use \component\form\ElementInterface;

/**
 * Abstract form element prototype
 *
 * @throws \InvalidArgumentException
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
abstract class Element implements ElementInterface {

	protected $identifier;

	protected $label;
	protected $required;

	protected $name;
	protected $value;
	protected $attributes;


	protected $error;

	/**
	 * Constructor
	 *
	 * @abstract
	 * @param string $name field name
	 * @param null $value field value
	 * @param null $label field label
	 * @param bool $required if true "required" tag will be inserted into label
	 * @param array $attributes additional attributes as associative array
	 */
	abstract public function __construct($name, $value = null, $label = null, $required = false, $attributes = array());

	/**
	 * Returns field identifier
	 * If no identifier is set - new is generated, based on field name
	 *
	 * @param null|string $identifier field identifier
	 * @return string
	 */
	public function identify($identifier = null) {
		if($identifier) {
			$this->identifier = $identifier;
		}

		if(!$identifier && $this->identifier) {
			return $this->identifier;
		}

		$identifier = $this->strip($this->name);

		return $identifier;
	}

	/**
	 * Sets field label
	 *
	 * @param string $label field label
	 * @param bool $required if true "required" tag will be inserted into label
	 * @return Element
	 */
	public function label($label, $required = false) {
		$this->label = !empty($label) ? $label : $this->name;
		$this->required = (bool) $required;

		return $this;
	}

	/**
	 * Validates the field by given condition
	 * Condition can be: string (regular expression), array of values or function or closure
	 *
	 * @param string|array|Closure $condition condition witch will be used
	 * @param string $message error message if condition is not met
	 * @return Element
	 */
	public function condition($condition, $message) {
		if(!$this->required && empty($this->value)) {
			return $this;
		}

		if(is_string($condition)) { // checks if condition is string (regexp)
			if(!preg_match($condition, $this->value)) {
				$this->error[] = $message;
			}
		}
		elseif(is_array($condition)) { // check if condition is array of permitted values
			if(!in_array($this->value, $condition)) {
				$this->error[] = $message;
			}
		}
		elseif(is_callable($condition)) { // checks if condition is closure
			if(!$condition($this->value)) {
				$this->error[] = $message;
			}
		}
		else {
			throw new \InvalidArgumentException('Invalid condition for field '.$this->name.'. Allowed condition types: regexp string, array of permitted values or closure');
		}
		
		return $this;
	}

	/**
	 * Sets field value
	 *
	 * @param mixed $value field value
	 * @return Button
	 */
	public function value($value) {
		$this->value = $value;

		return $this;
	}

	/**
	 * Checks if field is visible
	 * By default all fields are visible
	 *
	 * @return bool
	 */
	public function isVisible() {
		return true;
	}

	/**
	 * Checks if field is valid (if all conditions have been met)
	 *
	 * @return bool
	 */
	public function isValid() {
		return empty($this->error);
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
	 * @abstract
	 * @param \DOMDocument $DOM
	 * @return \DOMElement
	 */
	abstract protected function renderField(\DOMDocument $DOM);

	/**
	 * Renders field errors
	 *
	 * @param \DOMDocument $DOM
	 * @return \DOMElement|null
	 */
	protected function renderError(\DOMDocument $DOM) {
		if(empty($this->error)) {
			return null;
		}

		$element = $DOM->createElement('span');
		$element->setAttribute('class', 'error');

		foreach($this->error as $message) {
			$error = $DOM->createTextNode($message);
			$element->appendChild($error);
		}

		return $element;
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

	/**
	 * Casts element to string
	 *
	 * @return mixed|string
	 */
	public function __toString() {
		$DOM = new \DOMDocument();

		if($element = $this->renderLabel($DOM)) {
			$DOM->appendChild($element);
		}

		if($element = $this->renderField($DOM)) {
			$DOM->appendChild($element);
		}
		
		if($element = $this->renderError($DOM)) {
			$DOM->appendChild($element);
		}

		$DOM->preserveWhiteSpace = false;
		$DOM->formatOutput = true;

		/* HTML formatting */
		$output = $DOM->saveXML();

		$output = substr($output, strpos($output, "\n")+1);
		$output = str_replace('  ', "\t", $output);

		return $output;
	}

	/**
	 * Strips string from invalid characters
	 *
	 * @param string $string string to strip
	 * @return string
	 */
	protected function strip($string) {
		$string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
		$string = strtolower($string);
		$string = preg_replace('#[^a-z0-9_-]+#i', '_', $string);
		$string = trim($string, '_');

		return $string;
	}
}
