<?php
namespace lib;

use \lib\CookieInterface;

/**
 * Cookie object representation
 *
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Cookie implements CookieInterface {

	protected $domain;
	protected $path;
	protected $expire;
	protected $secure = null;
	protected $httponly = true;

	protected $protected = array('__utma', '__utmz', 'PHPSESSID');
	protected $storage;

	/**
	 * Creates cookie wrapper instance
	 *
	 * @param string $domain
	 * @param string $path
	 */
	public function __construct($domain = null, $path = '/') {
		$this->domain = $domain;
		$this->path = $path;
		$this->expire = strtotime('+2 months');

		$this->storage = &$_COOKIE;;
	}

	/**
	 * Clears all cookie data in domain/path
	 *
	 * @return void
	 */
	public function reset() {
		foreach(array_keys($_COOKIE) as $offset) {
			$_COOKIE = array();
			setcookie($offset, "", time() - 3600, $this->path, $this->domain, $this->secure, $this->httponly);
		}
	}

	/**
	 * @param int|string $offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($_COOKIE[$offset]);
		setcookie($offset, "", time() - 3600, $this->path, $this->domain, $this->secure, $this->httponly);
	}

	/**
	 * @param int|string $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		if(empty($offset)) {
			$offset = array_push($_COOKIE, $value);
		}

		setcookie($offset, $value, $this->expire, $this->path, $this->domain, $this->secure, $this->httponly);
	}

	/**
	 * Offset to retrieve
	 *
	 * @param string $offset
	 * @return mixed
	 */
	public function &offsetGet($offset) {
		if(!isset($this->storage[$offset])) {
			$this->storage[$offset] = null;
		}

 		return $this->storage[$offset];
	}

	/**
	 * Whether a offset exists
	 *
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset) {
		return isset($this->storage[$offset]);
	}

	/**
	 * Return the current element
	 *
	 * @return mixed
	 */
	public function current() {
		return current($this->storage);
	}

	/**
	 * Move forward to next element
	 *
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		next($this->storage);
	}

	/**
	 * Return the key of the current element
	 *
	 * @return scalar
	 */
	public function key() {
		return key($this->storage);
	}

	/**
	 * Checks if current position is valid
	 *
	 * @return boolean
	 */
	public function valid() {
		$key = key($this->storage);

		while($key !== null && in_array($key, $this->protected)) {
			$this->next();
			$key = key($this->storage);
		}

		if($key === false || $key === null) {
			return false;
		}

		return isset($this->storage[$key]);
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @return void
	 */
	public function rewind() {
		reset($this->storage);
	}

	/**
	 * Count elements of an object
	 *
	 * @return int
	 */
	public function count() {
		return count($this->storage) - count($this->protected);
	}
}