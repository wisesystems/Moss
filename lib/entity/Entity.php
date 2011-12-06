<?php
namespace lib\entity;

use \lib\entity\EntityInterface;

/**
 * Entity prototype
 * Default entities (prototypes) can not be identified, therefore some methods should be overriden
 *
 * @throws \BadMethodCallException|\DomainException
 * @package Moss DDD
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Entity implements EntityInterface {

	/**
	 * Constructor
	 * Allows to set initial data
	 *
	 * @param array $iArr initial data
	 */
	public function __construct(Array $iArr = array()) {
		foreach(get_object_vars($this) as $field => $value) {
			if(isset($iArr[$field]) && !empty($iArr[$field])) {
				$this->$field = $iArr[$field];
			}
		}
	}

	/**
	 * Setter, sets property and its value.
	 * If property exits - will be updated otherwise adds new property
	 *
	 * @param string $property property name
	 * @param mixed $value property value
	 * @return \lib\entity\Entity
	 */
	public function set($property, $value) {
		$this->$property = $value;

		return $this;
	}

	/**
	 * Getter, retrieves property value
	 * If property does not exists null is returned
	 *
	 * @param string $property property name
	 * @return mixed
	 */
	public function get($property) {
		if(!isset($this->$property)) {
			return null;
		}
		
		return $this->$property;
	}

	/**
	 * Retrieves all entity properties
	 * This method should be overwritten in extending classes
	 * 
	 * @return array
	 */
	public function retrieve() {
		$data = array();

		foreach(get_class_vars(get_class($this)) as $param => $value) {
			$data[$param] = $this->$param;
		}

		return $data;
	}

	/**
	 * Identifies property
	 * Prototype Entity can not be identified
	 * This method should be overwritten in extending classes
	 *
	 * @throws \DomainException
	 * @param null $identifier
	 * @return void
	 */
	public function identify($identifier = null) {
		throw new \DomainException('Prototype Entity can not be identified!');
	}

	/**
	 * Checks if offset exists
	 *
	 * @param int|string $offset offset to check
	 * @return bool
	 */
	public function offsetExists($offset) {
		return isset($this->$offset);
	}

	/**
	 * Retrieves offset value
	 *
	 * @param int|string $offset offset to retrieve
	 * @return mixed
	 */
	public function offsetGet($offset) {
		return $this->get($offset);
	}

	/**
	 * Sets value for offset
	 *
	 * @throws \BadMethodCallException
	 * @param int|string $offset offset to set
	 * @param mixed $value offsets value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		throw new \BadMethodCallException('Forbidden! Use setters instead');
	}

	/**
	 * Unsets offset
	 *
	 * @throws \BadMethodCallException
	 * @param int|string $offset offset to unset
	 * @return void
	 */
	public function offsetUnset($offset) {
		throw new \BadMethodCallException('Forbidden! Use setters instead');
	}


}
