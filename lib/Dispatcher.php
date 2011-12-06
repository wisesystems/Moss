<?php
namespace lib;
use \lib\Container;
use \lib\ComponentDefinition;

use \lib\component\SubjectPrototype;

use \lib\response\ResponseInterface;

/**
 * Event dispatcher
 * Handles events and forwards responses trough them
 *
 * @throws \DomainException|\InvalidArgumentException|\OutOfRangeException
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Dispatcher extends SubjectPrototype {

	protected $Container;
	protected $listeners = array();

	/**
	 * Constructor
	 * 
	 * @param Container $Container
	 */
	public function __construct(Container $Container) {
		$this->Container = &$Container;
	}

	/**
	 * Reads listeners definitions
	 * All listeners are treated as components and must be defined in Container
	 *
	 * @throws \DomainException
	 * @param string $path path to XML file
	 * @return Dispatcher
	 */
	public function readXML($path) {
		if(!is_file($path)) {
			throw new \DomainException(sprintf('XML file (%s) not found!', $path));
		}

		$xml = new \SimpleXMLElement(file_get_contents($path));

		if(!isset($xml->event)) {
			throw new \DomainException(sprintf('Incorrect file format (missing nodes) in %s!', $path));
		}

		if(isset($xml->event)) {
			foreach($xml->event as $event) {
				foreach($event->component as $component) {
					if($component->attributes()->id) {
						$this->registerComponent(
							(string) $event->attributes()->name,
							(string) $component->attributes()->id,
							(string) $component->attributes()->method
						);
					}
					else {
						$Definition = new \lib\ComponentDefinition(
							(string) $component->attributes()->class,
							true
						);

						if(isset($component->arguments)) {
							foreach($component->arguments->children() as $arg) {
								$Definition->argument(
									(string) $arg->attributes()->type,
									count($arg->children()) ? (array) $arg->children() : (string) $arg
								);
							}
						}

						$this->register(
							(string) $event->attributes()->name,
							(string) $component->attributes()->class,
							(string) $component->attributes()->method,
							$Definition
						);
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Registers listener to event
	 *
	 * @throws \InvalidArgumentException
	 * @param string $event event name, e.g. core:send
	 * @param string $listener listener class, instance created at event
	 * @param string $method listener method called at event
	 * @param ComponentDefinition $Definition Component definition
	 * @return Dispatcher
	 */
	public function register($event, $listener, $method, ComponentDefinition $Definition) {
		if(empty($listener)) {
			throw new \InvalidArgumentException(sprintf('Invalid definition %s::%s!', $listener, $method));
		}

		if(!isset($this->listeners[$event])) {
			$this->listeners[$event] = array();
		}

		$this->Container->setComponent($event.$listener.$method, $Definition);
		$this->listeners[$event][$event.$listener.$method] = $method;

		return $this;
	}

	/**
	 * Registers component as event listener
	 *
	 * @param string $event event identifier (e.g. core:request)
	 * @param string $listener listening component identifier
	 * @param string $method components method that should be called
	 * @return Dispatcher
	 * @throws \InvalidArgumentException
	 */
	public function registerComponent($event, $listener, $method) {
		if(empty($listener) || !$this->Container->checkComponent($listener)) {
			throw new \InvalidArgumentException(sprintf('Invalid listener %s::%s!', $listener, $method));
		}

		if(!isset($this->listeners[$event])) {
			$this->listeners[$event] = array();
		}

		$this->listeners[$event][$listener] = $method;

		return $this;
	}

	/**
	 * Unregisters listener from event
	 *
	 * @throws \InvalidArgumentException
	 * @param string $event event name, e.g. core:send
	 * @param string $listener listener class
	 * @param string $method listener method
	 * @return Dispatcher
	 */
	public function unregister($event, $listener, $method = null) {
		if(!empty($listener) && !class_exists($listener, true)) {
			throw new \InvalidArgumentException(sprintf('Invalid listener %s::%s!', $listener, $method));
		}
		
		if(!isset($this->listeners[$event])) {
			return $this;
		}

		unset($this->listeners[$event][$event.$listener.$method]);

		return $this;
	}

	/**
	 * Notifies all attached listeners fo event
	 *
	 * @throws \OutOfRangeException
	 * @param string $event event name, e.g. core:send
	 * @param response\ResponseInterface|null $Response
	 * @param string $message
	 * @return response\ResponseInterface|null|object
	 */
	public function fire($event, \lib\response\ResponseInterface $Response = null, $message = null) {
		$this->notify($event.' '.$message);

		if(!isset($this->listeners[$event])) {
			throw new \OutOfRangeException(sprintf('No listener defined for event "%s"', $event));
		}

		foreach($this->listeners[$event] as $componentIdentifier => $method) {
			if(isset($Response) && $Response instanceof \lib\response\ResponseInterface) {
				$this->Container->setComponentInstance('Response', $Response, true);
			}

			$instance = $this->Container->getComponent($componentIdentifier);
			
			if($method && is_callable(array($instance, $method))) {
				$Response = $instance->$method($message);
				continue;
			}

			if($instance instanceof \lib\response\ResponseInterface) {
				$Response = $instance;
			}
		}

		if(isset($Response)) {
			return $Response;
		}

		throw new \OutOfRangeException('No response found');
	}
}

