<?php
namespace lib;
use \lib\Container;
use \lib\Config;
use \lib\Router;
use \lib\Request;
use \lib\response\ResponseInterface;

 /**
 * Controllers interface
 *
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ControllerInterface {

	/**
	 * Constructor, calls init function
	 *
	 * @abstract
	 * @param \lib\Container $Container
	 * @param \lib\Config $Config
	 * @param \lib\Router $Router
	 * @param \lib\Request $Request
	 */
	public function __construct(Container $Container, Config $Config, Router $Router, Request $Request);

	/**
	 * Method for initialisation operations
	 * Called at the end of constructor
	 *
	 * @abstract
	 * @return void
	 */
	public function init();

	/**
	 * Controllers default action
	 * 
	 * @abstract
	 * @return ResponseInterface
	 */
	public function index();
}