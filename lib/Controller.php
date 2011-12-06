<?php
namespace lib;
use \lib\Container;
use \lib\Config;
use \lib\Router;
use \lib\Request;
use \lib\response\ResponseInterface;

/**
 * Abstract controller entity
 *
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
abstract class Controller implements \lib\ControllerInterface {

	/**
	 * @var \lib\Container
	 */
	protected $Container;

	/**
	 * @var \lib\Config
	 */
	protected $Config;

	/**
	 * @var \lib\Router
	 */
	protected $Router;

	/**
	 * @var \lib\Request
	 */
	protected $Request;

	/**
	 * Constructor, calls init function
	 *
	 * @param \lib\Container $Container
	 * @param \lib\Config $Config
	 * @param \lib\Router $Router
	 * @param \lib\Request $Request
	 */
	public function __construct(Container $Container, Config $Config, Router $Router, Request $Request) {
		$this->Container = &$Container;
		$this->Config = &$Config;
		$this->Router = &$Router;
		$this->Request = &$Request;
	}
}