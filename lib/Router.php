<?php
namespace lib;

use \lib\Request;
use \lib\RouteDefinition;

/**
 * Router
 * Responsible for matching Request to route and URI creation
 *
 * @throws \DomainException|\LengthException|\OutOfBoundsException|\RangeException
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Router {

	protected $baseName;
	protected $controller;

	protected $normal = false;
	protected $direct = true;

	protected $namespaceController = 'controller';
	protected $namespaceSeparator = '\\';
	protected $moduleSeparator = ':';

	protected $routes = array();

	/**
	 * Reads route definitions from XML file
	 *
	 * @throws \DomainException|\LengthException
	 * @param string $path
	 * @return Router
	 */
	public function readXML($path) {
		if(empty($path)) {
			throw new \LengthException('File path not set');
		}

		if(!is_file($path)) {
			throw new \DomainException(sprintf('XML file (%s) not found!', $path));
		}

		$xml = new \SimpleXMLElement(file_get_contents($path));

		if(!isset($xml->route)) {
			throw new \DomainException(sprintf('Incorrect file format (missing nodes) in %s!', $path));
		}

		foreach($xml as $route) {
			$this->register(new RouteDefinition(
				(string) $route->attributes()->domain,
				(string) $route->attributes()->pattern,
				(string) $route->attributes()->controller,
				(array) $route->arguments
			));
		}

		return $this;
	}

	/**
	 * Registers route definition into routing table
	 *
	 * @param RouteDefinition $RouteDefinition
	 * @return Router
	 */
	public function register(RouteDefinition $RouteDefinition) {
		if(!isset($this->routes[$RouteDefinition->identifier])) {
			$this->routes[$RouteDefinition->identifier] = array();
		}

		$this->routes[$RouteDefinition->identifier][] = $RouteDefinition;

		return $this;
	}

	/**
	 * Matches request to route
	 * Throws RangeException if no matching route found
	 *
	 * @param Request $Request
	 * @return Router
	 * @throws \RangeException
	 */
	public function match(Request $Request) {
		if(!empty($Request->identifier)) {
			$Request->identifier = $this->resolveComponentPath($Request, $Request->identifier);
			$this->retrieveRequest($Request);
			return $this;
		}

		$Route = null;
		foreach($this->routes as $block) {
			foreach($block as $Definition) {
				if(preg_match($Definition->regexp, $Request->url) && preg_match($Definition->domain, $Request->domain)) {
					$Route = $Definition;
					break;
				}
			}

			if($Route) {
				break;
			}
		}

		if(!$Route) {
			throw new \RangeException('Route '.$Request->self.' not found!');
		}

		$Request->identifier = $Route->identifier;
		$this->resolveComponentPath($Request, $Route->identifier);

		preg_match_all($Route->regexp, $Request->url, $match, PREG_SET_ORDER);
		if(isset($match[0]) && !empty($match[0])) {
			foreach(array_keys($Route->arguments) as $argument) {
				if(isset($match[0][$argument])) {
					$Route->arguments[$argument] = $match[0][$argument];
				}
			}
		}

		$Request->query = (object) array_merge($Route->arguments, (array) $Request->query);
		$this->retrieveRequest($Request);

		foreach($Request->query as $key => $value) {
			$_GET[$key] = $value;
		}

		return $this;
	}

	/**
	 * Makes link
	 * If corresponding route exists - friendly link is generated, otherwise normal
	 *
	 * @param null|string $identifier controller identifier, if null request controller is used
	 * @param array $arguments additional arguments
	 * @param bool $normal if true forces normal link
	 * @param bool $direct if true forces direct link
	 * @return string
	 * @throws \OutOfBoundsException
	 */
	public function make($identifier = null, $arguments = array(), $normal = false, $direct = false) {
		$arguments = $arguments && is_array($arguments) ? $arguments : array();

		$kArr = array();
		$vArr = array();
		$qArr = array();

		try {
			if($this->normal || $normal) {
				throw new \OutOfBoundsException('Forced to generate the normal address');
			}

			$Route = null;
			if(isset($this->routes[$identifier])) {
				foreach($this->routes[$identifier] as $Route) {

					if($Route->match($identifier, $arguments)) {
						break;
					}
				}
			}
			else {
				throw new \OutOfBoundsException('Route not found, fall back to normal address');
			}

			$arguments = array_merge($Route->arguments, $arguments);

			foreach($arguments as $argName => $argValue) {
				if(strpos($Route->pattern, ':'.$argName) !== false) {
					$kArr[] = ':'.$argName;
					$vArr[] = ':'.$argName == $argValue ? null : $this->strip($argValue);
				}
				elseif(!empty($argValue) && (!isset($Route->arguments[$argName]) || $Route->arguments[$argName] != $argValue)) {
					$qArr[$argName] = $this->strip($argValue);
				}
			}

			$url = str_replace($kArr, $vArr, $Route->pattern);
			$url = str_replace('//', '/', $url);

			if(!empty($qArr)) {
				$url .= '?'.http_build_query($qArr, null, '&');
			}
		}
		catch(\OutOfBoundsException $e) {
			$url =  '?'.http_build_query(array_merge(array('controller' => str_replace(array(':', '/', '\\'), '_', $identifier)), $arguments), null, '&');
		}

		if(!$this->direct && !$direct) {
			$url = (strpos($url, '?') === 0 ? null : '.').$url;
		}
		else {
			$url = $this->baseName.ltrim($url, '/');
		}

		return $url;
	}

	/**
	 * Resets routing table
	 *
	 * @return Router
	 */
	public function reset() {
		$this->routes = array();

		return $this;
	}

	/**
	 * Resolves component path from controller identifier
	 *
	 * @param Request $Request
	 * @param $identifier
	 */
	protected function resolveComponentPath(Request $Request, $identifier) {
		preg_match_all('/^((?P<lang>[a-z]{2}):)?(?P<controller>.+):(?P<action>[0-9a-z_]+)?$/i', $identifier, $matches, PREG_SET_ORDER);

		$Request->lang = isset($matches[0]['lang']) && $matches[0]['lang'] ? $matches[0]['lang'] : $Request->lang;

		$Request->controller = isset($matches[0]['controller']) && $matches[0]['controller'] ? $matches[0]['controller'] : $Request->controller;
		$Request->controller = ltrim($Request->controller, $this->moduleSeparator);

		$Request->action = isset($matches[0]['action']) && $matches[0]['action'] ? $matches[0]['action'] : $Request->action;

		if($modPos = strpos($Request->controller, $this->moduleSeparator)) {
			$module = substr($Request->controller, 0, $modPos);
			$Request->controller = $module.$this->namespaceSeparator.$this->namespaceController.$this->namespaceSeparator.substr($Request->controller, $modPos+strlen($this->moduleSeparator));
		}

		$Request->controller = str_replace(array('\\', '/', ':'), $this->namespaceSeparator, $Request->controller);
	}

	/**
	 * Retrieves request data for further routing
	 *
	 * @param Request $Request
	 */
	protected function retrieveRequest(Request $Request) {
		$this->baseName = $Request->baseName;
		$this->controller = $Request->identifier;
		$this->controller = str_replace($this->namespaceSeparator.$this->namespaceController, null, $this->controller);
		$this->controller = str_replace($this->namespaceSeparator, $this->moduleSeparator, $this->controller);
	}

	/**
	 * Strips string from non ASCII chars
	 *
	 * @param string $urlString string to strip
	 * @param string $separator char replacing non ASCII chars
	 * @return string
	 */
	protected function strip($urlString, $separator = '-') {
		$urlString = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $urlString);
		$urlString = strtolower($urlString);
		$urlString = preg_replace('#[^\w\. \-]+#i', null, $urlString);
		$urlString = preg_replace('/[ -]+/', $separator, $urlString);
		$urlString = trim($urlString, '-.');

		return $urlString;
	}
}