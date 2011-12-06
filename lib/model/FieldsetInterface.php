<?php
namespace lib\model;

use \lib\entity\EntityInterface;

/**
 * Fieldset interface
 * Models implementing this interface provide part of form
 *
 * @package Moss Model
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface FieldsetInterface {

	/**
	 * Retrieves form
	 *
	 * @abstract
	 * @param \lib\entity\EntityInterface $Entity
	 * @return \component\form\FieldsetInterface
	 */
	public function getFieldset(EntityInterface $Entity);

}
