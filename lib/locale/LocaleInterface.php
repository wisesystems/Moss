<?php
namespace lib\locale;

/**
 * Locale interface
 *
 * @package Moss Locale
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface LocaleInterface {

	/**
	 * Checks if localized word exists
	 *
	 * @param string $string word identifier
	 * @return bool
	 */
	public function exists($string);

	/**
	 * Retrieves localized word for passed identifier
	 * If args not empty - localized word will be parsed for data insertion (e.g. {foo} will be replaced with corresponding args[foo] value)
	 *
	 * @param $string word identifier
	 * @param array $args data insertion variables
	 * @return string
	 */
	public function get($string, $args = array());
}