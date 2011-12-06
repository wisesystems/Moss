<?php
class Twig_Extension_Text extends Twig_Extension {

	public function getFilters() {
		return array(
			'truncate' => new Twig_Filter_Function('twig_truncate_filter', array('needs_environment' => true)),
			'wordwrap' => new Twig_Filter_Function('twig_wordwrap_filter', array('needs_environment' => true)),
			'nl2br' => new Twig_Filter_Function('twig_nl2br_filter', array('pre_escape' => 'html', 'is_safe' => array('html'))),
			'ucfirst' => new Twig_Filter_Function('ucfirst', array('pre_escape' => 'html')),
			'regex_replace' => new Twig_Filter_Function('twig_regex_replace_filter', array('pre_escape' => 'html', 'is_safe' => array('html'))),
		);
	}

	public function getName() {
		return 'Text';
	}
}

function twig_nl2br_filter($value, $sep = '<br />') {
	return str_replace("\n", $sep."\n", $value);
}

function twig_ucfirst_filter($value) {
	return ucfirst($value);
}

function twig_regex_replace_filter($string, $search, $replace) {
	if(preg_match('!([a-zA-Z\s]+)$!s', $search, $match) && (strpos($match[1], 'e') !== false)) {
		$search = substr($search, 0, -strlen($match[1])) . preg_replace('![e\s]+!', '', $match[1]);
	}
	
	return preg_replace($search, $replace, $string);
}

if(function_exists('mb_get_info')) {
	function twig_truncate_filter(Twig_Environment $env, $value, $length = 30, $preserve = false, $separator = '...') {
		if(mb_strlen($value, $env->getCharset()) > $length) {
			if($preserve) {
				if(false !== ($breakpoint = mb_strpos($value, ' ', $length, $env->getCharset()))) {
					$length = $breakpoint;
				}
			}

			return mb_substr($value, 0, $length, $env->getCharset()) . $separator;
		}

		return $value;
	}

	function twig_wordwrap_filter(Twig_Environment $env, $value, $length = 80, $separator = "\n", $preserve = false) {
		$sentences = array();

		$previous = mb_regex_encoding();
		mb_regex_encoding($env->getCharset());

		$pieces = mb_split($separator, $value);
		mb_regex_encoding($previous);

		foreach ($pieces as $piece) {
			while(!$preserve && mb_strlen($piece, $env->getCharset()) > $length) {
				$sentences[] = mb_substr($piece, 0, $length, $env->getCharset());
				$piece = mb_substr($piece, $length, 2048, $env->getCharset());
			}

			$sentences[] = $piece;
		}

		return implode($separator, $sentences);
	}
} else {
	function twig_truncate_filter(Twig_Environment $env, $value, $length = 30, $preserve = false, $separator = '...') {
		if (strlen($value) > $length) {
			if ($preserve) {
				if (false !== ($breakpoint = strpos($value, ' ', $length))) {
					$length = $breakpoint;
				}
			}

			return substr($value, 0, $length) . $separator;
		}

		return $value;
	}

	function twig_wordwrap_filter(Twig_Environment $env, $value, $length = 80, $separator = "\n", $preserve = false) {
		return wordwrap($value, $length, $separator, !$preserve);
	}
}
