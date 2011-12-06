<?php
class Twig_Extension_Number extends Twig_Extension {
	public function getFilters() {
		return array(
			'number' => new Twig_Filter_Function('twig_number_filter'),
			'currency' => new Twig_Filter_Function('twig_currency_filter'),
			'fraction' => new Twig_Filter_Function('twig_fraction_filter')
		);
	}

	public function getName() {
		return 'Number';
	}
}

function twig_number_filter($number, $decimals = 0, $dec_point = ',', $thousands_sep = ' ') {
	return number_format((float) $number, $decimals, $dec_point, $thousands_sep);
}

function twig_currency_filter($number, $decimals = 2, $dec_point = ',', $thousands_sep = ' ') {
	return number_format((float) $number, $decimals, $dec_point, $thousands_sep);
}

function twig_fraction_filter($fraction) {
	return str_replace(array('1/2', '1/4/', '3/4'), array('&frac12;', '&frac14;', '&frac34;'), $fraction);
}
