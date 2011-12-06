<?php
namespace lib;

use \lib\SessionInterface;

/**
 * Session object representation
 *
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Session implements SessionInterface {
	protected $agent = true;
	protected $ip = true;
	protected $host = true;
	protected $salt;

	protected $protected = array('agent', 'ip', 'host');
	protected $storage;

	/**
	 * Creates session wrapper instance
	 * Also validates existing session - if session is invalid, resets it
	 */
	public function __construct() {
		$this->salt = null;

		if(!session_id()) {
			session_start();
		}

		$this->storage = &$_SESSION;

		if(!$this->validate()) {
			$this->reset();
		}

		if($this->agent && !isset($_SESSION['agent'])) {
			$_SESSION['agent'] = $this->agent();
		}

		if($this->ip && !isset($_SESSION['ip'])) {
			$_SESSION['ip'] = $this->ip();
		}

		if($this->host && !isset($_SESSION['host'])) {
			$_SESSION['host'] = $this->host();
		}
	}

	/**
	 * Clears all session data
	 *
	 * @return void
	 */
	public function reset() {
		$_SESSION = array();
		session_destroy();
		session_start();
	}

	/**
	 * Validates session
	 *
	 * @return bool
	 */
	protected function validate() {
		if($this->agent && (!isset($_SESSION['agent']) || $_SESSION['agent'] != $this->agent())) {
			return false;
		}

		if($this->ip && (!isset($_SESSION['ip']) || $_SESSION['ip'] != $this->ip())) {
			return false;
		}

		if($this->host && (!isset($_SESSION['host']) || $_SESSION['host'] != $this->host())) {
			return false;
		}

		return true;
	}

	/**
	 * Defines user agent hash for validation purposes
	 *
	 * @return string
	 */
	protected function agent() {
		return base64_encode(sha1((isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'UndefinedUserAgent').$this->salt, true));
	}

	/**
	 * Defines user ip hash for validation purposes
	 *
	 * @return string
	 */
	protected function ip() {
		if(getenv('HTTP_X_FORWARDED_FOR')) {
			$ret = getenv('HTTP_X_FORWARDED_FOR').$this->salt;
		}
		else {
			$ret = getenv('REMOTE_ADDR').$this->salt;
		}

		return base64_encode(sha1($ret, true));
	}

	/**
	 * Defines host hash for validation purposes
	 *
	 * @return string
	 */
	protected function host() {
		return base64_encode(sha1($_SERVER['SCRIPT_FILENAME'].(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'CGI').$this->salt, true));
	}

	/**
	 * Offset to unset
	 *
	 * @param string $offset
	 * @return void
	 */
	public function offsetUnset($offset) {
		unset($this->storage[$offset]);
	}

	/**
	 * Offset to set
	 *
	 * @param string $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value) {
		if(empty($offset)) {
			$offset = array_push($_COOKIE, $value);
		}

		$this->storage[$offset] = $value;
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