<?php
namespace lib;

/**
 * Cookie objects interface
 *
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface CookieInterface extends \Iterator, \ArrayAccess, \Countable {

	/**
	 * Creates cookie wrapper instance
	 *
	 * @abstract
	 */
	public function __construct();

	/**
	 * Clears all cookie data
	 *
	 * @abstract
	 * @return void
	 */
	public function reset();
}