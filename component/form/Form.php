<?php
namespace component\form;

use \component\form\FormInterface;

use \component\form\Fieldset;
use \component\form\Element;

/**
 * Object oriented form representation
 * Form is represented as unordered list
 *
 * Notice: for XHTML compatible form set $xhtml property to true
 *
 * @package Moss Form
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Form extends Fieldset implements FormInterface {
	
	protected $xhtml = false;

	protected $action;
	protected $method;
	protected $enctype;

	/**
	 * Constructor
	 *
	 * @param string $action forms target action
	 * @param string $method forms sending method
	 * @param string $enctype  forms encoding type
	 */
	public function __construct($action, $method = 'post', $enctype = 'multipart/form-data') {
		$this->action = $action;
		$this->method = $method;
		$this->enctype = $enctype;

		parent::__construct();
	}

	/**
	 * Sets forms action
	 *
	 * @param string $action
	 * @return Form
	 */
	public function setAction($action) {
		$this->action = $action;
		return $this;
	}

	/**
	 * Sets forms sending method
	 *
	 * @param string $method
	 * @return Form
	 */
	public function setMethod($method) {
		$this->method = $method;
		return $this;
	}

	/**
	 * Sets forms encoding type
	 *
	 * @param string $enctype
	 * @return Form
	 */
	public function setEnctype($enctype) {
		$this->enctype = $enctype;
		return $this;
	}

	/**
	 * Renders entire form
	 *
	 * @return string
	 */
	public function render() {
		$DOM = new \DOMDocument();

		$form = $DOM->createElement('form');

		$form->setAttribute('action', $this->action);
		$form->setAttribute('method', $this->method);
		$form->setAttribute('enctype', $this->enctype);

		$fieldset = $DOM->createElement('fieldset');
		$fieldset->appendChild($this->renderFieldset($DOM, $fieldset));
		$form->appendChild($fieldset);
		$DOM->appendChild($form);

		$DOM->preserveWhiteSpace = false;
		$DOM->formatOutput = true;

		/* HTML formatting */
		$output = $DOM->saveXML();

		$output = substr($output, strpos($output, "\n")+1);
		if(!$this->xhtml) {
			$output = str_replace('/>', '>', $output);
		}
		$output = str_replace('  ', "\t", $output);

		return $output;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}
}
