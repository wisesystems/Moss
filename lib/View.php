<?php
namespace lib;

use \lib\Container;

/**
 * Moss view
 * Uses Twig as template engine
 *
 * @throws \DomainException|\InvalidArgumentException|\OutOfRangeException
 * @package Moss Core
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class View {
	protected $reservedVars = array('Config', 'Request', 'css', 'js');
	protected $extensions = array();
	protected $compress = false;
	protected $moduleSeparator = '\\';

	protected $template;
	protected $vars = array('css' => array(), 'js' => array());

	protected $Container;

	/**
	 * Creates View instance
	 *
	 * @param string $path path to XML file
	 * @param Container $Container
	 */
	public function __construct($path, Container $Container) {
		$this->readXML($path);

		$this->Container = &$Container;
	}

	/**
	 * Reads view configuration from XML file
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

		if(!isset($xml->reserved) || !isset($xml->compress) || !isset($xml->extensions)) {
			throw new \DomainException(sprintf('Incorrect file format (missing nodes) in %s!', $path));
		}

		foreach($xml->reserved->children() as $node) {
			$this->reservedVars[] = (string) $node;
		}

		$this->compress = $xml->compress == 'true' || $xml->compress == 1;

		foreach($xml->extensions->children() as $node) {

			$arguments = array();
			if(isset($node->arguments)) {
				foreach($node->arguments->children() as $arg) {
					$arguments[] = array('type' => (string) $arg->attributes()->type, 'value' => (string) $arg);
				}
			}

			$this->extensions[(string) $node->attributes()->name] = $arguments;
		}

		return $this;
	}

	/**
	 * Assigns template to view
	 *
	 * @param string $template path to template (supports namespaces)
	 * @return View
	 */
	public function template($template) {
		$this->template = $template;

		return $this;
	}

	/**
	 * Sets variable to be used in template
	 *
	 * @throws \InvalidArgumentException
	 * @param string|array $param variable name, if array - its key will be used as variable names
	 * @param null|mixed $value variable value
	 * @return View
	 */
	public function set($param, $value = null) {
		if(is_string($param) && in_array($param, $this->reservedVars)) {
			throw new \InvalidArgumentException(sprintf('Variable name %s is reserved', $param));
		}

		if(is_array($param)) {
			foreach($param as $key => $val) {
				$this->set($key, $val);
				unset($val);
			}
		}
		else {
			$this->vars[$param] = $value;
		}
		return $this;
	}

	/**
	 * Retrieves variable value
	 *
	 * @throws \OutOfRangeException
	 * @param string $param variable name
	 * @return mixed
	 */
	public function get($param) {
		if(!isset($this->vars[$param])) {
			throw new \OutOfRangeException(sprintf('Variable %s does not exists', $param));
		}

		return $this->vars[$param];
	}

	/**
	 * Sets CSS file associated with view
	 *
	 * @param string $file path to file
	 * @param string $media
	 * @return View
	 */
	public function css($file, $media = 'screen') {
		$this->vars['css'][] = array('file' => $file, 'media' => $media);

		return $this;
	}

	/**
	 * Sets JS file associated with view
	 *
	 * @param string $file path to file
	 * @return View
	 */
	public function js($file) {
		$this->vars['js'][] = $file;

		return $this;
	}

	/**
	 * Renders view
	 *
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public function render() {
		if(!$this->template) {
			throw new \InvalidArgumentException('Undefined view or view file does not exists: '.$this->template.'!');
		}

		$this->vars['Request'] = $this->Container->getComponent('Request');
		$this->vars['Config'] = $this->Container->getComponent('Config');

		if($this->compress) {
			$this->vars['css'] = $this->rebuildCSS($this->vars['css']);
			$this->vars['js'] = $this->rebuildJS($this->vars['js']);
		}
		else {
			$this->vars['css'] = $this->resolvePath($this->vars['css'], $this->Container->getComponent('Config')->getDirectory('style'), 'css');
			$this->vars['js'] = $this->resolvePath($this->vars['js'], $this->Container->getComponent('Config')->getDirectory('script'), 'js');
		}

		$Twig = $this->Twig();

		$Template = $Twig->loadTemplate($this->template);
		return $Template->render($this->vars);
	}

	/**
	 * Creates Twig instance
	 * Loads defined extensions
	 *
	 * @return \Twig_Environment
	 */
	protected function &Twig() {
		\Twig_Autoloader::register();
		$Twig = new \Twig_Environment(
			new \Twig_Loader_Viewsystem($this->Container->getComponent('Config')->getNamespaces()),
			array(
			     'debug' => $this->Container->getComponent('Config')->isDebugMode(),
			     'auto_reload' => true,
			     'strict_variables' => false,
			     'cache' => $this->Container->getComponent('Config')->getDirectory('compile')
			)
		);

		$this->TwigLoadExtensions($Twig);

		return $Twig;
	}

	/**
	 * Loads defined Twig extensions
	 *
	 * @param \Twig_Environment $Twig
	 * @return void
	 */
	protected function TwigLoadExtensions($Twig) {
		foreach($this->extensions as $extension => $arguments) {
			$Twig->addExtension($this->TwigInitExtension($extension, $arguments));
		}
	}

	/**
	 * Creates Twig extension instance
	 * Functionality similar to dependency injection container
	 *
	 * @param string $className
	 * @param array $arguments
	 * @return \Twig_ExtensionInterface
	 */
	protected function &TwigInitExtension($className, $arguments = array()) {
		if(empty($arguments)) {
			$instance = new $className;
		}
		else {
			foreach($arguments as &$arg) {
				if(!is_array($arg)) {
					continue;
				}

				switch($arg['type']) {
					case 'container':
						$arg = $this->Container;
						break;
					case 'component':
						$arg = $this->Container->getComponent($arg['value']);
						break;
					case 'parameter':
						$arg = $this->Container->getParameter($arg['value']);
						break;
					default:
						$arg = $arg['value'];
				}
				unset($arg);
			}

			$ref = new \ReflectionClass($className);
			$instance = $ref->newInstanceArgs($arguments);
		}

		return $instance;
	}

	/**
	 * Resolves path to file
	 *
	 * @param array $iArr array containing files to resolve
	 * @param null|string $path path to files
	 * @param null|string $extension files extension
	 * @return array
	 */
	protected function resolvePath($iArr, $path = null, $extension = null) {
		/* Updates path if request from outside /web/ */
		if($this->Container->getComponent('Request')->incorrectRedirect) {
			$path = str_replace('./', './web/', $path);
		}

		foreach($iArr as &$node) {
			$file = &$node;
			if(is_array($node) && isset($node['file'])) {
				$file = &$node['file'];
			}

			if(!preg_match('/^(https?:)?\/\/.+/imU', $file)) {
				$file = sprintf('%s%s.%s', $path, str_replace(':', '/', $file), $extension);
			}

			unset($node, $file);
		}

		return $iArr;
	}

	/**
	 * Rebuilds paths and addresses for associated CSS files
	 *
	 * @param array $iArr
	 * @return array
	 */
	protected function rebuildCSS(Array $iArr) {
		$oArr = array();
		foreach($iArr as $node) {
			if(preg_match('/^(https?:)?\/\/.*/', $node['file'])) {
				$oArr[] = $node;
			}
			else {
				if(!isset($oArr[$node['media']])) {
					$oArr[$node['media']] = array('file' => null, 'media' => $node['media']);
				}

				$oArr[$node['media']]['file'][] = $node['file'];
			}
		}

		foreach($oArr as $key => $val) {
			if(empty($val)) {
				unset($oArr[$key]);
			}
		}

		foreach($oArr as $key => &$val) {
			if(!is_numeric($key) && is_array($val['file'])) {
				$val['file'] = '/style/?file='.str_replace('.css', null, implode(',', $val['file']));
			}

			unset($val);
		}

		return $oArr;
	}

	/**
	 * Rebuilds paths and addresses for associated JS files
	 *
	 * @param array $iArr
	 * @return array
	 */
	protected function rebuildJS($iArr) {
		$oArr = array('compress' => array());
		foreach($iArr as $node) {
			if(preg_match('/^(https?:)?\/\/.*/', $node)) {
				$oArr[] = $node;
			}
			else {
				$oArr['compress'][] = $node;
			}
		}

		foreach($oArr as $key => $val) {
			if(empty($val)) {
				unset($oArr[$key]);
			}
		}

		foreach($oArr as $key => &$val) {
			if(!is_numeric($key) && is_array($val)) {
				$val = '/script/?file='.str_replace('.js', null, implode(',', $val));
			}

			unset($val);
		}

		return $oArr;
	}
}