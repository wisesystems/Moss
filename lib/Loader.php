<?php
namespace lib;

/**
 * Moss autoload handlers
 * Supports standard SPL and mapped autoloading handlers
 *
 * @throws \DomainException
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Loader {

	protected $namespaces;
	
	protected $map;
	protected $mapCacheFile = '../cache/loader.cache';
	protected $mapIgnoredDirectories = '/^.*[\/\\\](cache|compile)[\/\\\].*$/i';

	/**
	 * Creates Loader instance
	 *
	 * @param null|string $namespace
	 * @param null|string $path path to directory containing files
	 */
	public function __construct($namespace = null, $path = null) {
		$this->addNamespace($namespace, $path);
	}

	/**
	 * Registers namespace in loader
	 *
	 * @throws \DomainException
	 * @param string $namespace
	 * @param null|string $path path to directory containing files
	 * @return void
	 */
	public function addNamespace($namespace, $path = null) {
		if(isset($this->namespaces[(string)$namespace])) {
			throw new \DomainException('The namespace '.$namespace.' is already added.');
		}

		$length = strlen($path);
		if($length == 0 || $path[$length - 1] != '/') {
			$path .= '/';
		}

		$this->namespaces[(string)$namespace] = realpath($path);

		krsort($this->namespaces);
	}

	/**
	 * Recovers loader map
	 * If map not exists builds it
	 *
	 * @abstract
	 * @return Loader
	 */
	public function recover() {
		if(is_file($this->mapCacheFile)) {
			$this->map = unserialize(file_get_contents($this->mapCacheFile));
		}
		else {
			$this
				->gather()
				->persist()
			;

		}

		return $this;
	}

	/**
	 * Saves Loader map
	 *
	 * @abstract
	 * @return Loader
	 */
	public function persist() {
		file_put_contents($this->mapCacheFile, serialize($this->map));

		return $this;
	}

	/**
	 * Gathers files from directories
	 *
	 * @return Loader
	 */
	protected function gather() {
		$RecursiveIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->namespaces['']));

		foreach($RecursiveIterator as $item) {
			if(!$this->isValid($item) || !$name = $this->identify((string) $item)) {
				continue;
			}

			$this->map[ltrim($name, '\\')] = (string) $item;
		}

		return $this;
	}

	/**
	 * Checks if file is valid
	 * Valid file has .php extension and is not in ignored directories
	 *
	 * @param \SplFileInfo $file
	 * @return bool
	 */
	protected function isValid(\SplFileInfo $file) {
		if(!$file->isFile()) {
			return false;
		}

		if(!preg_match('/^.*\.php$/', (string) $file)) {
			return false;
		}

		if($this->mapIgnoredDirectories && preg_match($this->mapIgnoredDirectories, (string) $file)) {
			return false;
		}

		return true;
	}

	/**
	 * Identifies namespace and interface/class declaration in file
	 *
	 * @param $file
	 * @return bool|null|string
	 */
	protected function identify($file) {
		$content = file_get_contents($file, null, null, 0, 1024);

		preg_match_all('/^namespace (.+);/im', $content, $nsMatches);

		preg_match_all('/^(abstract )?(interface|class) ([^ \n{]+).*$/im', $content, $nameMatches);

		if(!isset($nameMatches[3][0]) || empty($nameMatches[3][0])) {
			return false;
		}

		if(empty($nsMatches[1][0])) {
			return $nameMatches[3][0];
		}

		return '\\'.$nsMatches[1][0].'\\'.$nameMatches[3][0];
	}

	/**
	 * Registers loader mapper
	 *
	 * @return void
	 */
	public function registerMapper() {
		$this->recover();
		spl_autoload_register(array($this, 'mapper'));
	}

	/**
	 * Unregisters loader mapper
	 *
	 * @return void
	 */
	public function unregisterMapper() {
		spl_autoload_unregister(array($this, 'mapper'));
	}

	/**
	 * Handles mapper autoload calls
	 *
	 * @param string $className
	 * @return bool
	 */
	public function mapper($className) {
		if(isset($this->map[$className])) {
			return require $this->map[$className];
		}

		return false;
	}

	/**
	 * Registers loader handler
	 *
	 * @return void
	 */
	public function registerHandle() {
		spl_autoload_register(array($this, 'handler'));
	}

	/**
	 * Unregisters loader handler
	 *
	 * @return void
	 */
	public function unregisterHandle() {
		spl_autoload_unregister(array($this, 'handler'));
	}

	/**
	 * Handles autoload calls
	 *
	 * @param string $className
	 * @return bool
	 */
	public function handler($className) {
		if(strpos($className, '_') !== false) {
			$className = str_replace('_', '\\', $className);
		}

		foreach($this->namespaces as $namespace => $path) {
			if($namespace && $namespace.'\\' !== substr($className, 0, strlen($namespace.'\\'))) {
				continue;
			}

			$fileName = '';

			if(false !== ($lastNsPos = strripos($className, '\\'))) {
				$namespace = substr($className, 0, $lastNsPos);
				$className = substr($className, $lastNsPos + 1);
				$fileName = str_replace('\\', '/', $namespace) . '/';
			}

			$fileName .= $className.'.php';
			$fileName = ($path !== null ? $path . '/' : '').$fileName;

			return require $fileName;
		}

		return false;
	}
}