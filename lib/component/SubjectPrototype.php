<?php
namespace lib\component;

use \lib\component\ObserverPrototype;

/**
 * Subject entity (observer pattern)
 * Interface used for determining role in observer pattern.
 *
 * @package Moss Component
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class SubjectPrototype {

	/**
	 * List of attached observers
	 * @var \splObjectStorage
	 */
	protected $observers;

	/**
	 * Attaches observer to subject
	 *
	 * @param ObserverPrototype $observer
	 * @return SubjectPrototype
	 */
	public function attach(ObserverPrototype $observer) {
		$this->constructStorage();

		$this->observers->attach($observer);

		return $this;
	}

	/**
	 * Detaches observer from subject
	 *
	 * @param ObserverPrototype $observer
	 * @return SubjectPrototype
	 */
	public function detach(ObserverPrototype $observer) {
		$this->constructStorage();

		$this->observers->detach($observer);

		return $this;
	}

	/**
	 * Notifies all attached subjects
	 *
	 * @param null|string $message message associated with notification
	 * @return SubjectPrototype
	 */
	public function notify($message = null) {
		$this->constructStorage();

		foreach($this->observers as $observer) {
			$observer->update($this, $message);
		}

		return $this;
	}

	/**
	 * Creates subject storage instance
	 *
	 * @return void
	 */
	protected function constructStorage() {
		if(!$this->observers) {
			$this->observers = new \SplObjectStorage();
		}
	}

}
