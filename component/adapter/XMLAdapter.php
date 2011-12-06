<?php
namespace component\adapter;

use \lib\storage\AdapterInterface;
use \lib\component\SubjectPrototype;

use \lib\entity\EntityInterface;
use \lib\entity\CollectionInterface;

/**
 * XML data source adapter
 * Allow for access to data from xml
 *
 * @package Moss Adapter
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 *
 * @todo insert, update, write, delete
 */
class XMLAdapter extends SubjectPrototype implements AdapterInterface {

	protected $path;

	private $comparisonOperators = array(
		'==' => '==',
		'!=' => '!=',
		'<>' => '!=',
		'<' => '<',
		'>' => '>',
		'<=' => '<=',
		'>=' => '=>'
	);
	private $logicalOperators = array(
		'&&' => '&&',
		'||' => '||',
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
		$this->path = $path;
	}

	/**
	 * Defines count operation
	 *
	 * @return AdapterInterface
	 */
	public function count() {
		$this->query = 'count';
		$this->resultType = 'num_rows';
	}

	/**
	 * Defines read operation
	 *
	 * @return AdapterInterface
	 */
	public function select() {
		$this->query = 'select';
		$this->resultType = 'entity';
		return $this;
	}

	/**
	 * Defines insert operation
	 *
	 * @return AdapterInterface
	 */
	public function insert() {
		throw new \LogicException('Unsupported');
		/*
		$this->query = 'insert';
		$this->resultType = 'identifier';
		return $this;
		*/
	}

	/**
	 * Defines update operation
	 *
	 * @return AdapterInterface
	 */
	public function update() {
		throw new \LogicException('Unsupported');
		/*
		$this->query = 'update';
		$this->resultType = 'boolean';
		return $this;
		*/
	}

	/**
	 * Defines delete operation
	 *
	 * @return AdapterInterface
	 */
	public function delete() {
		$this->query = 'delete';
		$this->resultType = 'boolean';
		return $this;
	}

	/**
	 * Defines table list operation
	 *
	 * @return AdapterInterface
	 */
	public function tables() {
		throw new \LogicException('Unsupported');
	}

	/**
	 * Defines describe table operation
	 *
	 * @return AdapterInterface
	 */
	public function describe() {
		throw new \LogicException('Unsupported');
	}

	/**
	 * Defines field used in read operation
	 *
	 * @param string $field
	 * @param null|string $mapping
	 * @return AdapterInterface
	 */
	public function field($field, $mapping = null) {
		$this->field[$mapping ? $mapping : $field] = $field;
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
		$this->value[$field] = $this->prepareValue($value, $type);
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
				$val = sprintf(
					'$node[\'%s\'] %s %s',
					$field,
					$this->comparisonOperators[$comparisonOperator],
					$this->prepareValue($val, $type)
				);
				unset($val);
			}

			$this->condition[] = array(
				sprintf('(%s)', implode(sprintf(' %s ', $this->logicalOperators['||']), $value)),
				$this->logicalOperators[$logicalOperator]
			);
		}
		else {
			$this->condition[] = array(
				sprintf(
					'$node[\'%s\'] %s %s',
					$field,
					$this->comparisonOperators[$comparisonOperator],
					$this->prepareValue($value, $type)
				),
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
	 * @return \lib\entity\CollectionInterface|\lib\entity\EntityInterface|integer|boolean
	 */
	public function execute($container, $entity, $collection) {
		switch($this->query) {
			case 'count':
				return $this->executeCount($container);
				break;
			case 'select':
				return $this->executeRead($container, $entity, $collection);
				break;
			default:
				throw new \DomainException('Undefined or invalid operation type '.$this->query);
		}
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

		return $this;
	}

	/**
	 * Prepares value for statement
	 *
	 * @throws \DomainException
	 * @param int|float|double|string $value value
	 * @param string $type value type
	 * @return string
	 */
	protected function prepareValue($value, $type = 's') {
		if(stripos('idsb', $type) === false) {
			throw new \DomainException('Invalid value type '.$type);
		}

		switch($type) {
			case 'i':
			case 'd':
				$value = str_replace(' ', null, $value);
				$value = str_replace(',', '.', $value);
				$value = strpos($value, '.') === false ? (int) $value : (double) $value;
				break;
			case 's':
			case 'b':
				$value = '\''.htmlentities($value).'\'';
				break;
		}

		return $value;
	}

	/**
	 * Reads data from associated XML
	 *
	 * @param string $container container name
	 * @return \SimpleXMLElement[]
	 */
	protected function readXML($container) {
		$xml = new \SimpleXMLElement(file_get_contents($this->path));

		$result = $xml->xpath(str_replace('_', '/', $container));

		return $result;
	}

	/**
	 * Executes count operation
	 *
	 * @param string $container container name
	 * @return int
	 */
	protected function executeCount($container) {
		$xml = $this->readXML($container);

		$checkConditionsFunc = $this->buildConditions();

		$i = -1;
		$count = 0;
		foreach($xml as $node) {
			$node = (array) $node;

			if(!$checkConditionsFunc($node)) {
				continue;
			}

			$i++;

			if($this->offset && $this->offset > $i) {
				continue;
			}

			if($this->limit && $this->offset + $this->limit <= $i) {
				break;
			}

			$count++;
		}

		return $count;
	}

	/**
	 * Executes read operation
	 *
	 * @param string $container container name
	 * @param string $entity entity class
	 * @param string $collection collection class
	 * @return array
	 */
	protected function executeRead($container, $entity, $collection) {
		$xml = $this->readXML($container);

		$checkConditionsFunc = $this->buildConditions();

		$i = -1;
		$output = new $collection;
		foreach($xml as $node) {

			$node = (array) $node;

			if(!$checkConditionsFunc($node)) {
				continue;
			}

			$i++;

			if($this->offset && $this->offset > $i) {
				continue;
			}

			if($this->limit && $this->offset + $this->limit <= $i) {
				break;
			}

			$obj = new $entity($this->buildFields($node));
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
	 * Translates SimpleXMLNode to array
	 *
	 * @param SimpleXMLNode|Array $node
	 * @return array
	 */
	protected function buildFields($node) {
		$output = array();
		foreach((array) $node as $field => $value) {
			if(!isset($this->field[$field])) {
				continue;
			}

			$output[$this->field[$field]] = $value;
		}

		return $output;
	}

	/**
	 * Builds values list for statement
	 *
	 * @return null|string
	 */
	protected function buildConditions() {
		if(empty($this->condition)) {
			return create_function('$node', 'return true;');
		}

		$arguments = '$node';
		$code = null;
		foreach($this->condition as $condition) {
			$code .= (!empty($code) ? ' '.$condition[1].' ' : null).$condition[0];
		}

		$code = 'return ('.$code.');';

		$this->notify($code);

		return create_function($arguments, $code);
	}
}