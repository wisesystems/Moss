<?php
namespace lib\model;

/**
 * Sortable interface
 * Models implementing this interface allow for sorting returning data
 *
 * @package Moss Model
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface SortableInterface {

	/**
	 * Defines order for get operations
	 *
	 * @abstract
	 * @param string $field field that should be ordered
	 * @param string $order order type
	 * @return \lib\model\ModelInterface
	 */
	public function order($field, $order = 'asc');
}
