<?php
namespace lib\response;
use \lib\response\ResponseInterface;

/**
 * Abstract response entity
 *
 * @package Moss Response
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
abstract class Response implements ResponseInterface {

	protected $status;
	protected $header;
	protected $content;

	/**
	 * Sets response content
	 *
	 * @param string $content
	 */
	public function __construct($content = null) {
		$this->content = $content ? $content : $this->content;
	}

	/**
	 * Sets response content
	 *
	 * @param string $content
	 * @return Response
	 */
	public function content($content) {
		$this->content = $content;

		return $this;
	}

	/**
	 * Returns response status
	 *
	 * @return int
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * Returns response header
	 *
	 * @return string
	 */
	public function getHeader() {
		return $this->header;
	}

	/**
	 * Return response content
	 *
	 * @return mixed
	 */
	public function getContent() {
		return $this->content;
	}
}