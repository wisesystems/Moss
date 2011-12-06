<?php
class Twig_Loader_Viewsystem implements Twig_LoaderInterface {

	protected $moduleSeparator = ':';
	protected $includePath;
	protected $namespaces = array();

	public function __construct($namespaces) {
		foreach((array) $namespaces as $namespace => $path) {
			$this->addNamespace($namespace, $path);
		}
	}

	protected function addNamespace($namespace, $path = null) {
		if(isset($this->namespaces[(string)$namespace])) {
			throw new \DomainException('The namespace '.$namespace.' is already added.');
		}

		$length = strlen($path);
		if($length == 0 || $path[$length - 1] != '/') {
			$path .= '/';
		}

		$this->namespaces[(string)$namespace] = realpath($path);
	}

	public function getSource($name) {
		return file_get_contents($this->findTemplate($name));
	}

	public function getCacheKey($name) {
		return $this->findTemplate($name);
	}

	public function isFresh($name, $time) {
		return filemtime($this->findTemplate($name)) < $time;
	}

	protected function findTemplate($viewName) {
		if($modPos = strpos($viewName, $this->moduleSeparator)) {
			$module = substr($viewName, 0, $modPos);

			$viewName = rtrim($this->namespaces[$module], '/').'/'.$module.'/view/'.str_replace(':', '/', substr($viewName, $modPos+strlen($this->moduleSeparator)));
		}

		$viewName .= '.twig';
		$dirName = null;

		if(false !== ($lastNsPos = strripos($viewName, '\\'))) {
			$dirName = substr($viewName, 0, $lastNsPos);
			$viewName = substr($viewName, $lastNsPos + 1);
			$viewName = str_replace('\\', '/', $dirName).'/'.$viewName;
		}

		$viewName = str_replace('_', '/', $viewName);

		return ($this->includePath !== null ? $this->includePath.'/' : null).$viewName;
	}
}
