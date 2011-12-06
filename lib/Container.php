<?php
namespace lib;

use \lib\ComponentDefinition;
use \lib\component\PersistentInterface;

/**
 * Dependency Injection Container
 *
 * @throws \DomainException|\OutOfRangeException
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Container {

	protected $definitions = array();
	protected $shared = array();
	protected $parameter = array();

	/**
	 * Reads component definitions from XML file
	 *
	 * @throws \DomainException
	 * @param string $path path to XML file
	 * @return Container
	 */
	public function readXML($path) {
		if(!is_file($path)) {
			throw new \DomainException(sprintf('XML file (%s) not found!', $path));
		}

		$xml = new \SimpleXMLElement(file_get_contents($path));

		if(!isset($xml->component) && !isset($xml->parameter)) {
			throw new \DomainException(sprintf('Incorrect file format (missing nodes) in %s!', $path));
		}

		if(isset($xml->component)) {
			foreach($xml->component as $component) {
				$Definition = new ComponentDefinition(
					(string) $component->attributes()->class,
					$component->attributes()->shared == "true" || $component->attributes()->shared == 1
				);

				if(isset($component->arguments)) {
					foreach($component->arguments->children() as $arg) {
						$Definition->argument(
							(string) $arg->attributes()->type,
							count($arg->children()) ? (array) $arg->children() : (string) $arg
						);
					}
				}

				$this->setComponent((string) $component->attributes()->id, $Definition);
			}
		}

		if(isset($xml->parameter)) {
			foreach($xml->parameter as $parameter) {
				$this->setParameter(
					(string) $parameter->attributes()->id,
					html_entity_decode($parameter->attributes()->value)
				);
			}
		}

		return $this;
	}

	/**
	 * Assigns component definition to identifier
	 *
	 * @param string $componentIdentifier
	 * @param ComponentDefinition $Definition
	 * @return Container
	 */
	public function setComponent($componentIdentifier, ComponentDefinition $Definition) {
		$this->definitions[$componentIdentifier] = $Definition;

		return $this;
	}

	/**
	 * Assigns existing object instance to identifier
	 * Creates definition from passed instance
	 *
	 * @param string $componentIdentifier
	 * @param object $instance component instance
	 * @param bool $shared true if component should have only one instance
	 * @return Container
	 */
	public function setComponentInstance($componentIdentifier, $instance, $shared = true) {
		$this->definitions[$componentIdentifier] = new ComponentDefinition(get_class($instance), $shared);

		if($this->definitions[$componentIdentifier]->isShared()) {
			$this->shared[$componentIdentifier] = &$instance;
		}

		return $this;
	}

	/**
	 * Retrieves component
	 *
	 * @throws \DomainException|\OutOfRangeException
	 * @param string $componentIdentifier component identifier to retrieve
	 * @return mixed
	 */
	public function &getComponent($componentIdentifier) {
		if(!isset($this->definitions[$componentIdentifier])) {
			throw new \OutOfRangeException(sprintf('Component %s does not exists!', $componentIdentifier));
		}

		if($this->definitions[$componentIdentifier]->isShared()) {
			if(!isset($this->shared[$componentIdentifier])) {
				if(!$this->shared[$componentIdentifier] = $this->definitions[$componentIdentifier]->initialize($this)) {
					throw new \DomainException(sprintf('Shared component %s can not be created!', $componentIdentifier));
				}
			}

			if($this->shared[$componentIdentifier] instanceof PersistentInterface) {
				$this->shared[$componentIdentifier]->recover();
			}

			return $this->shared[$componentIdentifier];
		}
		else {
			if(!$component = $this->definitions[$componentIdentifier]->initialize($this)) {
				throw new \DomainException(sprintf('Component %s can not be created!', $componentIdentifier));
			}

			return $component;
		}
	}

	/**
	 * Checks if component exists
	 *
	 * @param string $componentIdentifier
	 * @return bool
	 */
	public function checkComponent($componentIdentifier) {
		return isset($this->definitions[$componentIdentifier]);
	}

	/**
	 * Sets parameter
	 *
	 * @param string $paramIdentifier
	 * @param mixed $paramValue
	 * @return Container
	 */
	public function setParameter($paramIdentifier, $paramValue) {
		$this->parameter[$paramIdentifier] = $paramValue;
		
		return $this;
	}

	/**
	 * Retrieves parameter
	 *
	 * @throws \OutOfRangeException
	 * @param string $paramIdentifier parameter identifier to retrieve
	 * @return mixed
	 */
	public function getParameter($paramIdentifier) {
		if(!isset($this->parameter[$paramIdentifier])) {
			throw new \OutOfRangeException(sprintf('Parameter %s does not exists!', $paramIdentifier));
		}

		return $this->parameter[$paramIdentifier];
	}

	/**
	 * Checks if parameter exists
	 *
	 * @param string $paramIdentifier
	 * @return bool
	 */
	public function checkParameter($paramIdentifier) {
		return isset($this->parameter[$paramIdentifier]);
	}

	/**
	 * Writes persistent components states
	 *
	 * @return Container
	 */
	public function writePersistent() {
		foreach($this->shared as &$Component) {
			if($Component instanceof PersistentInterface) {
				$Component->persist();
			}
			
			unset($Component);
		}

		return $this;
	}
}