<?php
namespace lib\model;

use \lib\SessionInterface;
use \lib\security\UserInterface;
use \lib\security\UserProviderInterface;

/**
 * Security interface
 * Models implementing this interface acts an intermediaries with Security, User Providers and so on
 *
 * @package Moss Model
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface SecureInterface {

	/**
	 * Encodes passed string as password
	 *
	 * @abstract
	 * @param string $password password to encode
	 * @return string
	 */
	public function encodePassword($password);

	/**
	 * Generates authorisation key
	 * Based on session data
	 *
	 * @abstract
	 * @return string
	 */
	public function generateAuthKey();

	/**
	 * Generates user authorisation key
	 * Based on user and session data
	 *
	 * @abstract
	 * @param \lib\security\UserInterface $User
	 * @return string
	 */
	public function generateUserAuthKey(\lib\security\UserInterface $User);

	/**
	 * Tries to log in user into realm
	 *
	 * @abstract
	 * @throws \UnexpectedValueException
	 * @param \lib\security\UserInterface $User user entity to be logged in
	 * @param bool $force if true, user entity will not be authenticated
	 * @return \lib\security\UserInterface
	 */
	public function login(UserInterface $User, $force = false);

	/**
	 * Logs out user
	 * Resets session
	 *
	 * @abstract
	 * @return Security
	 */
	public function logout();

	/**
	 * Checks if user is logged into realm
	 *
	 * @abstract
	 * @throws \UnexpectedValueException
	 * @param \lib\security\UserInterface $User user instance
	 * @return \lib\security\UserInterface
	 */
	public function logged(\lib\security\UserInterface $User);
}