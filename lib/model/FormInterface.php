<?php
namespace lib\model;

use \lib\entity\EntityInterface;

/**
 * Form interface
 * Models implementing this interface provide form for entities
 *
 * @package Moss Model
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface FormInterface {

	/**
	 * Retrieves form
	 *
	 * @abstract
	 * @param \lib\entity\EntityInterface $Entity
	 * @return \component\form\FormInterface
	 */
	public function getForm(EntityInterface $Entity);

}
