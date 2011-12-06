<?php
namespace lib\storage;

use \lib\entity\EntityInterface;
use \lib\entity\CollectionInterface;

/**
 * Adapter interface
 * Used by storages to access data
 *
 * @package Moss Storage
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface AdapterInterface {

	/**
	 * Creates adapter instance
	 *
	 * @abstract
	 * @param string $path path to configuration or to storage file
	 */
	public function __construct($path);

	/**
	 * Defines count operation
	 *
	 * @abstract
	 * @return AdapterInterface
	 */
	public function count();

	/**
	 * Defines read operation
	 *
	 * @abstract
	 * @return AdapterInterface
	 */
	public function select();

	/**
	 * Defines insert operation
	 *
	 * @abstract
	 * @return AdapterInterface
	 */
	public function insert();

	/**
	 * Defines update operation
	 *
	 * @abstract
	 * @return AdapterInterface
	 */
	public function update();

	/**
	 * Defines delete operation
	 *
	 * @abstract
	 * @return AdapterInterface
	 */
	public function delete();

	/**
	 * Defines table list operation
	 *
	 * @abstract
	 * @return AdapterInterface
	 */
	public function tables();

	/**
	 * Defines describe table operation
	 *
	 * @abstract
	 * @return AdapterInterface
	 */
	public function describe();

	/**
	 * Defines field used in read operation
	 * 
	 * @abstract
	 * @param string $field
	 * @param null|string $mapping
	 * @return AdapterInterface
	 */
	public function field($field, $mapping = null);

	/**
	 * Defines field value used in insert and update operation
	 *
	 * @abstract
	 * @param string $field field name
	 * @param string $value field value
	 * @param string $type value type (allowed types: i - integer, s - string, d - double/decimal/float, b - binary)
	 * @return AdapterInterface
	 */
	public function value($field, $value, $type = 's');

	/**
	 * Defines condition used in count and read operations
	 *
	 * @abstract
	 * @param string $field field name used in condition
	 * @param string $comparisonOperator comparison operator (allowed operators: ==, !=, <>, <, <=, >, =>)
	 * @param mixed $value value used in condition
	 * @param string $logicalOperator logical operator used between conditions (allowed operators: &&, ||, XOR)
	 * @param string $type value type (allowed types: i - integer, s - string, d - double/decimal/float, b - binary)
	 * @return AdapterInterface
	 */
	public function condition($field, $comparisonOperator, $value, $logicalOperator = '&&', $type = 's');

	/**
	 * Defines order in read operations
	 *
	 * @abstract
	 * @param string $field field that should be ordered
	 * @param string $order order type
	 * @return AdapterInterface
	 */
	public function order($field, $order = 'asc');

	/**
	 * Defines limit for read operations
	 *
	 * @abstract
	 * @param int $limit
	 * @param null|int $offset
	 * @return AdapterInterface
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
	 * Executes defined operation
	 *
	 * @abstract
	 * @param string $container operation container
	 * @param string $entity entity class
	 * @param string $collection collection class
	 * @return \lib\entity\CollectionInterface|\lib\entity\EntityInterface|integer|boolean
	 */
	public function execute($container, $entity, $collection);

	/**
	 * Resets adapter instance data (operations, conditions, relations)
	 *
	 * @abstract
	 * @return AdapterInterface
	 */
	public function reset();
}
