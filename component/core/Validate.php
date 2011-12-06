<?php
namespace component\core;

/**
 * Validate
 * Contains popular validation patterns, methods and data sets
 * Supports regular expressions, arrays of valid data and closures
 *
 * @package Moss Core Component
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Validate {


	// Remember the 'u' flag for utf-8 support
	// If case insensitive remember 'i' flag
	protected $year = '/^19|20\d\d$/iu';
	protected $date = '/^19|20\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/iu';
	protected $time = '/^\d{1,2}:\d{2}(:\d{2})?$/iu';
	protected $datetime = '/^19|20\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])( \d{1,2}:\d{2}(:\d{2})?)?$/iu';

	protected $name = '/^.+$/iu';
	protected $firstname = '/^[\pL ]{3,}$/iu';
	protected $surname = '/^[\pL -]{2,}$/iu';

	protected $street = '/^[\pL -.]{3,}$/iu';
	protected $city = '/^[\pL -.]{3,}$/iu';
	protected $region = '/^[\pL -.]{3,}$/iu';

	protected $tel = '/^\+?[0-9 -]+$/i';
	protected $fax = '/^\+?[0-9 -]+$/i';

	protected $email = '/^[a-z0-9._%-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i';
	protected $postcode = '/^[0-9]{2}-[0-9]{3}$/i';

	protected $login = '/^[a-z0-9!@#$^&*()_+\-={}\[\]:;<>,.]{3,}$/i';
	protected $password = '/^[a-z0-9!@#$^&*()_+\-={}\[\]:;<>,.]{3,}$/i';

	protected $lang = '/^[a-z]{2}$/';
	protected $controller= '/^[a-z]{2}:[a-z:]+:[a-z_]+$/i';

	protected $boolean = array(0,1);
	protected $integer = '/^[\-+]?[0-9]+$/i';
	protected $decimal = '/^[-+]?[0-9]+(\.|,)?[0-9]*$/i';
	protected $text = '/^.+$/imu';

	/**
	 * Retrieves validation method
	 * Method can be represented as regular expression, array of permitted values or closure
	 *
	 * @param string $identifier validation identifier
	 * @return null|string|array|closure
	 */
	public function get($identifier) {
		if(isset($this->$identifier)) {
			return $this->$identifier;
		}

		if(method_exists($this, $identifier)) {
			$Validate = &$this;
			return function($value) use($Validate, $identifier) { return $Validate->$identifier($value); };
		}

		return null;
	}

	/**
	 * Validates ISBN
	 *
	 * @param string $isbn ISBN number to validate
	 * @return bool
	 */
	public function isbn($isbn) {
		$isbn = preg_replace('#[^0-9]#im', null, $isbn);

		if(strlen($isbn) != 13) {
			return false;
		}

		$num = 0;
		for($i = 0; $i < 12; $i++) {
			$num += $isbn[$i] * ($i % 2 ? 3 : 1);
		}

		$num = $num % 10;
		if($num) {
			$num = 10 - $num % 10;
		}

		if($num != $isbn[12]) {
			return false;
		}

		return true;
	}

	/**
	 * Validates PESEL
	 *
	 * @param string $pesel PESEL number to validate
	 * @return bool
	 */
	public function pesel($pesel) {
		$pesel = preg_replace('#[^0-9]#im', null, $pesel);

		if(strlen($pesel) != 11) {
			return false;
		}

		$num = ($pesel[0] + 3 * $pesel[1] + 7 * $pesel[2] + 9 * $pesel[3] + $pesel[4] + 3 * $pesel[5] + 7 * $pesel[6] + 9 * $pesel[7] + $pesel[8] + 3 * $pesel[9]);
		$num = $num % 10;
		if($num) {
			$num = 10 - $num % 10;
		}
		
		if($num != $pesel[10]) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if PESEL is for male person
	 *
	 * @param $pesel
	 * @return bool|int
	 */
	public function peselMale($pesel) {
		$pesel = preg_replace('#[^0-9]#im', null, $pesel);

		if(strlen($pesel) != 11) {
			return false;
		}

		return $pesel[9] % 2 != 0;
	}

	/**
	 * Validates NIP
	 *
	 * @param string $nip NIP to validate
	 * @return bool
	 */
	public function nip($nip) {
		$nip = preg_replace('#[^0-9]#im', null, $nip);

		if(strlen($nip) != 10){
			return false;
		}

		return ($nip[0] * 6 + $nip[1] * 5 + $nip[2] * 7 + $nip[3] * 2 + $nip[4] * 3 + $nip[5] * 4 + $nip[6] * 5 + $nip[7] * 6 + $nip[8] * 7) % 11 == $nip[9];
	}
}
