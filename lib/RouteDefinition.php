<?php
namespace lib;

/**
 * Route definition, represents route for Router
 *
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class RouteDefinition {
	public $domain;
	public $pattern;
	public $identifier;
	public $arguments;
	public $required;
	public $regexp;

	/**
	 * Creates route definition instance
	 *
	 * @param null|string $domain domain filter, if null fits all domains
	 * @param string $pattern route pattern, can include arguments e.g. /foo/{var:\d}/ where variable var is numeric
	 * @param string $identifier controller identifier
	 * @param array $arguments route arguments
	 */
	public function __construct($domain, $pattern, $identifier, $arguments = array()) {
		$this->domain = '/^'.(!empty($domain) ? $domain : '.*').'$/';
		$this->pattern = $pattern;
		$this->identifier = $identifier;
		$this->arguments = (array) $arguments;
		$this->required = array();

		preg_match_all('#{([a-z]+):([^\}]+)?}#i', $this->pattern, $match);

		$match[3] = $match[2];
		foreach(array_keys($match[1]) as $key) {
			if(!isset($this->arguments[$match[1][$key]])) {
				$this->arguments[$match[1][$key]] = null;
			}

			$this->required[$match[1][$key]] = '*';

			$match[3][$key] = sprintf('#%s#', $match[1][$key]);
			$match[2][$key] = sprintf('(?P<%s>[%s]+)', $match[1][$key], $match[2][$key]);
			$match[1][$key] = sprintf(':%s', $match[1][$key]);

			unset($pattern);
		}

		if(empty($this->required)) {
			$this->required = $this->arguments;
		}

		$regexp = $this->pattern;

		$this->pattern = str_replace($match[0], $match[1], $this->pattern);
			
		$regexp = str_replace($match[0], $match[3], $regexp);
		$regexp = preg_quote($regexp, '/');
		$regexp = str_replace($match[3], $match[2], $regexp);

		if(!empty($regexp)) {
			$regexp .= '?';
		}

		$this->regexp = '/^'.$regexp.'$/i';
	}

	/**
	 * Checks if controller and arguments match route definition
	 *
	 * @param string $controller controller identifier
	 * @param array $arguments route arguments
	 * @return bool
	 */
	public function match($controller, $arguments = array()) {
		if($this->identifier !== $controller) {
			return false;
		}

		$arg = array();
		$req = array();
		foreach($this->required as $node => $value) {
			$req[$node] = $value == '*' && array_key_exists($node, $arguments) ? $arguments[$node] : $value;
			$arg[$node] = array_key_exists($node, $arguments) ? $arguments[$node] : null;
		}

		if(!empty($this->required) && $arg == $req) {
			return true;
		}

		return false;
	}
}