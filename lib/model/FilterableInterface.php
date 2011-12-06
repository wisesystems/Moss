<?php
namespace lib\model;

use \lib\model\ConditionalInterface;

/**
 * Filterable interface
 * Models implementing this interface can be filtered
 *
 * @package Moss Model
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface FilterableInterface extends ConditionalInterface {

	/**
	 * Filters retrieved entities
	 *
	 * @abstract
	 * @param array $iArr array containing filter data
	 * @return \component\form\FieldsetInterface
	 */
	public function filter(Array $iArr = array());
}
