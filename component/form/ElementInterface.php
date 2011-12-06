<?php
namespace component\form;

/**
 * Form field interface
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ElementInterface {

	/**
	 * Returns field identifier
	 * If no identifier is set - new is generated, based on field name
	 *
	 * @abstract
	 * @param null|string $identifier field identifier
	 * @return string
	 */
	public function identify($identifier = null);

	/**
	 * Sets field label
	 *
	 * @abstract
	 * @param string $label field label
	 * @param bool $required if true "required" tag will be inserted into label
	 * @return Element
	 */
	public function label($label, $required = false);

	/**
	 * Validates the field by given condition
	 * Condition can be: string (regular expression), array of values or function or closure
	 *
	 * @abstract
	 * @param string|array|Closure $condition condition witch will be used
	 * @param string $message error message if condition is not met
	 * @return Element
	 */
	public function condition($condition, $message);

	/**
	 * Sets field value
	 *
	 * @abstract
	 * @param mixed $value field value
	 * @return Button
	 */
	public function value($value);

	/**
	 * Checks if field is visible
	 * By default all fields are visible
	 *
	 * @abstract
	 * @return bool
	 */
	public function isVisible();

	/**
	 * Checks if field is valid (if all conditions have been met)
	 *
	 * @abstract
	 * @return bool
	 */
	public function isValid();

	/**
	 * Renders element
	 *
	 * @abstract
	 * @param \DOMDocument $DOM
	 * @param \DOMNode $Container
	 * @return \DOMNode
	 */
	public function render(\DOMDocument $DOM, \DOMNode $Container);

	/**
	 * Casts element to string
	 *
	 * @abstract
	 * @return mixed|string
	 */
	public function __toString();
}
