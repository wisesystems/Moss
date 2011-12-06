<?php
namespace component\adapter;

use \lib\storage\AdapterInterface;
use \lib\component\SubjectPrototype;

use \lib\entity\EntityInterface;
use \lib\entity\CollectionInterface;

/**
 * Dummy adapter
  *
 * @throws \DomainException|\InvalidArgumentException|\LengthException|\OutOfRangeException
 * @package Moss Adapter
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class DummyAdapter extends SubjectPrototype implements AdapterInterface {

	protected $prefix;

	private $comparisonOperators = array(
		'==' => '=',
		'!=' => '!=',
		'<>' => '!=',
		'<' => '<',
		'>' => '>',
		'<=' => '<=',
		'>=' => '>='
	);
	private $logicalOperators = array(
		'&&' => 'AND',
		'||' => 'OR',
		'XOR' => 'XOR',
	);

	protected $resultType;

	protected $keyField;
	protected $field;
	protected $mapping;
	protected $order;
	protected $limit;
	protected $offset;

	/**
	 * Creates adapter instance
	 *
	 * @param string $path path to configuration or to storage file
	 */
	public function __construct($path) {
	}

	/**
	 * Defines count operation
	 *
	 * @return AdapterInterface
	 */
	public function count() {
		$this->resultType = 'num_rows';
		return $this;
	}

	/**
	 * Defines read operation
	 *
	 * @return AdapterInterface
	 */
	public function select() {
		$this->resultType = 'entity';
		return $this;
	}

	/**
	 * Defines insert operation
	 *
	 * @return AdapterInterface
	 */
	public function insert() {
		$this->resultType = 'identifier';
		return $this;
	}

	/**
	 * Defines update operation
	 *
	 * @return AdapterInterface
	 */
	public function update() {
		$this->resultType = 'boolean';
		return $this;
	}

	/**
	 * Defines delete operation
	 *
	 * @return AdapterInterface
	 */
	public function delete() {
		$this->resultType = 'boolean';
		return $this;
	}

	/**
	 * Defines table list operation
	 *
	 * @return AdapterInterface
	 */
	public function tables() {
		$this->resultType = 'entity';
		return $this;
	}

	/**
	 * Defines describe table operation
	 *
	 * @return AdapterInterface
	 */
	public function describe() {
		$this->resultType = 'entity';
		return $this;
	}

	/**
	 * Defines field used in read operation
	 *
	 * @param string $field
	 * @param null|string $mapping
	 * @return AdapterInterface
	 */
	public function field($field, $mapping = null) {
		$this->mapping[$mapping ? $mapping : $field] = $field;
		$this->field[$field] = $field;
		return $this;
	}

	/**
	 * Defines field value used in insert and update operation
	 *
	 * @param string $field field name
	 * @param string $value field value
	 * @param string $type value type (allowed types: i - integer, s - string, d - double/decimal/float, b - binary)
	 * @return AdapterInterface
	 */
	public function value($field, $value, $type = 's') {
		$this->field[$field] = $value;

		return $this;
	}

	/**
	 * Defines condition used in count and read operations
	 *
	 * @param string $field field name used in condition
	 * @param mixed $value value used in condition
	 * @param string $comparisonOperator comparison operator (allowed operators: ==, !=, <>, <, <=, >, =>)
	 * @param string $logicalOperator logical operator used between conditions (allowed operators: &&, ||, XOR)
	 * @param string $type value type (allowed types: i - integer, s - string, d - double/decimal/float, b - binary)
	 * @return AdapterInterface
	 */
	public function condition($field, $value, $comparisonOperator = '==', $logicalOperator = '&&', $type = 's') {
		if(!isset($this->comparisonOperators[strtoupper($comparisonOperator)])) {
			throw new \DomainException('Invalid comparison operator');
		}

		if(!isset($this->logicalOperators[strtoupper($logicalOperator)])) {
			throw new \DomainException('Invalid logic operator');
		}

		$this->field[$this->mapping[$field]] = $value;

		return $this;
	}

	/**
	 * Defines order in read operations
	 *
	 * @param string $field field that should be ordered
	 * @param string $order order type
	 * @return AdapterInterface
	 */
	public function order($field, $order = 'asc') {
		$this->order[$this->mapping[$field]] = $order;

		return $this;
	}

	/**
	 * Defines limit for read operations
	 *
	 * @param int $limit
	 * @param null|int $offset
	 * @return AdapterInterface
	 */
	public function limit($limit, $offset = null) {
		$this->limit = (int) $limit;
		$this->offset = $offset ? (int) $offset : null;
		
		return $this;
	}

	/**
	 * Defines field used in collection as element keys
	 *
	 * @param string $field field name
	 * @return StorageInterface
	 */
	public function keyField($field) {
		if($field) {
			$this->keyField = $field;
		}

		return $this;
	}

	/**
	 * Executes defined operation
	 *
	 * @param string $container storage container
	 * @param string $entity entity class
	 * @param string $collection entity class
	 * @return \lib\entity\CollectionInterface|\lib\entity\EntityInferace|integer|boolean
	 */
	public function execute($container, $entity, $collection) {
		switch($this->resultType) {
			case 'num_rows':
				$output = $this->dummyNumRows();
				break;
			case 'entity':
				$output = $this->dummyEntity($entity, $collection);
				break;
			case 'identifier':
				$output = $this->dummyIdentifier();
				break;
			case 'boolean':
				$output = $this->dummyBoolean();
				break;
			default:
				throw new \DomainException('Undefined or invalid result type');
		}

		$this->reset();

		return $output;
	}

	/**
	 * Resets adapter instance data (operations, conditions, relations)
	 *
	 * @return AdapterInterface
	 */
	public function reset() {
		$this->resultType = null;

		$this->keyField = null;
		$this->field = null;
		$this->mapping = null;
		$this->order = null;
		$this->limit = null;
		$this->offset = null;
	}

	/**
	 * Return number of rows
	 * If limit is set - then double limit returned otherwise random number
	 *
	 * @return int
	 */
	protected function dummyNumRows() {
		return $this->limit ? $this->limit * 2 : rand(1, 100);
	}

	/**
	 * Returns dummy entity collection
	 *
	 * @param string $entity
	 * @param string $collection
	 * @return CollectionInterface
	 */
	protected function dummyEntity($entity, $collection) {
		$Collection = new $collection;

		$limit = $this->limit ? $this->limit : 3;

		for($i = 0; $i < $limit; $i++) {
			$Entity = new $entity($this->dummyValue($this->field, $i+1));

			$Collection[$this->keyField ? $Entity->get($this->keyField) : $i] = $Entity;
		}

		return $Collection;
	}

	/**
	 * Creates dummy value for entity
	 *
	 * @param array $fields
	 * @param int $offset
	 * @return int|string
	 */
	protected function dummyValue($fields, $offset = 0) {
		foreach($fields as $field => &$value) {
			if($field == 'id' || strpos($field, '_id') !== false || strpos($field, '_id') !== false) {
				$value = $offset;
			}
			elseif(in_array($field, array('cover', 'file'))) {
				$value = null;
			}
			elseif($field == 'language') {
				$value = 'pl';
			}
		}

		return $fields;
	}

	/**
	 * Returns random identifier
	 *
	 * @return int
	 */
	protected function dummyIdentifier() {
		return rand(1, 100);
	}

	/**
	 * Returns true for boolean operations
	 *
	 * @return bool
	 */
	protected function dummyBoolean() {
		return true;
	}
}
