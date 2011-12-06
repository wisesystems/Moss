<?php
namespace component\adapter;

use \lib\storage\AdapterInterface;
use \lib\component\SubjectPrototype;

use \lib\entity\EntityInterface;
use \lib\entity\CollectionInterface;

/**
 * MySQLi database adapter
 * Allow for data access trough MySQLi
 *
 * @throws \DomainException|\InvalidArgumentException|\LengthException|\OutOfRangeException
 * @package Moss Adapter
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class MySQLiAdapter extends SubjectPrototype implements AdapterInterface {

	private $MySQLi;
	private $prefix;

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

	protected $query;
	protected $keyField;
	protected $field;
	protected $value;
	protected $condition;
	protected $order;
	protected $limit;
	protected $offset;

	/**
	 * Creates adapter instance
	 *
	 * @param string $path path to configuration or to storage file
	 */
	public function __construct($path) {
		if(empty($path)) {
			throw new \LengthException('File path not set');
		}

		if(!is_file($path)) {
			throw new \InvalidArgumentException('XML file not found!');
		}

		$xml = new \SimpleXMLElement(file_get_contents($path));

		if(!isset($xml->driver, $xml->hostname, $xml->port, $xml->database, $xml->username, $xml->password)) {
			throw new \OutOfRangeException(sprintf('Incorrect file format (missing nodes)!', $path));
		}

		if(!$this->MySQLi = new \mysqli((string) $xml->hostname, (string) $xml->username, (string) $xml->password, (string) $xml->database, (int) $xml->port)) {
			throw new \InvalidArgumentException('Database connection error!');
		}

		if(isset($xml->prefix) && !empty($xml->prefix)) {
			$this->prefix = $xml->prefix.'_';
		}

		$this->MySQLi->query('SET NAMES '.$xml->charset);
	}

	/**
	 * Defines count operation
	 *
	 * @return AdapterInterface
	 */
	public function count() {
		$this->resultType = 'num_rows';
		$this->query = 'SELECT {fields} FROM {container} WHERE {conditions}';
	}

	/**
	 * Defines read operation
	 *
	 * @return AdapterInterface
	 */
	public function select() {
		$this->resultType = 'entity';

		$this->query = 'SELECT {fields} FROM {container} WHERE {conditions} ORDER {order} LIMIT {limit}';
		return $this;
	}

	/**
	 * Defines insert operation
	 *
	 * @return AdapterInterface
	 */
	public function insert() {
		$this->resultType = 'identifier';
		$this->query = 'INSERT INTO {container} SET {values}';
		return $this;
	}

	/**
	 * Defines update operation
	 *
	 * @return AdapterInterface
	 */
	public function update() {
		$this->resultType = 'boolean';
		$this->query = 'UPDATE {container} SET {values} WHERE {conditions} LIMIT {limit}';
		return $this;
	}

	/**
	 * Defines delete operation
	 *
	 * @return AdapterInterface
	 */
	public function delete() {
		$this->resultType = 'boolean';
		$this->query = 'DELETE FROM {container} WHERE {conditions} LIMIT {limit}';
		return $this;
	}

	/**
	 * Defines table list operation
	 *
	 * @return AdapterInterface
	 */
	public function tables() {
		$this->resultType = 'entity';
		$this->query = 'SHOW TABLES';
		return $this;
	}

	/**
	 * Defines describe table operation
	 *
	 * @return AdapterInterface
	 */
	public function describe() {
		$this->resultType = 'entity';
		$this->query = 'DESCRIBE {container}';
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
		if($mapping && $mapping != $field) {
			$this->field[] = sprintf('`%s` as `%s`', $mapping, $field);
		}
		else {
			$this->field[] = sprintf('`%s`', $field);
		}
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
		$this->value[] = $this->prepareValue('value', $field, '=', $value, $type);
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

		if(is_array($value)) {
			foreach($value as &$val) {
				$val = $this->prepareValue('condition', $field, $this->comparisonOperators[$comparisonOperator], $val, $type);
				unset($val);
			}

			$this->condition[] = array(
				sprintf('(%s)', implode(sprintf(' %s ', $this->logicalOperators['||']), $value)),
				$this->logicalOperators[$logicalOperator]
			);
		}
		else {
			$this->condition[] = array(
				$this->prepareValue('condition', $field, $this->comparisonOperators[$comparisonOperator], $value, $type),
				$this->logicalOperators[$logicalOperator]
			);
		}
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
		$this->order[] = sprintf('`%s` %s', $field, $order);
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
		$queryString = $this->buildStatement($container);

		$this->notify($queryString);

		$result = $this->MySQLi->query($queryString);

		if(!$result) {
			throw new \InvalidArgumentException(sprintf("Database error!\n%s\n%s", $this->MySQLi->error, $queryString));
		}
		
		switch($this->resultType) {
			case 'num_rows':
				$output = $result->num_rows;
				break;
			case 'entity':
				$output = $this->buildEntityResult($result, $entity, $collection);
				break;
			case 'identifier':
				$output = $this->MySQLi->insert_id;
				break;
			case 'boolean':
				$output = (bool) $result;
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

		$this->query = null;
		$this->keyField = null;
		$this->field = null;
		$this->value = null;
		$this->condition = null;
		$this->order = null;
		$this->limit = null;
		$this->offset = null;
	}

	/**
	 * Builds result collection from statement
	 *
	 * @param \MySQLi_Result $statement
	 * @param string $entity entity class
	 * @param string $collection collection class
	 * @return \lib\entity\CollectionInterface
	 */
	protected function buildEntityResult(\MySQLi_Result $statement, $entity, $collection) {
		$output = new $collection;
		while($obj = $statement->fetch_object($entity)) {
			if($this->keyField) {
				$output[$obj->get($this->keyField)] = $obj;
			}
			else {
				$output[] = $obj;
			}
		}

		return $output;
	}

	/**
	 * Builds query statement from operation pattern
	 *
	 * @param string $container storage container
	 * @return string
	 */
	protected function buildStatement($container) {
		preg_match_all('#[^ ]+ ({([^}]+)})#', $this->query, $matches);

		$count = count($matches[0]);
		for($i = 0; $i < $count; $i++) {
			switch($matches[2][$i]) {
				case 'container':
					$match = str_replace('{prefix}', $this->prefix, $container);
					break;
				case 'fields':
					$match = $this->buildQueryFields();
					break;
				case 'values':
					$match = $this->buildQueryValues();
					break;
				case 'conditions':
					$match = $this->buildQueryConditions();
					break;
				case 'order':
					$match = $this->buildQueryOrder();
					break;
				case 'limit':
					$match = $this->buildQueryLimit();
					break;
				default:
					$match = null;
			}

			if(!$match) {
				$matches[1][$i] = $matches[0][$i];
			}

			$matches[2][$i] = $match;
		}

		return str_replace($matches[1], $matches[2], $this->query);
	}

	/**
	 * Prepares value for statement
	 *
	 * @throws \DomainException
	 * @param string $operation operation name
	 * @param string $field field name
	 * @param string $operator comparison operator
	 * @param int|float|double|string $value value
	 * @param string $type value type
	 * @return string
	 */
	protected function prepareValue($operation, $field, $operator, $value, $type = 's') {
		if(stripos('idsb', $type) === false) {
			throw new \DomainException('Invalid value type');
		}

		if($value !== false && !strlen($value)) {
			$value = sprintf('`%s` %s NULL', $field, $operation == 'value' ? $operator : 'IS');
		}
		else {
			switch($type) {
				case 'i':
					$value = (int) $value;
					break;
				case 'd':
					$value = str_replace(' ', null, $value);
					$value = str_replace(',', '.', $value);
					$value = strpos($value, '.') === false ? (int) $value : (double) $value;
					$value = (float) $value;
					break;
				case 's':
					$value = '\''.$this->MySQLi->real_escape_string($value).'\'';
					break;
				case 'b':
					$value = '\''.$this->MySQLi->real_escape_string($value).'\'';
					break;
			}

			$value = sprintf('`%s` %s %s', $field, $operator, $value);
		}

		return $value;
	}

	/**
	 * Builds field list for statement
	 *
	 * @return string
	 */
	protected function buildQueryFields() {
		return empty($this->field) ? '*' : implode(', ', $this->field);
	}

	/**
	 * Builds values list for statement
	 *
	 * @return null|string
	 */
	protected function buildQueryValues() {
		return empty($this->value) ? null : implode(', ', $this->value);
	}

	/**
	 * Builds conditions list for statement
	 *
	 * @return null|string
	 */
	protected function buildQueryConditions() {
		if(empty($this->condition)) {
			return null;
		}

		$conditionString = null;
		foreach($this->condition as $condition) {
			$conditionString .= (!empty($conditionString) ? ' '.$condition[1].' ' : null).$condition[0];
		}

		return $conditionString;
	}

	/**
	 * Builds order part for statement
	 *
	 * @return null|string
	 */
	protected function buildQueryOrder() {
		if(empty($this->order)) {
			return null;
		}
		
		return 'BY '.implode(', ', (array) $this->order);
	}

	/**
	 * Builds limit part for statement
	 *
	 * @return null|string
	 */
	protected function buildQueryLimit() {
		if(!$this->limit) {
			return null;
		}

		return ($this->offset ? $this->offset.',' : null).' '.(int) $this->limit;
	}
}
