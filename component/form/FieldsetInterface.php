<?php
namespace component\form;

use component\form\Element;

/**
 * Fieldset interface
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface FieldsetInterface extends \ArrayAccess {

	/**
	 * Checks if fieldset is valid
	 * Fieldset is valid if all fields in it are valid
	 *
	 * @abstract
	 * @return bool
	 */
	public function isValid();
}
