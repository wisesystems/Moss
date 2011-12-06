<?php
namespace lib\component;

use \lib\component\SubjectPrototype;

/**
 * Observer interface (observer pattern)
 * Interface used for determining role in observer pattern.
 *
 * @package Moss Component
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
abstract class ObserverPrototype {

	/**
	 * Responds to observers subject notification
	 *
	 * @abstract
	 * @param SubjectPrototype $subject notifying subject instance
	 * @param null|string $message message associated with notification
	 * @return void
	 */
	abstract public function update(SubjectPrototype $subject, $message = null);
}
