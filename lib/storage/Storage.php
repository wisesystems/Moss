<?php
namespace lib\storage;

use \lib\storage\StorageInterface;
use \lib\storage\RelationInterface;
use \lib\storage\AdapterInterface;

use \lib\entity\EntityInterface;
use \lib\entity\CollectionInterface;

/**
 * Abstract storage prototype
 *
 * @throws \DomainException|\InvalidArgumentException|\OutOfRangeException|\OverflowException
 * @package Moss Storage
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
abstract class Storage implements StorageInterface {

	/**
	 * @var \lib\storage\AdapterInterface
	 */
	protected $Adapter;

	/**
	 * @var \lib\entity\EntityInterface
	 */
	protected $Entity;
	protected $resultType;

	protected $container;

	protected $entity;
	protected $collection;

	protected $primary;
	protected $index;
	protected $fields;
	protected $mapping;

	protected $Relations = array();

	protected $keyField;
	protected $mapped;
	protected $operation;
	protected $value;
	protected $condition;
	protected $order;
	protected $limit;
	protected $offset;

	/**
	 * Defines storage container name
	 * e.g. database table or xml namespace
	 *
	 * @param $container container name
	 * @return StorageInterface
	 */
	public function container($container) {
		$this->container = $container;
		return $this;
	}

	/**
	 * Defines entity class that will be returned
	 *
	 * @param $className class of retrieved entities
	 * @return StorageInterface
	 */
	public function entity($className) {
		$this->entity = $className;
		return $this;
	}

	/**
	 * Creates associated entity instance
	 *
	 * @param array $data
	 * @return \lib\entity\EntityInterface
	 */
	public function createEntity(Array $data = array()) {
		$className = $this->entity;
		return new $className($data);
	}

	/**
	 * Defines collection class that will be returned
	 *
	 * @param $className class of retrieved collections
	 * @return StorageInterface
	 */
	public function collection($className) {
		$this->collection = $className;
		return $this;
	}

	/**
	 * Creates associated collection instance
	 *
	 * @param array $data data passed to collection constructor
	 * @return \lib\entity\CollectionInterface
	 */
	public function createCollection(Array $data = array()) {
		$className = $this->collection;
		return new $className($data);
	}

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
	public function field($field, $required = true, $type = 's', $index = false, $primaryIndex = false, $mapping = null) {
		$this->fields[$field] = array(
			'field' => $field,
			'required' => (bool) $required,
			'type' => $type,
		);

		$this->mapping[$field] = $mapping ? $mapping : $field;

		if($index) {
			$this->index[$field] = $field;
		}

		if($primaryIndex) {
			$this->primary[$field] = $field;
		}

		return $this;
	}

	/**
	 * Defines count operation on storage container
	 * Execution will return affected row count
	 *
	 * @return StorageInterface
	 */
	public function count() {
		$this->resultType = 'count';
		$this->operation = 'count';

		return $this;
	}

	/**
	 * Defines read operation on storage container
	 * Execution will return collection of entities
	 *
	 * @return StorageInterface
	 */
	public function read() {
		$this->resultType = 'read';
		$this->operation = 'select';

		return $this;
	}

	/**
	 * Defines read one operation on storage container
	 * Execution will return one entity
	 *
	 * @return StorageInterface
	 */
	public function readOne() {
		$this->read();

		$this->resultType = 'readOne';
		$this->limit(1);

		return $this;
	}

	/**
	 * Defines write operation on storage container
	 * Execution will decide if update or insert operation should be made
	 * Execution will return written entity
	 *
	 * @param \lib\entity\EntityInterface $Entity entity that will be written
	 * @return StorageInterface
	 */
	public function write(EntityInterface $Entity) {
		$indexCounter = 0;
		foreach($Entity->retrieve() as $field => $value) {
			if(!in_array($field, $this->primary)) {
				continue;
			}

			$this->condition($field, (string) $value);

			$indexCounter++;
		}

		$count = $this->count()->execute();

		if(!$indexCounter || $count === 0) {
			$this->reset(false)->insert($Entity);
		}
		elseif($count === 1) {
			$this->reset(false)->update($Entity);
		}
		else {
			throw new \OverflowException('Entity can not be written - not unique');
		}

		return $this;
	}

	/**
	 * Defines insert operation on storage container
	 * Execution will return inserted entity
	 *
	 * @param \lib\entity\EntityInterface $Entity entity that will be inserted
	 * @return StorageInterface
	 */
	public function insert(EntityInterface $Entity) {
		$this->Entity = &$Entity;
		$this->resultType = 'insert';
		$this->operation = 'insert';

		$this->Adapter->insert();

		foreach($this->Entity->retrieve() as $field => $value) {
			if($value !== false && empty($value)) {
				continue;
			}
			
			$this->value[] = array(
				'field' => $this->mapping[$field],
				'value' => $value,
				'type' => $this->fields[$field]['type']
			);
		}

		return $this;
	}

	/**
	 * Defines update operation on storage container
	 * Execution will return updated entity
	 *
	 * @param \lib\entity\EntityInterface $Entity entity that will be updated
	 * @return StorageInterface
	 */
	public function update(EntityInterface $Entity) {
		$this->Entity = &$Entity;
		$this->resultType = 'update';
		$this->operation = 'update';

		$this->Adapter->update()->limit(1);

		foreach($this->Entity->retrieve() as $field => $value) {
			if(count($this->index) !== count($this->fields) && in_array($field, $this->primary)) {
				continue;
			}

			$this->value[] = array(
				'field' => $this->mapping[$field],
				'value' => $value,
				'type' => $this->fields[$field]['type']
			);
		}


		foreach($this->Entity->retrieve() as $field => $value) {
			if(($value !== false && empty($value)) || !in_array($field, $this->primary)) {
				continue;
			}

			$this->condition($field, (string) $value);
		}

		return $this;
	}

	/**
	 * Defines delete operation on storage container
	 * Execution will return boolean result
	 *
	 * @param \lib\entity\EntityInterface $Entity entity that will be deleted
	 * @return StorageInterface
	 */
	public function delete(EntityInterface $Entity) {
		$this->resultType = 'delete';
		$this->operation = 'delete';
		
		$this->Entity = &$Entity;
		$this->Adapter->delete()->limit(1);

		foreach($this->Entity->retrieve() as $field => $value) {
			if(empty($value)) {
				continue;
			}
			
			$this->condition($field, (string) $value);
		}
		
		return $this;
	}

	/**
	 * Defines fields used in read operations
	 * Each argument represents single field name
	 *
	 * @return StorageInterface
	 */
	public function fields() {
		if(is_array(func_get_arg(0))) {
			$arg = func_get_arg(0);
		}
		else {
			$arg = func_get_args();
		}
		
		foreach($arg as $field) {
			if(!isset($this->mapping[$field])) {
				throw new \InvalidArgumentException(sprintf('Field %s not found', $field));
			}

			$this->mapped[$field] = $this->mapping[$field];
		}

		return $this;
	}

	/**
	 * Defines condition for count and read operations
	 *
	 * @param string $field field name used in condition
	 * @param mixed $value value used in condition
	 * @param string $comparisonOperator comparison operator (allowed operators: ==, !=, <>, <, <=, >, =>)
	 * @param string $logicalOperator logical operator used between conditions (allowed operators: &&, ||, XOR)
	 * @return StorageInterface
	 */
	public function condition($field, $value, $comparisonOperator = '==', $logicalOperator = '&&') {
		if(!isset($this->mapping[$field])) {
			throw new \InvalidArgumentException(sprintf('Field %s not found', $field));
		}
		
		$this->condition[] = array(
			'field' => $this->mapping[$field],
			'comparison' => $comparisonOperator,
			'value' => $value,
			'logical' => $logicalOperator,
			'type' => $this->fields[$field]['type']
		);

		return $this;
	}

	/**
	 * Defines order for read operations
	 *
	 * @param string $field field that should be ordered
	 * @param string $order order type
	 * @return StorageInterface
	 */
	public function order($field, $order = 'asc') {
		if(!isset($this->mapping[$field])) {
			throw new \InvalidArgumentException(sprintf('Field %s not found', $field));
		}

		$this->order[] = array(
			'field' => $this->mapping[$field],
			'order' => $order
		);
		
		return $this;
	}

	/**
	 * Defines limit for read operations
	 *
	 * @param int $limit
	 * @param null|int $offset
	 * @return StorageInterface
	 */
	public function limit($limit, $offset = null) {
		$this->limit = $limit;
		$this->offset = $offset;

		return $this;
	}

	/**
	 * Defines field used in collection as element keys
	 *
	 * @param string $field field name
	 * @return StorageInterface
	 */
	public function keyField($field) {
		if(!isset($this->mapping[$field])) {
			throw new \InvalidArgumentException(sprintf('Field %s not found', $field));
		}

		$this->keyField = $field;

		return $this;
	}

	/**
	 * Retrieves field name used in collection as element keys
	 *
	 * @abstract
	 * @return null|string
	 */
	public function hasKeyField() {
		return $this->keyField;
	}

	/**
	 * Defines relation instance for read, write, delete operations
	 *
	 * @param RelationInterface $Relation relation name that instance will be associated
	 * @return StorageInterface
	 */
	public function relation(RelationInterface $Relation) {
		$this->Relations[$Relation->identify()] = &$Relation;

		return $this;
	}

	/**
	 * If storage has relation
	 * If identifier passed, checks if relation has specified relation, otherwise if any relation
	 *
	 * @param string $identifier
	 * @return bool
	 */
	public function hasRelation($identifier = null) {
		if($identifier) {
			return isset($this->Relations[$identifier]);
		}

		return !empty($this->Relations);
	}

	/**
	 * Executes defined operation
	 *
	 * @return mixed
	 */
	public function execute() {
		switch($this->resultType) {
			case 'count':
				$this->assignDataToAdapter();
				$result = $this->Adapter->execute($this->container, $this->entity, $this->collection);

				$result = (int) $result;
			break;
			case 'read':
				$this->assignDataToAdapter();
				$result = $this->Adapter->execute($this->container, $this->entity, $this->collection);

				if(!$result instanceof $this->collection) {
					throw new \DomainException('Invalid result type');
				}

				foreach($this->Relations as $Relation) {
					$Relation->read($result);
				}
			break;
			case 'readOne':
				$this->assignDataToAdapter();
				$result = $this->Adapter->execute($this->container, $this->entity, $this->collection);
					
				if(!$result instanceof $this->collection) {
					throw new \DomainException('Invalid result type');
				}

				if(!isset($result[0])) {
					throw new \OutOfRangeException('Result out of range or does not exists');
				}

				foreach($this->Relations as $Relation) {
					$Relation->read($result);
				}

				$result = $result[0];
			break;
			case 'insert':
				if(!$this->Entity) {
					throw new \DomainException('Expected Entity relation not found');
				}

				$this->assignDataToAdapter();
				$result = $this->Adapter->execute($this->container, $this->entity, $this->collection);

				$this->Entity->identify($result);

				foreach($this->Relations as &$Relation) {
					$Relation->write($this->Entity);
				}

				$result = $this->Entity;

				break;
			case 'update':
				if(!$this->Entity) {
					throw new \DomainException('Expected Entity relation not found');
				}

				$this->assignDataToAdapter();
				$this->Adapter->execute($this->container, $this->entity, $this->collection);

				foreach($this->Relations as &$Relation) {
					$Relation->write($this->Entity);
				}

				$result = $this->Entity;
			break;
			case 'delete':
				foreach($this->Relations as $Relation) {
					$Relation->delete($this->Entity);
				}

				$this->assignDataToAdapter();
				$result = (bool) $this->Adapter->execute($this->container, $this->entity, $this->collection);
			break;
			default:
				throw new \DomainException('Invalid result type');
		}

		return $result;
	}

	/**
	 * Resets storage instance data (operations, conditions, relations)
	 *
	 * @param bool $relations if true, all relations will be removed
	 * @return StorageInterface
	 */
	public function reset($relations = true) {
		unset($this->Entity);
		$this->Entity = null;
		$this->resultType = null;
		$this->Adapter->reset();

		if($relations) {
			$this->Relations = array();
		}

		$this->keyField = null;
		$this->mapped = array();
		$this->operation = null;
		$this->value = array();
		$this->condition = array();
		$this->order = array();
		$this->limit = null;
		$this->offset = null;

		return $this;
	}

	/**
	 * Assigns data from storage instance to adapter instance
	 *
	 * @return void
	 */
	protected function assignDataToAdapter() {
		call_user_func(array($this->Adapter, $this->operation));

		$this->Adapter->keyField($this->keyField);

		$fields = !empty($this->mapped) ? $this->mapped : $this->mapping;
		foreach($fields as $field => $mapping) {
			$this->Adapter->field($field, $mapping);
		}

		if(!empty($this->value)) {
			foreach($this->value as $node) {
				$this->Adapter->value($node['field'], $node['value'], $node['type']);
			}
		}

		if(!empty($this->condition)) {
			foreach($this->condition as $node) {
				$this->Adapter->condition($node['field'], $node['value'], $node['comparison'], $node['logical'], $node['type']);
			}
		}

		if(!empty($this->order)) {
			foreach($this->order as $node) {
				$this->Adapter->order($node['field'], $node['order']);
			}
		}

		if(!empty($this->limit)) {
			$this->Adapter->limit($this->limit, $this->offset);
		}
	}
}
