<?php
namespace lib\component;

/**
 * Cache interface
 * Used to maintain uniform interface for all cache mechanisms
 *
 * @package Moss Component
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface CacheInterface {

	/**
	 * Adds data to cache
	 * If data already exists in cache, should throw exception
	 *
	 * @abstract
	 * @throws \OutOfRangeExcception
	 * @param string $index data identifier
	 * @param mixed $value cached data
	 * @param int $ttl time to live in seconds
	 * @return \lib\component\Cache
	 */
	public function add($index, $value = null, $ttl = 0);

	/**
	 * Adds data to cache
	 * If data already exists in cache will be overwritten
	 *
	 * @abstract
	 * @param string $index data identifier
	 * @param mixed $value cached data
	 * @param int $ttl time to live in seconds
	 * @return \lib\component\Cache
	 */
	public function store($index, $value = null, $ttl = 0);

	/**
	 * Removes data from cache
	 * Throws exception if data not found
	 *
	 * @abstract
	 * @throws \OutOfRangeExcception
	 * @param string $index data identifier
	 * @return \lib\component\Cache
	 */
	public function delete($index);

	/**
	 * Retrieves data from cache
	 * Throws exception if data not found
	 *
	 * @abstract
	 * @throws \OutOfRangeExcception
	 * @param $index data identifier
	 * @return mixed
	 */
	public function fetch($index);

	/**
	 * Checks if data exists in cache
	 *
	 * @abstract
	 * @param $index data identifier
	 * @return bool
	 */
	public function exists($index);

	/**
	 * Retrieves cache info
	 *
	 * @abstract
	 * @return array
	 */
	public function info();

	/**
	 * Clears all data in cache
	 *
	 * @abstract
	 * @return \lib\component\Cache
	 */
	public function clear();
}
