<?php
namespace lib\response;
use \lib\response\ResponseInterface;
use \lib\response\Response;

/**
 * Server error response
 * Should be only used when further action is not possible
 *
 * @package Moss Response
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Response500 extends Response implements ResponseInterface {

	protected $status = 500;
	protected $header = 'Content-Type: text/plain; charset=UTF-8';
	protected $content = 'Internal Server Error';

	/**
	 * Converts response content to string and sends headers
	 *
	 * @return null|string
	 */
	public function __toString() {
		if(isset($_SERVER['SERVER_PROTOCOL'])) {
			header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
		}
		
		header($this->header);
		return $this->content;
	}
}
