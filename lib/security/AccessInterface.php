<?php
namespace lib\security;

/**
 * Access interface
 * Represents role access
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface AccessInterface {

	/**
	 * Checks if element identifier matches access
	 *
	 * @abstract
	 * @param string $elementIdentifier
	 * @return void
	 */
	public function check($elementIdentifier);
}
