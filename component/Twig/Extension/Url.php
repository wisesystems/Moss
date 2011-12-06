<?php
class Twig_Extension_Url extends \Twig_Extension {

	/**
	 * @var \lib\Router
	 */
	protected $Router;

	/**
	 * @param lib\Router $Router
	 */
	public function __construct(\lib\Router $Router) {
		$this->Router = &$Router;
	}

	/**
	 * @return array
	 */
	public function getFunctions() {
		return array(
			'Url' => new \Twig_Function_Method($this, 'Url'),
			'UrlPreserve' => new \Twig_Function_Method($this, 'UrlPreserve')
		);
	}

	/**
	 * Generates url from passed data
	 * Additional argument as name:value pairs
	 *
	 * @param null|string|array $identifier
	 * @param array $arguments
	 * @return string
	 */
	public function Url($identifier = null, $arguments = array()) {
		return $this->Router->make($identifier, $arguments);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'Url';
	}
}