<?php
namespace console\entity;

use \lib\entity\Entity;

/**
 * Represents table column
 *
 * @package Moss console component
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Field extends Entity {

	protected $Field;
	protected $Type;
	protected $Null;
	protected $Key;
	protected $Default;
	protected $Extra;

	/**
	 * Identifies entity.
	 * If argument passed sets new identifier
	 *
	 * @param null|int|string $identifier entity identifier
	 * @return mixed
	 */
	public function identify($identifier = null) {
		if($identifier) {
			$this->Field = $identifier;
		}

		return $this->Field;
	}

	/**
	 * Returns true if column is required (not null)
	 *
	 * @return bool
	 */
	public function isRequired() {
		return $this->Null != 'YES';
	}

	/**
	 * Returns true if column is an index
	 *
	 * @return bool
	 */
	public function isIndex() {
		return $this->Key != '';
	}

	/**
	 * Returns true if column is primary index
	 *
	 * @return bool
	 */
	public function isPrimary() {
		return $this->Key == 'PRI';
	}

	/**
	 * Returns char representing columns data type
	 *
	 * @return string
	 */
	public function getTypeChar() {
		$type = strtolower($this->get('Type'));
		$type = substr($type, 0, strpos($type, '('));

		switch($type) {
			case 'tynyint':
			case 'smallint':
			case 'mediumint':
			case 'int':
			case 'bigint':
			case 'bit':
			case 'bool':
				return 'i';
			break;

			case 'decimal':
			case 'float':
			case 'double':
			case 'real':
				return 'd';

			default:
				return 's';
		}
	}
}
