<?php
namespace lib\model;

/**
 * Conditional interface
 * Models implementing this interface can use conditions for returned data
 *
 * @package Moss Model
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ConditionalInterface {

	/**
	 * Adds condition to result retrieval
	 *
	 * @abstract
	 * @param string $field field name used in condition
	 * @param mixed $value value used in condition
	 * @param string $comparisonOperator comparison operator (allowed operators: ==, !=, <>, <, <=, >, =>)
	 * @param string $logicalOperator logical operator used between conditions (allowed operators: &&, ||, XOR)
	 * @return \lib\model\ModelInterface
	 */
	public function condition($field, $value, $comparisonOperator = '==', $logicalOperator = '&&');
}
