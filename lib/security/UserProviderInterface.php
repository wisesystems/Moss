<?php
namespace lib\security;

use \lib\security\UserProviderInterface;

/**
 * User provider interface
 * Represents source for user data
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface UserProviderInterface {

	/**
	 * Authenticates User Entity
	 *
	 * @abstract
	 * @param UserInterface $User entity instance that will be authenticated
	 * @return void
	 */
	public function authenticate(UserInterface $User);
}
