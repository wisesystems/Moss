<?php
namespace lib\entity;

use \lib\entity\CollectionInterface;

/**
 * Collection prototype
 *
 * @package Moss DDD
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Collection extends \ArrayObject implements CollectionInterface {

	/**
	 * Creates collection instance
	 *
	 * @param array $iArr array containing collection data
	 */
	public function __construct($iArr = array()) {
		parent::__construct($iArr, 2);
	}

	/**
	 * Retrieves all data as array
	 * 
	 * @return array
	 */
	public function retrieve() {
		$content = array();
		foreach($this as $key => $Entity) {
			$content[$key] = $Entity->retrieve();
		}

		return $content;
	}

	/**
	 * Sorts an array by key in reverse order, maintaining key to data correlations
	 *
	 * @return Collection
	 */
	public function krsort() {
		$this->uksort( function($a, $b) {
			if($a > $b) {
				return -1;
			}
			elseif($a < $b) {
				return 1;
			}
			else {
				return 0;
			}
		});

		return $this;
	}
}