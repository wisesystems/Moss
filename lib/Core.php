<?php
namespace lib;

use \lib\Config;
use \lib\Router;
use \lib\Dispatcher;
use \lib\Request;
use \lib\response\ResponseInterface;

/**
 * Moss Core
 *
 * @throws \DomainException
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Core {

	/**
	 * @var \lib\Config
	 */
	protected $Config;

	/**
	 * @var \lib\Router
	 */
	protected $Router;

	/**
	 * @var \lib\Container
	 */
	protected $Container;

	/**
	 * @var \lib\Dispatcher
	 */
	protected $Dispatcher;

	/**
	 * Constructor
	 * Creates Config, Router, Container, Dispatcher instance
	 */
	public function __construct() {
		$this->Config = new Config('../settings/config.xml');

		$this->Router = new Router();
		foreach($this->Config->getRoutes() as $xml) {
			$this->Router->readXML($xml);
		}

		$this->Container = new Container();
		foreach($this->Config->getComponents() as $xml) {
			$this->Container->readXML($xml);
		}

		$this->Dispatcher = new Dispatcher($this->Container);
		foreach($this->Config->getListeners() as $xml) {
			$this->Dispatcher->readXML($xml);
		}
	}

	/**
	 * Handles request
	 *
	 * @param \lib\Request $Request
	 * @return \lib\response\ResponseInterface
	 */
	public function handle(\lib\Request $Request) {
		$this->Container->setComponentInstance('Config', $this->Config, true);
		$this->Container->setComponentInstance('Request', $Request, true);
		$this->Container->setComponentInstance('Router', $this->Router, true);

		try {
			$Response = $this->Dispatcher->fire('core:request');
		}
		catch(\OutOfRangeException $e) {
			try {
				$this->Router->match($Request);

				try {
					try {
						$Response = $this->Dispatcher->fire('core:access');
					}
					catch(\UnexpectedValueException $e) {
						$Response = $this->Dispatcher->fire('core:403', null, sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine()));
					}
					catch(\OutOfRangeException $e) {
						$Response = $this->Dispatcher->fire('core:route');
					}
				}
				catch(\OutOfRangeException $e) {
					$Response = $this->getController($Request);
				}
			}
			catch(\RangeException $e) {
				$Response = $this->Dispatcher->fire('core:404', null, sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine()));
			}
			catch(\DomainException $e) {
				$Response = $this->Dispatcher->fire('core:500', null, sprintf('%s (%s line:%s)', $e->getMessage(), $e->getFile(), $e->getLine()));
			}
		}

		try {
			$Response = $this->Dispatcher->fire('core:send', $Response);
		}
		catch(\OutOfRangeException $e) {
			// NOP
		}

		$this->Container->writePersistent();

		return $Response;
	}

	/**
	 * Calls requested controller to handle request
	 *
	 * @throws \BadMethodCallException|\DomainException
	 * @param \lib\Request $Request
	 * @return \lib\response\ResponseInterface
	 */
	protected function getController(Request $Request) {
		if(empty($Request->lang) || empty($Request->controller) || empty($Request->action)) {
			throw new \DomainException('Undefined controller in request!');
		}

		$controller = $Request->controller;

		if(!$Controller = new $controller($this->Container, $this->Config, $this->Router, $Request)) {
			throw new \DomainException(sprintf('Controller %s can not be created', $Request->controller));
		}

		if(!method_exists($Controller, 'init') || !is_callable(array($Controller, 'init'))) {
			throw new \DomainException(sprintf('Action %s::%s can not be called', $Request->controller, 'init'));
		}

		if($Response = $Controller->init()) {
			return $Response;
		}

		$action = $Request->action;
		if(!method_exists($Controller, $action) || !is_callable(array($Controller, $action))) {
			throw new \DomainException(sprintf('Action %s::%s can not be called', $Request->controller, $Request->action));
		}

		if(!$Response = $Controller->$action()) {
			throw new \DomainException(sprintf('No response from %s::%s', $Request->controller, $Request->action));
		}

		return $Response;
	}
}