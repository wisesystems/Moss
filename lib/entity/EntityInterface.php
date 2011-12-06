<?php
namespace lib\entity;

/**
 * Entity interface
 * Entity represents data set (in ActiveRecord entity represents row)
 * Entities can contain other entities, values or entity collections
 *
 * @package Moss DDD
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface EntityInterface extends \ArrayAccess {

	/**
	 * Retrieves entity data as associative array
	 *
	 * @abstract
	 * @return array
	 */
	public function retrieve();

	/**
	 * Identifies entity.
	 * If argument passed sets new identifier
	 *
	 * @abstract
	 * @param null|int|string $identifier entity identifier
	 * @return mixed
	 */
	public function identify($identifier = null);

	/**
	 * Assigns value to property
	 *
	 * @abstract
	 * @param string $property property name
	 * @param mixed $value property value
	 * @return EntityInterface
	 */
	public function set($property, $value);

	/**
	 * Retrieves property value
	 * Throws exception if property does not exist
	 *
	 * @abstract
	 * @throws \DomainException
	 * @param string $property property name
	 * @return mixed
	 */
	public function get($property);
}
