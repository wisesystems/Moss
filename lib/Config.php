<?php
namespace lib;

 /**
 * Configuration representation
 *
 * @throws \DomainException|\LengthException
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Config {
        protected $cache;
        protected $debug;
	protected $namespaces = array();
	protected $components = array();
	protected $routes = array();
        protected $directories = array();
	protected $locales = array();
	protected $listeners = array();

	/**
	 * Constructor
	 *
	 * @param string $path path to configuration file
	 */
	public function __construct($path) {
		$this->readXML($path);
	}

	/**
	 * Reads configuration from XML file
	 * 
	 * @throws \DomainException|\LengthException
	 * @param string $path path to XML file
	 * @return Config
	 */
	public function readXML($path) {
		if(empty($path)) {
			throw new \LengthException('File path not set');
		}

		if(!is_file($path)) {
			throw new \DomainException(sprintf('XML file (%s) not found!', $path));
		}

		$xml = new \SimpleXMLElement(file_get_contents($path));

		if(!isset($xml->cache, $xml->debug, $xml->namespaces, $xml->components, $xml->routes, $xml->directories, $xml->locales)) {
			throw new \DomainException(sprintf('Incorrect file format (missing nodes) in %s!', $path));
		}

		$this->cache = (int) $xml->cache == 1 || (string) $xml->cache == 'true';
		$this->debug = (int) $xml->debug == 1 || (string) $xml->debug == 'true';
		$this->directories = (array) $xml->directories->children();
		$this->components = (array) $xml->components->children();
		$this->routes = (array) $xml->routes->children();
		$this->locales = (array) $xml->locales->children();
		$this->listeners = (array) $xml->listeners->children();

		$nsxml = new \SimpleXMLElement(file_get_contents((string) $xml->namespaces));
		foreach($nsxml as $namespace) {
			$this->namespaces[(string) $namespace->attributes()->namespace] = (string) $namespace->attributes()->path;
		};

		return $this;
	}

	/**
	 * Returns true if cache enabled
	 * 
	 * @return bool
	 */
	public function isCacheMode() {
		return (bool) $this->cache;
	}

	/**
	 * Returns true if debug enabled
	 *
	 * @return bool
	 */
	public function isDebugMode() {
		return (bool) $this->debug;
	}

	/**
	 * Returns path to namespace
	 *
	 * @param string $namespaceName
	 * @return string|bool
	 */
	public function getNamespace($namespaceName) {
		if(isset($this->namespaces[$namespaceName])) {
			return $this->namespaces[$namespaceName];
		}

		return false;
	}

	/**
	 * Returns all defined namespaces
	 *
	 * @return array
	 */
	public function getNamespaces() {
		return $this->namespaces;
	}

	/**
	 * Returns path to component definitions in XML
	 *
	 * @param string $componentName component definitions identifier
	 * @return string|bool
	 */
	public function getComponent($componentName) {
		if(isset($this->components[$componentName])) {
			return $this->components[$componentName];
		}

		return false;
	}

	/**
	 * Returns all defined component definitions paths
	 * 
	 * @return array
	 */
	public function getComponents() {
		return $this->components;
	}

	/**
	 * Returns path to route definitions in XML
	 * 
	 * @param string $routeName route definitions identifier
	 * @return array|bool
	 */
	public function getRoute($routeName) {
		if(isset($this->routes[$routeName])) {
			return $this->routes[$routeName];
		}

		return false;
	}

	/**
	 * Returns all defined routes definitions paths
	 *
	 * @return array
	 */
	public function getRoutes() {
		return $this->routes;
	}

	/**
	 * Returns path to directory
	 *
	 * @param string $directory directory identifier
	 * @return array|bool
	 */
	public function getDirectory($directory) {
		if(isset($this->directories[$directory])) {
			return $this->directories[$directory];
		}

		return false;
	}

	/**
	 * Returns all directory paths
	 * 
	 * @return array
	 */
	public function getDirectories() {
		return $this->directories;
	}

	/**
	 * Returns path to locale definitions
	 * 
	 * @param string $locale locale identifier
	 * @return array|bool
	 */
	public function getLocale($locale) {
		if(isset($this->locales[$locale])) {
			return $this->locales[$locale];
		}

		return false;
	}

	/**
	 * Returns all defined locale definition paths
	 * 
	 * @return array
	 */
	public function getLocales() {
		return $this->locales;
	}

	/**
	 * Returns path to listener definition
	 *
	 * @param string $listener definition identifier
	 * @return array|bool
	 */
	public function getListener($listener) {
		if(isset($this->listeners[$listener])) {
			return $this->listeners[$listener];
		}

		return false;
	}

	/**
	 * Returns all listener definition paths
	 * @return array
	 */
	public function getListeners() {
		return $this->listeners;
	}
}