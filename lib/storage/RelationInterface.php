<?php
namespace lib\storage;

use \lib\storage\StorageInterface;
use \lib\entity\EntityInterface;
use \lib\entity\CollectionInterface;

/**
 * Relation representation
 * In Moss Storage all relations are preformed in PHP not in data sources
 * it allows to combine different data sources
 *
 * @package Moss Storage
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface RelationInterface {

	/**
	 * Defines local and foreign key
	 * In addition, lets allows to set value when relation depends on other than entity data (e.g. entity class)
	 *
	 * @param null|string $local
	 * @param null|string $foreign
	 * @param null|string $value values for foreign keys if relation depends on other than entity data (e.g. entity class)
	 * @return RelationInterface
	 */
	public function key($local = null, $foreign = null, $value = null);

	/**
	 * Identifies relation
	 *
	 * @abstract
	 * @return string
	 */
	public function identify();

	/**
	 * Resets relation storage
	 *
	 * @abstract
	 * @return RelationInterface
	 */
	public function reset();

	/**
	 * Executes read for relation
	 *
	 * @abstract
	 * @param \lib\entity\CollectionInterface $result
	 * @return void
	 */
	public function read(CollectionInterface $result);
	
	/**
	 * Executes write for relation
	 *
	 * @abstract
	 * @param \lib\entity\EntityInterface $Entity
	 * @return void
	 */
	public function write(EntityInterface $Entity);

	/**
	 * Executes delete for relation
	 *
	 * @abstract
	 * @param \lib\entity\EntityInterface $Entity
	 * @return void
	 */
	public function delete(EntityInterface $Entity);
}
