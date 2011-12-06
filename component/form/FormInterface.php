<?php
namespace component\form;

use \component\form\FieldsetInterface;

/**
 * Form interface
 * 
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface FormInterface extends FieldsetInterface {

	/**
	 * Sets forms action
	 *
	 * * @abstract
	 * @param string $action
	 * @return Form
	 */
	public function setAction($action);

	/**
	 * Sets forms sending method
	 *
	 * * @abstract
	 * @param string $method
	 * @return Form
	 */
	public function setMethod($method);

	/**
	 * Sets forms encoding type
	 *
	 * * @abstract
	 * @param string $enctype
	 * @return Form
	 */
	public function setEnctype($enctype);

	/**
	 * Renders entire form
	 *
	 * * @abstract
	 * @return string
	 */
	public function render();

	/**
	 * Renders form as string
	 *
	 * @abstract
	 * @return string
	 */
	public function __toString();
}
