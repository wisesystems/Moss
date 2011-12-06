<?php
namespace lib\security;

/**
 * Role interface
 * Represents user role
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface RoleInterface {

	/**
	 * Checks if identifier matches role
	 *
	 * @abstract
	 * @param string $identifier role identifier
	 * @return bool
	 */
	public function check($identifier);
}
