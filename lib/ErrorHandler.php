<?php
namespace lib;

/**
 * Moss error handler
 *
 * @throws \ErrorException
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class ErrorHandler {

	/**
	 * Registers handler
	 *
	 * @return void
	 */
	public function register() {
		set_error_handler(array($this, 'handler'), !E_WARNING );
	}


	/**
	 * Unregisters handler
	 *
	 * @return void
	 */
	public function unregister() {
		restore_error_handler();
	}

	/**
	 * Handles errors, throws them as Exceptions
	 * 
	 * @throws \ErrorException
	 * @param $errno
	 * @param $errstr
	 * @param $errfile
	 * @param $errline
	 * @param null $errcontext
	 * @return void
	 */
	public function handler($errno, $errstr, $errfile, $errline, $errcontext = null) {
		throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
	}
}