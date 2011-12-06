<?php
namespace autodoc\controller;

use \lib\Controller;
use \lib\response\ResponseHTML;

use \component\core\Compressor;

/**
 * Generates documentation based on PHPDoc comments
 *
 * @package AutoDoc
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class AutodocController extends Controller {

	/**
	 * @var \lib\View
	 */
	protected $View;

	protected $files = array();
	protected $doc = array();
	protected $ignored = array(
		'../.',
		'../cache/',
		'../compile/',
		'../component/Twig/',
		'../web/',
	);

	/**
	 * Initializes Autodoc
	 * Prepares View and regular expression for ignored directories
	 *
	 * @return void
	 */
	public function init() {
		$this->View = $this->Container->getComponent('View');

		if(!empty($this->ignored)) {
			$disabled = $this->ignored;

			$this->ignored = '(';
			foreach($disabled as $dir) {
				if(!empty($dir)) {
					$dir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $dir);
					$this->ignored .= ''.preg_quote($dir).'|';
				}
			}

			$this->ignored[strlen($this->ignored) - 1] = ')';
			$this->ignored = '#^'.$this->ignored.'.*$#';
		}
	}

	/**
	 * Creates autodoc
	 *
	 * @return \lib\response\ResponseHTML
	 */
	public function index() {

		try {
			$autodocResponse = $this->Container->getComponent('Cache')->fetch('autodocResponse');
		}
		catch(\OutOfRangeException $e) {
			$this->gather();

			foreach($this->files as $file => $name) {
				include_once($file);
				$RefClass = new \ReflectionClass($name);

				$this->doc['\\'.$RefClass->getName()] = $this->parseClass($RefClass);

				foreach($RefClass->getMethods() as $method) {
					$RefMethod = new \ReflectionMethod($method->class.'::'.$method->name);
					$this->doc['\\'.$RefClass->getName()]['method'][$method->name] = $this->parseFunction($RefMethod, (string) $RefClass->getName());
				}

				if(isset($this->doc['\\'.$RefClass->getName()]['method'])) {
					ksort($this->doc['\\'.$RefClass->getName()]['method']);
				}
			}

			ksort($this->doc);

			foreach($this->doc as &$class) {
				foreach($class['method'] as &$method) {
					foreach($method['param'] as &$param) {
						$param['rel'] = $this->relation($param['type'], $class['namespace']);
						unset($param);
					}

					foreach($method['attributes'] as &$node) {
						foreach($node as &$param) {
							$param['rel'] = $this->relation($param['type'], $class['namespace']);
							unset($param);
						}
						unset($node);
					}

					unset($method);
				}
				unset($class);
			}

			$autodocResponseContent = $this->View
				->template('autodoc:autodoc')
			
				->css('normalize')
				->css('autodoc')

				->set('Doc', $this->doc)

				->render();
			;

			$autodocResponse = new ResponseHTML($autodocResponseContent);

			if($this->Container->checkComponent('Cache')) {
				$this->Container->getComponent('Cache')->store('autodocResponse', $autodocResponse, 86400);
			}
		}
		return new Compressor( $autodocResponse, true, true, true);
	}

	/**
	 * Gathers files from directories
	 *
	 * @return void
	 */
	protected function gather() {
		$RecursiveIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator('../'));

		foreach($RecursiveIterator as $item) {
			if(!$this->isValid($item)) {
				continue;
			}

			if(!$name = $this->identify((string) $item)) {
				continue;
			}

			$this->files[(string) $item] = $name;
		}
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

		if($this->ignored && preg_match($this->ignored, (string) $file)) {
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

		preg_match_all('/^(abstract )?(interface|class) ([^ ]+).*$/im', $content, $nameMatches);

		if(!empty($nameMatches[3][0])) {
			return empty($nsMatches[1][0]) ? null : '\\'.$nsMatches[1][0].'\\'.$nameMatches[3][0];
		}

		return false;
	}

	/**
	 * Parses class data
	 * Resolves description from phpDoc comment, if file contains abstract class or interface and parent class (if any)
	 *
	 * @param \ReflectionClass $RefClass
	 * @return array
	 */
	protected function parseClass(\ReflectionClass $RefClass) {

		$doc = array(
			'hash' => md5($RefClass->getName()),
			'namespace' => $RefClass->getNamespaceName(),
			'name' => '\\'.$RefClass->getName(),
			'desc' => null,
			'package' => null,
			'author' => null,
			'abstract' => $RefClass->isAbstract(),
			'interface' => $RefClass->isInterface(),
			'extends' => $RefClass->getParentClass() ? array('name' => $RefClass->getParentClass()->getName(), 'hash' => md5($RefClass->getParentClass()->getName())) : null,
			'method' => array()
		);

		$comment = $RefClass->getDocComment();
		$comment = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#', '$1', $comment);
		$comment = str_replace(array("\t", "\r", "\n"), array(null, null, ' '), $comment);
		$comment = str_replace('  ', ' ', $comment);
		$comment = trim($comment);

		$doc['desc'] = trim(preg_replace('/^([^@]+).*/i', '$1', $comment));

		preg_match_all('/@[^@]+/i', $comment, $matches);
		foreach($matches[0] as $lNo => $line) {
			$def = substr($line, 0, (int) strpos($line, ' '));

			switch($def) {
				case '@package':
					$doc['package'] = preg_replace('/^@package (.+)$/', '$1', trim($line));
					break;
				case '@author':
					$line = $line.(isset($matches[0][$lNo+1]) ? $matches[0][$lNo+1] : null);
					$doc['author'] = preg_replace('/^@author (.+)$/', '$1', trim($line));
					break;
			}
		}

		return $doc;
	}

	/**
	 * Parses class methods
	 * Resolves description from phpDoc comment, if method is abstract, static, its parameters and so on
	 *
	 * @param \ReflectionMethod $RefMethod
	 * @param null $className
	 * @return array
	 */
	protected function parseFunction(\ReflectionMethod $RefMethod, $className = null) {
		$doc = array(
			'hash' => md5($className.$RefMethod->getName()),
			'name' => $RefMethod->getName(),
			'desc' => null,
			'param' => array(),
			'public' => $RefMethod->isPublic(),
			'protected' => $RefMethod->isProtected(),
			'private' => $RefMethod->isPrivate(),
			'static' => $RefMethod->isStatic(),
			'abstract' => $RefMethod->isAbstract(),
			'attributes' => array(
				'throws' => array(),
				'param' => array(),
				'return' => array(),
			)
		);

		$comment = $RefMethod->getDocComment();

		if(trim($comment) == '') {
			return $doc;
		}

		$comment = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#', '$1', $comment);
		$comment = str_replace(array("\t", "\r", "\n"), array(null, null, ' '), $comment);
		$comment = str_replace('  ', ' ', $comment);
		$comment = trim($comment);

		$doc['desc'] = trim(preg_replace('/^([^@]+).*/i', '$1', $comment));

		foreach($RefMethod->getParameters() as $param) {
			$doc['param'][$param->getName()] = array(
				'name' => '$'.$param->getName(),
				'type' => null,
				'required' => !$param->isOptional(),
				'default' => $param->isOptional() ? $this->valueToString($param->getDefaultValue()) : null,
				'rel' => null
			);
		}

		preg_match_all('/@[^@]+/i', $comment, $matches);
		foreach($matches[0] as $line) {
			$def = substr($line, 0, (int) strpos($line, ' '));

			if($def == '@abstract' || $def == '@static') {
				continue;
			}

			switch($def) {
				case '@param':
					preg_match_all('/^@param ([^ ]+) ([^ ]+)(.*)$/', trim($line), $nodes);
					break;
				case '@throws':
					preg_match_all('/^@throws ([^ ]+)$/', trim($line), $nodes);
					break;
				case '@return':
					preg_match_all('/^@return ([^ ]+)$/', trim($line), $nodes);
					break;
			}

			$nodes = array(
				'param' => trim($def, '@'),
				'type' => isset($nodes[1][0]) ? trim(str_replace('|', ', ', $nodes[1][0])) : null,
				'name' => isset($nodes[2][0]) ? trim($nodes[2][0]) : null,
				'desc' => isset($nodes[3][0]) ? trim($nodes[3][0]) : null,
			);

			$name = trim($nodes['name'], '$');
			if($nodes['param'] == 'param') {
				$doc['param'][$name]['type'] = $nodes['type'];
			}

			$doc['attributes'][$nodes['param']][$name] = $nodes;
		}

		return $doc;
	}

	/**
	 * Converts "invisible" types to string representation
	 * E.g. false to 'false'
	 *
	 * @param $value
	 * @return string
	 */
	protected function valueToString($value) {
		if($value === true) {
			return 'true';
		}
		elseif($value === false) {
			return 'false';
		}
		elseif($value === null) {
			return 'null';
		}
		elseif($value == 'Array' || is_array($value)) {
			return 'array()';
		}

		return $value;
	}

	/**
	 * Retrieves relation to definition in doc for types
	 *
	 * @param string $type
	 * @param string $namespace
	 * @return null|string
	 */
	protected function relation($type, $namespace) {
		if(strpos($type, '\\') === false) {
			$type = '\\'.$namespace.'\\'.$type;
		}

		if(!isset($this->doc[$type])) {
			return null;
		}

		return $this->doc[$type]['hash'];
	}
}
