<?php
namespace lib\response;
use \lib\response\ResponseInterface;
use \lib\response\Response;

/**
 * Custom content response
 * Content type is determined on passed headers
 *
 * @package Moss Response
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class ResponseCustom extends Response implements ResponseInterface {

	protected $status = 200;
	protected $header;
	protected $content;

	/**
	 * Creates custom response instance
	 *
	 * @param string $content response content
	 * @param string$header response header
	 */
	public function __construct($content, $header) {
		$this->content = $content;
		$this->header = $header;
	}

	/**
	 * Converts response content to string and sends headers
	 *
	 * @return null|string
	 */
	public function __toString() {
		if(isset($_SERVER['SERVER_PROTOCOL'])) {
			header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
		}
		
		header($this->header);
		return $this->content;
	}
}
