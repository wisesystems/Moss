<?php
namespace lib\security;

/**
 * User interface
 * Represents user entity
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface UserInterface {

	/**
	 * Retrieves login
	 *
	 * @abstract
	 * @return string
	 */
	public function getLogin();

	/**
	 * Sets login
	 *
	 * @abstract
	 * @param $login new login
	 * @return UserInterface
	 */
	public function setLogin($login);

	/**
	 * Retrieves password
	 *
	 * @abstract
	 * @return string
	 */
	public function getPassword();

	/**
	 * Sets password
	 *
	 * @abstract
	 * @param string $password new password (raw)
	 * @return UserInterface
	 */
	public function setPassword($password);

	/**
	 * Retrieves user active status
	 *
	 * @abstract
	 * @return bool
	 */
	public function getActive();

	/**
	 * Sets user active status
	 *
	 * @abstract
	 * @param bool $status new status
	 * @return UserInterface
	 */
	public function setActive($status);

	/**
	 * Checks if user has role
	 *
	 * @abstract
	 * @param string $role role identifier
	 * @return void
	 */
	public function hasRole($role);

	/**
	 * Checks if user has access to element
	 *
	 * @abstract
	 * @param string $element element identifier
	 * @return void
	 */
	public function hasAccess($element);
}