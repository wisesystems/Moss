<?php
namespace lib;

/**
 * Session objects interface
 *
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface SessionInterface extends \Iterator, \ArrayAccess, \Countable {

	/**
	 * Creates session wrapper instance
	 *
	 * @abstract
	 */
	public function __construct();

	/**
	 * Clears session data
	 *
	 * @abstract
	 * @return void
	 */
	public function reset();
}