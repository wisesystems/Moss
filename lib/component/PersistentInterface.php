<?php
namespace lib\component;

/**
 * Persistent interface
 * Persistent components regain its state when instance is initialized
 * Only shared components should use this interface
 *
 * @package Moss Component
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface PersistentInterface {

	/**
	 * Recovers object state
	 *
	 * @abstract
	 * @return void
	 */
	public function recover();

	/**
	 * Saves object state
	 *
	 * @abstract
	 * @return void
	 */
	public function persist();

}
