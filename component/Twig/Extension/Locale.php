<?php
class Twig_Extension_Locale extends \Twig_Extension {

	/**
	 * @var \lib\locale\LocaleInterface
	 */
	protected $Locale;
	protected $leftDelimeter = '{{';
	protected $rightDelimeter = '}}';

	/**
	 * @param lib\locale\LocaleInterface $Locale
	 */
	public function __construct(\lib\locale\LocaleInterface $Locale) {
		$this->Locale = &$Locale;
	}

	/**
	 * @return array
	 */
	public function getFilters() {
		return array(
			'translate' => new \Twig_Filter_Method($this, 'Locale', array('is_safe' => array('html'))),
			'translateInserted' => new \Twig_Filter_Method($this, 'LocaleInserted', array('is_safe' => array('html')))
		);
	}

	/**
	 * @return array
	 */
	public function getFunctions() {
		return array(
			'Locale' => new \Twig_Function_Method($this, 'Locale')
		);
	}

	/**
	 * Translates dictionary nodes in passed string
	 * Nodes are represented as {{ node }}
	 *
	 * @param string $string
	 * @return string
	 */
	public function LocaleInserted($string) {
		$regexp = '/'.preg_quote($this->leftDelimeter).' *([\w\d._]+) *'.preg_quote($this->rightDelimeter).'/ims';
		preg_match_all($regexp, $string, $words);

		foreach($words[1] as &$word) {
			$word = $this->Locale($word);
			unset($word);
		}

		return str_replace($words[0], $words[1], $string);
	}

	/**
	 * Translates passed word to corresponding definition from locale xml
	 * additional arguments in pairs
	 *
	 * @param string $string
	 * @return string
	 */
	public function Locale($string) {
		$arguments = array();
		
		if(func_num_args() > 1 && (func_num_args() - 1) % 2 == 0) {
			for($i = 1; $i < func_num_args(); $i+=2) {
				if(is_array(func_get_arg($i))) {
					$vars = func_get_arg($i);
				}
				else {
					$vars = explode(':', func_get_arg($i));
					if(count($vars) !== 2) {
						continue;
					}
				}

				$arguments[$vars[0]] = $vars[1];
			}
		}

		return $this->Locale->get($string, $arguments);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'Locale';
	}
}