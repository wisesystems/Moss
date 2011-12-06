<?php
namespace lib\model;

use \lib\entity\EntityInterface;

/**
 * Writeable model interface
 * Models implementing this interface allow for write and delete entities
 *
 * @package Moss Model
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface WriteableInterface {

	/**
	 * Creates entity structure associated with model
	 *
	 * @abstract
	 * @param array $data entity initial data
	 * @return \lib\entity\EntityInterface
	 */
	public function createEntity($data = array());

	/**
	 * Creates collection structure associated with model
	 *
	 * @abstract
	 * @param array $data collection initial data
	 * @return \lib\entity\CollectionInterface
	 */
	public function createCollection($data = array());

	/**
	 * Writes entity into storage
	 *
	 * @abstract
	 * @param \lib\entity\EntityInterface $Entity entity that will be written
	 * @return \lib\model\ModelInterface
	 */
	public function write(EntityInterface $Entity);

	/**
	 * Removes entity from storage
	 *
	 * @abstract
	 * @param \lib\entity\EntityInterface $Entity entity that will be deleted
	 * @return \lib\model\ModelInterface
	 */
	public function delete(EntityInterface $Entity);
}
