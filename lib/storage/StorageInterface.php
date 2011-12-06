<?php
namespace lib\storage;
use \lib\storage\RelationInterface;
use \lib\storage\AdapterInterface;

use \lib\entity\EntityInterface;
use \lib\entity\CollectionInterface;

/**
 * Storage objects interface
 * Allows for universal object oriented access to data source adapters
 * Also allows for translation of entities to adapter understandable commands
 *
 * @package Moss Storage
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface StorageInterface {

	/**
	 * Defines storage container name
	 * e.g. database table or xml namespace
	 *
	 * @param $container container name
	 * @return StorageInterface
	 */
	public function container($container);

	/**
	 * Defines entity class that will be returned
	 *
	 * @param $className class of retrieved entities
	 * @return StorageInterface
	 */
	public function entity($className);

	/**
	 * Creates associated entity instance
	 *
	 * @param array $data
	 * @return \lib\entity\EntityInterface
	 */
	public function createEntity(Array $data = array());

	/**
	 * Defines collection class that will be returned
	 *
	 * @param $className class of retrieved collections
	 * @return StorageInterface
	 */
	public function collection($className);

	/**
	 * Creates associated collection instance
	 *
	 * @param array $data data passed to collection constructor
	 * @return \lib\entity\CollectionInterface
	 */
	public function createCollection(Array $data = array());

	/**
	 * Defines field in storage container
	 * Undefined fields will not be written
	 *
	 * @param string $field field name in retrieved entity
	 * @param bool $required true if field is required (if not set, update/insert will throw exceptions)
	 * @param string $type type of field (allowed types: i - integer, s - string, d - double/decimal/float, b - binary)
	 * @param bool $index true if field should be treated as index
	 * @param bool $primaryIndex true if field should be treated as primary index
	 * @param null|string $mapping actual field name in storage container
	 * @return StorageInterface
	 */
	public function field($field, $required = true, $type = 's', $index = false, $primaryIndex = false, $mapping = null);

	/**
	 * Defines count operation on storage container
	 * Execution will return affected row count
	 *
	 * @abstract
	 * @return StorageInterface
	 */
	public function count();

	/**
	 * Defines read operation on storage container
	 * Execution will return collection of entities
	 *
	 * @abstract
	 * @return StorageInterface
	 */
	public function read();

	/**
	 * Defines read one operation on storage container
	 * Execution will return one entity
	 *
	 * @abstract
	 * @return StorageInterface
	 */
	public function readOne();

	/**
	 * Defines write operation on storage container
	 * Execution will decide if update or insert operation should be made
	 * Execution will return written entity
	 *
	 * @abstract
	 * @param \lib\entity\EntityInterface $Entity entity that will be written
	 * @return StorageInterface
	 */
	public function write(EntityInterface $Entity);

	/**
	 * Defines insert operation on storage container
	 * Execution will return inserted entity
	 *
	 * @abstract
	 * @param \lib\entity\EntityInterface $Entity entity that will be inserted
	 * @return StorageInterface
	 */
	public function insert(EntityInterface $Entity);

	/**
	 * Defines update operation on storage container
	 * Execution will return updated entity
	 *
	 * @abstract
	 * @param \lib\entity\EntityInterface $Entity entity that will be updated
	 * @return StorageInterface
	 */
	public function update(EntityInterface $Entity);

	/**
	 * Defines delete operation on storage container
	 * Execution will return boolean result
	 *
	 * @abstract
	 * @param \lib\entity\EntityInterface $Entity entity that will be deleted
	 * @return StorageInterface
	 */
	public function delete(EntityInterface $Entity);

	/**
	 * Defines fields used in read operations
	 * Each argument represents single field name
	 *
	 * @abstract
	 * @return StorageInterface
	 */
	public function fields();

	/**
	 * Defines condition for count and read operations
	 *
	 * @abstract
	 * @param string $field field name used in condition
	 * @param mixed $value value used in condition
	 * @param string $comparisonOperator comparison operator (allowed operators: ==, !=, <>, <, <=, >, =>)
	 * @param string $logicalOperator logical operator used between conditions (allowed operators: &&, ||, XOR)
	 * @return StorageInterface
	 */
	public function condition($field, $value, $comparisonOperator = '==', $logicalOperator = '&&');

	/**
	 * Defines order for read operations
	 *
	 * @abstract
	 * @param string $field field that should be ordered
	 * @param string $order order type
	 * @return StorageInterface
	 */
	public function order($field, $order = 'asc');

	/**
	 * Defines limit for read operations
	 *
	 * @abstract
	 * @param int $limit
	 * @param null|int $offset
	 * @return StorageInterface
	 */
	public function limit($limit, $offset = null);

	/**
	 * Defines field used in collection as element keys
	 *
	 * @abstract
	 * @param string $field field name
	 * @return StorageInterface
	 */
	public function keyField($field);

	/**
	 * Retrieves key field used in collection as element keys
	 *
	 * @abstract
	 * @return null|string
	 */
	public function hasKeyField();

	/**
	 * Defines storage instance for relation in read, write, delete operations
	 *
	 * @abstract
	 * @param RelationInterface $Relation relation name that instance will be associated
	 * @return StorageInterface
	 */
	public function relation(RelationInterface $Relation);

	/**
	 * If storage has relation
	 * If identifier passed, checks if relation has specified relation, otherwise if any relation
	 *
	 * @abstract
	 * @param string $identifier
	 * @return bool
	 */
	public function hasRelation($identifier = null);

	/**
	 * Executes defined operation
	 *
	 * @abstract
	 * @return mixed
	 */
	public function execute();

	/**
	 * Resets storage instance data (operations, conditions, relations)
	 *
	 * @abstract
	 * @param bool $relations if true, all relations will be removed
	 * @return StorageInterface
	 */
	public function reset($relations = true);
}
