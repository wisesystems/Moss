<?php
namespace component\core;

use \lib\component\ObserverPrototype;
use \lib\component\SubjectPrototype;

use \lib\component\PersistentInterface;

/**
 * Log mechanism
 * Users observer pattern
 *
 * @package Moss Core Component
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Log extends ObserverPrototype implements PersistentInterface {

	protected $log = array();
	protected $start;
	protected $path;
	protected $overwrite;

	/**
	 * Constructor
	 *
	 * @param null|string $path path to log file
	 * @param bool $overwrite if true - will overwrite log file
	 */
	public function __construct($path = null, $overwrite = true) {
		$this->path = $path;
		$this->start = microtime(true);
		$this->overwrite = (bool) $overwrite;
	}

	/**
	 * Inserts entry into log
	 *
	 * @param \lib\component\SubjectPrototype $subject
	 * @param string $message message passed to log
	 * @return Log
	 */
	public function update(SubjectPrototype $subject, $message = null) {

		$this->log[] = array(
			'timestamp' => microtime(true),
			'subject' => get_class($subject),
			'message' => $message
		);

		return $this;
	}

	/**
	 * Calculates time from the creation Log instance
	 *
	 * @return string
	 */
	public function getElapsedTime() {
		return number_format(microtime(true) - $this->start, 4);
	}

	/**
	 * Casts log to string
	 *
	 * @return string
	 */
	public function __toString() {
		ob_start();
		
		foreach($this->log as $entry) {
			echo date('Y-m-d H:i:s', $entry['timestamp']), "\t", $entry['subject'], "\t", $entry['message'], "\n";
		}

		echo $this->getElapsedTime();

		return ob_get_clean();
	}

	/**
	 * Recovers object state
	 *
	 * @return void
	 */
	public function recover() {
		// does nothing
	}


	/**
	 * Saves object state
	 *
	 * @return void
	 */
	public function persist() {
		if($this->path) {
			file_put_contents(
				$this->path,
				(is_file($this->path) && !$this->overwrite ? "\n\n" : null).(string) $this,
				$this->overwrite ? null : FILE_APPEND
			);
		}
	}
}
