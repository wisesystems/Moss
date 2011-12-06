<?php
namespace lib\entity;

use IteratorAggregate;
use ArrayAccess;
use Serializable;
use Countable;

/**
 * Collection interface
 * Collection is object representation of same type entities
 * Collection allows preform actions on itself (e.g. sorting, hierarchization and so on)
 *
 * @package Moss DDD
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface CollectionInterface extends IteratorAggregate , ArrayAccess , Serializable , Countable{

	/**
	 * Retrieves collection content as multidimensional array (collection and its entities)
	 *
	 * @abstract
	 * @return array
	 */
	public function retrieve();

	/**
	 * Sorts an array by key in reverse order, maintaining key to data correlations
	 *
	 * @abstract
	 * @return CollectionInterface
	 */
	public function krsort();
}
