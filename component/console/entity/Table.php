<?php
namespace console\entity;

use \lib\entity\Entity;

/**
 * Generates entity and storage objects based on database structure and vice versa
 *
 * @throws \InvalidArgumentException|\RangeException|\RuntimeException
 *
 * @package Moss console component
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Table extends Entity {

	protected $Table;

	protected $Entity;
	protected $Storage;
	protected $Model;

	protected $prefix;
	protected $overwrite = true;

	/**
	 * Creates Entity instance
	 *
	 * @abstract
	 * @param array $iArr array containing entity data
	 */
	public function __construct(Array $iArr = array()) {
		parent::__construct($iArr);

		foreach(get_object_vars($this) as $key => $var) {
			if(!preg_match('/^Tables_in_.+$/i', $key)) {
				continue;
			}

			$this->identify($var);
			unset($this->$key);
		}
	}

	/**
	 * Identifies entity.
	 * If argument passed sets new identifier
	 *
	 * @param null|int|string $identifier entity identifier
	 * @return mixed
	 */
	public function identify($identifier = null) {
		if($identifier) {
			$this->Table = $identifier;
		}

		return $this->Table;
	}

	/**
	 * Returns entity name
	 *
	 * @return string
	 */
	public function identifyEntity() {
		return ucfirst(str_replace($this->prefix, null, $this->Table));
	}

	/**
	 * Returns storage name
	 *
	 * @return string
	 */
	public function identifyStorage() {
		return ucfirst(str_replace($this->prefix, null, $this->Table).'Storage');
	}

	/**
	 * Returns model name
	 *
	 * @return mixed
	 */
	public function identifyModel() {
		return ucfirst(str_replace($this->prefix, null, $this->Table.'Model'));
	}

	/**
	 * Changes table prefix
	 * Will be removed from table name
	 *
	 * @param null|string $prefix table prefix
	 * @return
	 */
	public function prefix($prefix = null) {
		if($prefix) {
			$this->prefix = $prefix.'_';
		}

		return $this->prefix;
	}

	/**
	 * Build Entity objects structure
	 *
	 * @throws \RangeException
	 * @param \lib\View $View view instance
	 * @param string $namespace objects namespace
	 * @return Table
	 */
	public function buildEntity(\lib\View $View, $namespace) {
		if(!isset($this->Field)) {
			throw new \RangeException(sprintf('No fields defined in table %s', $this->Table));
		}

		$this->Entity = $View
			->template('console:entity')

			->set('namespace', $namespace)
			->set('entity', $this->identifyEntity())
			->set('Fields', $this->Field)

			->render()
		;

		return $this;
	}

	/**
	 * Builds Storage objects structure
	 *
	 * @throws \InvalidArgumentException
	 * @param \lib\View $View view instance
	 * @param string $namespace objects namespace
	 * @return Table
	 */
	public function buildStorage(\lib\View $View, $namespace) {
		if(!isset($this->Field)) {
			throw new \RangeException(sprintf('No fields defined in table %s', $this->Table));
		}

		$this->Storage = $View
			->template('console:storage')

			->set('namespace', $namespace)
			->set('table', $this->Table)
			->set('entity', $this->identifyEntity())
			->set('Fields', $this->Field)

			->render()
		;

		return $this;
	}

	/**
	 * Builds Model objects structure
	 *
	 * @throws \InvalidArgumentException
	 * @param \lib\View $View view instance
	 * @param string $namespace objects namespace
	 * @return Table
	 */
	public function buildModel(\lib\View $View, $namespace) {
		if(!isset($this->Field)) {
			throw new \RangeException('No fields defined in table %s', $this->Table);
		}

		$this->Model = $View
			->template('console:model')

			->set('namespace', $namespace)
			->set('entity', $this->identifyEntity())
			->set('Fields', $this->Field)

			->render()
		;

		return $this;
	}
}
