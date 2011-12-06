<?php
namespace lib\model;

/**
 * Countable interface
 * Models implementing this interface return amount of entities returned
 *
 * @package Moss Model
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface CountableInterface {

	/**
	 * Counts results
	 *
	 * @abstract
	 * @return \lib\model\ModelInterface
	 */
	public function getCount();
}
