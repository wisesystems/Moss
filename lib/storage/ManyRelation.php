<?php
namespace lib\storage;

use \lib\storage\RelationInterface;
use \lib\storage\RelativeInterface;
use \lib\entity\EntityInterface;
use \lib\entity\CollectionInterface;

/**
 * One to Many relation representation
 *
 * @package Moss Storage
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class ManyRelation implements RelationInterface {

	/**
	 * @var StorageInterface
	 */
	protected $Storage;

	protected $container;

	protected $key = array();
	protected $value = array();

	/**
	 * Constructor
	 * Creates relation instance
	 *
	 * @param StorageInterface $Storage
	 * @param string $container
	 * @param array $local
	 * @param array $foreign
	 * @param array $value
	 */
	public function __construct(StorageInterface $Storage, $container, Array $local = array(), Array $foreign = array(), Array $value = array()) {
		$this->Storage = &$Storage;
		$this->container($container);

		foreach($foreign as $key => $field) {
			$this->key(
				isset($local[$key]) ? $local[$key] : null,
				$foreign[$key],
				isset($value[$field]) ? $value[$field] : null
			);
		}
	}

	/**
	 * Defines container (entity parameter) name in witch relation data will be put
	 * In case write or delete operations - container defines container where relation data are available
	 *
	 * @param string $container container name
	 * @return OneToManyRelation
	 */
	public function container($container) {
		$this->container = $container;
		return $this;
	}

	/**
	 * Defines local and foreign key
	 * In addition, lets to set value when relation depends on other than entity data (e.g. entity class)
	 *
	 * @param null|string $local
	 * @param null|string  $foreign
	 * @param null|string $value values for foreign keys if relation depends on other than entity data (e.g. entity class)
	 * @return OneToManyRelation
	 */
	public function key($local = null, $foreign = null, $value = null) {
		if($local && $foreign) {
			$this->key[$local] = $foreign;
		}

		if($foreign && $value) {
			$this->value[$foreign] = $value;
		}

		return $this;
	}

	/**
	 * Identifies relation
	 *
	 * @return string
	 */
	public function identify() {
		return $this->container;
	}

	/**
	 * Resets relation storage
	 *
	 * @return RelationInterface
	 */
	public function reset() {
		$this->Storage->reset();

		return $this;
	}


	/**
	 * Executes read for relation
	 *
	 * @param \lib\entity\CollectionInterface $result
	 * @return void
	 */
	public function read(CollectionInterface $result) {
		if(!count($result)) {
			return;
		}

		$rArr = array();

		foreach($result as $key => $row) {
			foreach($this->key as $local => $foreign) {
				$rArr[$local][$row->get($local)][] = &$result[$key];
			}
		}

		foreach($this->value as $field => $value) {
			$this->Storage->condition($field, $value);
		}

		foreach($this->key as $local => $foreign) {
			$this->Storage->condition($foreign, array_keys($rArr[$local]));
		}

		$Collection = $this->Storage->read()->execute();
		$foreign = reset($this->key);
		$local = key($this->key);

		foreach($Collection as $row) {
			if(!isset($rArr[$local][$row->get($foreign)])) {
				continue;
			}

			foreach($rArr[$local][$row->get($foreign)] as &$Entity) {
				if(!isset($Entity->{$this->container})) {
					$Entity->{$this->container} = $this->Storage->createCollection();
				}

				if($this->Storage instanceof RelativeInterface && $this->Storage->hasRelation($this->identify())) {
					if(!isset($row->{$this->container})) {
						continue;
					}

					foreach($row->{$this->container} as $node) {
						if($keyField = $this->Storage->hasKeyField()) {
							$Entity->{$this->container}[$node->get($keyField)] = $node;
						}
						else {
							$Entity->{$this->container}[] = $node;
						}
					}
				}
				else {
					if($keyField = $this->Storage->hasKeyField()) {
						$Entity->{$this->container}[$row->get($keyField)] = $row;
					}
					else {
						$Entity->{$this->container}[] = $row;
					}
				}
				unset($Entity);
			}
		}
	}

	/**
	 * Executes write for relation
	 *
	 * @param \lib\entity\EntityInterface $Entity
	 * @return void
	 */
	public function write(EntityInterface $Entity) {

		try {
			$Entity->get($this->container);
		}
		catch(\DomainException $e) {
			return;
		}

		$relCollection = $Entity->get($this->container);

		if(count($relCollection)) {
			foreach($relCollection as $relEntity) {

				foreach($this->value as $field => $value) {
					$relEntity->set($field, $value);
				}

				foreach($this->key as $local => $foreign) {
					$relEntity->set($foreign, $Entity->get($local));
				}

				$this->Storage
					->reset()
					->write($relEntity)
					->execute()
				;
			}
			$Entity->set($this->container, $relCollection);
		}

		$this->Storage->reset()->read();
		foreach($this->value as $field => $value) {
			$this->Storage->condition($field, $value);
		}

		foreach($this->key as $local => $foreign) {
			$this->Storage->condition($foreign, $Entity->get($local));
		}

		$toDelete = $this->Storage->execute();

		if(isset($toDelete) && count($toDelete)) {
			foreach($toDelete as $key => $delEntity) {
				foreach($Entity->get($this->container) as $relEntity) {
					if($relEntity->retrieve() == $delEntity->retrieve()) {
						$toDelete[$key] = null;
					}
				}
			}
		}

		foreach($toDelete as $delEntity) {
			if(!$delEntity) {
				continue;
			}

			$this->Storage
				->reset()
				->delete($delEntity)
				->execute()
			;
		}

		unset($relation);
	}

	/**
	 * Executes delete for relation
	 *
	 * @param \lib\entity\EntityInterface $Entity
	 * @return void
	 */
	public function delete(EntityInterface $Entity) {
		try {
			$Entity->get($this->container);
		}
		catch(\DomainException $e) {
			return;
		}

		$relCollection = $Entity->get($this->container);
		if(count($relCollection)) {
			foreach($relCollection as $relEntity) {
				$this->Storage
					->reset()
					->delete($relEntity)
					->execute()
				;
			}
		}
	}
}
