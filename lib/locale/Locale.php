<?php
namespace lib\locale;

use \lib\locale\LocaleInterface;

/**
 * Localisation prototype
 * Uses xml files for translations
 *
 * @throws \InvalidArgumentException|\LengthException
 * @package Moss Model
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Locale implements LocaleInterface {

	protected $paths;

	public $lang;
	public $words;

	/**
	 * Creates locale instance
	 *
	 * @throws \InvalidArgumentException
	 * @param \lib\Request $Request
	 * @param \lib\Config $Config
	 */
	public function __construct(\lib\Request $Request, \lib\Config $Config) {
		$this->lang = $Request->lang;

		$this->paths = array();
		foreach($Config->getLocales() as $lang => $locales) {
			if($lang != $this->lang) {
				continue;
			}

			$this->paths = array_merge($this->paths, (array) $locales);
		}

		if(empty($this->paths)) {
			throw new \InvalidArgumentException(sprintf('Undefined locale %s', $this->lang));
		}

		foreach($this->paths as $path) {
			$this->readXML($path);
		}
	}

	/**
	 * Reads locale definition from XML
	 *
	 * @throws \InvalidArgumentException|\LengthException|\OutOfRangeException
	 * @param string $path path to XML file
	 * @return Locale
	 */
	public function readXML($path) {
		if(empty($path)) {
			throw new \LengthException('File path not set');
		}

		if(!is_file($path)) {
			throw new \InvalidArgumentException(sprintf('XML file (%s) not found!', $path));
		}

		$xml = new \SimpleXMLElement(file_get_contents($path));
/*
		if(!isset($xml->word)) {
			throw new \OutOfRangeException(sprintf('Incorrect file format (missing nodes)!', $path));
		}
*/
		$namespace = $xml->attributes()->namespace ? $xml->attributes()->namespace.'.' : null;
		$this->readXMLNodes($xml, $namespace);

		return $this;
	}

	/**
	 * Reads nodes from container
	 *
	 * @param \SimpleXMLElement $xml container to read from
	 * @param string $namespace current namespace
	 * @return void
	 */
	protected function readXMLNodes(\SimpleXMLElement $xml, $namespace = null) {
		foreach($xml->children() as $node) {
			if($node->getName() == 'word') {
				$this->words[ $namespace . (string) $node->attributes()->id ] = html_entity_decode((string) $node);
			}
			else {
				if($node->children()) {
					$this->readXMLNodes($node, $namespace.$node->getName().'.');
				}
			}
		}
	}

	/**
	 * Checks if localized word exists
	 *
	 * @param string $string word identifier
	 * @return bool
	 */
	public function exists($string) {
		return isset($this->words[$string]);
	}

	/**
	 * Retrieves localized word for passed identifier
	 * If args not empty - localized word will be parsed for data insertion (e.g. {foo} will be replaced with corresponding args[foo] value)
	 *
	 * @param string $string word identifier
	 * @param array $args data insertion variables
	 * @return string
	 */
	public function get($string, $args = array()) {
		if(!$this->exists($string)) {
			$this->words[$string] = sprintf('undefined %s', $string);
		}

		if(!empty($args)) {
			return $this->parse($this->words[$string], $args);
		}

		return $this->words[$string];
	}

	/**
	 * Parses localized word for data insertion
	 *
	 * @param string $string word definition
	 * @param array $args data insertion variables
	 * @return string
	 */
	protected function parse($string, $args = array()) {
		return call_user_func_array('sprintf', array_merge(array($string), $args));
	}
}