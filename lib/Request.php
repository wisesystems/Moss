<?php
namespace lib;

/**
 * Request representation
 *
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Request {
	public $xhr = false;
	public $method;
	public $sheme;
	public $domain;
	public $dir;
	public $baseName;
	public $clientIP;
	public $identifier;
	public $url;
	public $self;
	public $incorrectRedirect = false;

	public $lang;
	public $controller;
	public $action;

	public $query = array();

	/**
	 * Creates request instance
	 * Resolves request parameters
	 *
	 * @param null|string $identifier default controller identifier
	 */
	public function __construct($identifier = null) {
		$this->xhr = (bool) isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
		$this->method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
		$this->sheme = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : null;
		$this->domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
		$this->dir = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : null;

		if(!in_array($this->dir[strlen($this->dir) - 1], array('/', '\\'))) {
			$this->dir = str_replace('\\', '/', dirname($this->dir));
		}

		if(isset($_SERVER['REQUEST_URI'])) {
			$this->url = $this->dir == '/' ? $_SERVER['REQUEST_URI'] : str_replace($this->dir, null, $_SERVER['REQUEST_URI']);
			$this->url = '/'.trim($this->url, '/');

			$this->hostCorrection();

			$this->baseName = strtolower(substr($this->sheme, 0, strpos($this->sheme, '/'))).'://'.str_replace('//', '/', $this->domain.$this->dir.'/');
		}

		$this->clientIP = $this->getClientIP();

		$queryStart = strpos($this->url, '?');
		if($queryStart !== false) {
			$this->query = substr($this->url, $queryStart+1);
			$this->url = substr($this->url, 0, $queryStart);
		}

		if(!empty($this->query)) {
			parse_str($this->query, $this->query);
		}

		$this->self = rtrim($this->baseName,'/').$this->url.(!empty($this->query) ? '?'.http_build_query($this->query, null, '&') : null);

		$this->query = array_merge($this->query, $_GET);

		if(isset($GLOBALS['argc'], $GLOBALS['argv']) && $GLOBALS['argc'] > 1) {
			$this->url = $GLOBALS['argv'][1];

			for($i = 2; $i < $GLOBALS['argc']; $i++) {
				$arg = explode('=', $GLOBALS['argv'][$i]);
				$this->query[ltrim($arg[0], '--')] = isset($arg[1]) ? $arg[1] : null;
			}
		}

		$this->identifier = isset($this->query['controller']) ? str_replace('_', ':', $this->query['controller']) : $identifier;

		unset($this->query['controller']);

		$this->query = (object) $this->query;
	}

	/**
	 * For request outside /web/
	 * Some hosts do not allow include from outside domains directory
	 * If so - all request should have additional parameter incorrectRedirect = 1
	 */
	protected function hostCorrection() {
		if(!isset($_GET['incorrectRedirect']) || $_GET['incorrectRedirect'] != 1) {
			return;
		}

		$this->incorrectRedirect = true;
		unset($_GET['incorrectRedirect']);

		$pos = strpos($this->dir, '/web');

		if($pos !== false) {
			$this->dir = str_replace(substr($this->dir, $pos), null, $this->dir);
			$this->url = str_replace(substr($this->dir, 0, $pos), null, $this->url);
		}
	}

	/**
	 * Resolves request source IP
	 *
	 * @return null|string
	 */
	protected function getClientIP() {
		if(isset($_SERVER["REMOTE_ADDR"])) {
			return $_SERVER["REMOTE_ADDR"];
		}

		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			return $_SERVER["HTTP_X_FORWARDED_FOR"];
		}

		if(isset($_SERVER["HTTP_CLIENT_IP"])) {
			return $_SERVER["HTTP_CLIENT_IP"];
		}

		return null;
	}
}