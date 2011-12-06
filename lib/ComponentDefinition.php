<?php
namespace lib;

 /**
 * Component definition, represents component for Dependency Injection container
 *
 * @throws \InvalidArgumentException
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class ComponentDefinition {

	protected $shared;
	protected $className;
	protected $arguments;
	protected $initialisation;

	/**
	 * Constructor
	 *
	 * @param string $className component class name
	 * @param bool $shared true if should have only one instance
	 */
	public function __construct($className, $shared = false) {
		if(strrpos($className, '\\') === false) {
			$className = '\\component\\'.$className;
		}

		$this->className = $className;
		$this->shared = (bool) $shared;
	}

	/**
	 * Adds arguments to definition
	 *
	 * @throws \InvalidArgumentException
	 * @param string $type argument type, can be container, component, variable or parameter
	 * @param mixed $value argument value
	 * @return ComponentDefinition
	 */
	public function argument($type, $value) {
		if(!in_array($type, array('container', 'component', 'parameter', 'variable'))) {
			throw new \InvalidArgumentException(sprintf('Invalid argument type (%s) in %s for component %s', $type,  __CLASS__, $this->className));
		}

		$this->arguments[] = array('type' => $type, 'value' => $value);
		return $this;
	}

	/**
	 * Returns information about component sharing
	 * True if component is shared (only one instance)
	 *
	 * @return bool
	 */
	public function isShared() {
		return $this->shared;
	}

	/**
	 * Creates component instance
	 * 
	 * @param \lib\Container $Container
	 * @return object
	 */
	public function &initialize(\lib\Container $Container) {
		if(empty($this->arguments)) {
			$instance = new $this->className();
		}
		else {
			foreach($this->arguments as &$arg) {
				if(!is_array($arg)) {
					continue;
				}

				switch($arg['type']) {
					case 'container':
						$arg = $Container;
						break;
					case 'component':
						$arg = $Container->getComponent($arg['value']);
						break;
					case 'parameter':
						$arg = $Container->getParameter($arg['value']);
						break;
					default:
						$arg = $arg['value'];
				}
				unset($arg);
			}

			$ref = new \ReflectionClass($this->className);
			$instance = $ref->newInstanceArgs($this->arguments);
		}

		return $instance;
	}
}
