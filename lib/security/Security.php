<?php
namespace lib\security;

use \lib\SessionInterface;

use \lib\security\UserInterface;
use \lib\security\RoleInterface;
use \lib\security\AccessInterface;

use \lib\security\UserProviderInterface;

/**
 * Security implementation
 *
 * @throws \UnexpectedValueException
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Security {

	protected $realm;
	protected $Session;
	protected $UserProvider;

	protected $passwordAlgorithm = 'md5';
	protected $passwordIterations = 1;
	protected $passwordBase64 = false;
	protected $passwordUseSalt = false;
	protected $passwordSalt;

	/**
	 * Creates new Security instance
	 *
	 * @param string $realm realm identifier that will be maintained by instance
	 * @param \lib\SessionInterface $Session
	 * @param UserProviderInterface $UserProvider
	 */
	public  function __construct($realm, SessionInterface $Session, UserProviderInterface $UserProvider) {
		$this->realm = $realm;
		$this->Session = &$Session;
		$this->UserProvider = &$UserProvider;
	}

	/**
	 * Encodes passed string as password
	 *
	 * @param string $password password to encode
	 * @return string
	 */
	public function encodePassword($password) {
		if($this->passwordUseSalt) {
			$password .= $this->passwordSalt;
		}

		for($i = 0; $i < $this->passwordIterations; $i++) {
			$password = hash($this->passwordAlgorithm, $password);
		}

		if($this->passwordBase64) {
			$password = base64_encode($password);
		}

		return $password;
	}

	/**
	 * Generates authorisation key
	 * Based on session data
	 *
	 * @return string
	 */
	public function generateAuthKey() {
		return $this->encodePassword($this->Session['UserId'].$this->Session['agent'].$this->Session['ip'].$this->Session['host']);
	}


	/**
	 * Generates user authorisation key
	 * Based on user and session data
	 *
	 * @param UserInterface $User
	 * @return string
	 */
	public function generateUserAuthKey(\lib\security\UserInterface $User) {
		return $this->encodePassword($User->identify().$User->getLogin().$this->Session['UserId'].$this->Session['agent'].$this->Session['ip'].$this->Session['host']);
	}

	/**
	 * Tries to log in user into realm
	 *
	 * @throws \UnexpectedValueException
	 * @param UserInterface $User user entity to be logged in
	 * @param bool $force if true, user entity will not be authenticated
	 * @return UserInterface|void
	 */
	public function login(UserInterface $User, $force = false) {
		if($force) {
			if(!$User->identify()) {
				throw new \UnexpectedValueException('Invalid or empty user identifier');
			}
		}
		else {
			if(!$User->getLogin() || !$User->getPassword()) {
				throw new \UnexpectedValueException('Username and/or password is not specified');
			}

			$User
				->setPassword($this->encodePassword($User->getPassword()))
				->setActive(true)
			;
		}

		if(!$User = $this->UserProvider->authenticate($User)) {
			throw new \UnexpectedValueException('Invalid username and/or password');
		}

		$this->Session['Realm'] = $this->realm;
		$this->Session['UserId'] = $User->identify();
		$this->Session['AuthKey'] = $this->generateAuthKey();

		return $User;
	}

	/**
	 * Logs out user
	 * Resets session
	 *
	 * @return Security
	 */
	public function logout() {
		$this->Session->reset();

		return $this;
	}

	/**
	 * Checks if user is logged into realm
	 *
	 * @throws \UnexpectedValueException
	 * @param UserInterface $User user instance
	 * @return UserInterface|void
	 */
	public function logged(\lib\security\UserInterface $User) {
		if(!$this->Session['Realm'] || $this->Session['Realm'] != $this->realm) {
			throw new \UnexpectedValueException('Not logged: Invalid realm.');
		}

		if(!$this->Session['UserId'] || $this->Session['UserId'] <= 0) {
			throw new \UnexpectedValueException('Not logged: Invalid user data.');
		}

		$User->identify($this->Session['UserId']);
		$User->setActive(true);

		$User = $this->UserProvider->authenticate($User);

		if(!$User) {
			throw new \UnexpectedValueException('Not logged: active user not found');
		}

		return $User;
	}
}
