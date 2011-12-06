<?php
namespace lib\response;
use \lib\response\ResponseInterface;
use \lib\response\Response;

/**
 * 404 Not found response
 *
 * @package Moss Response
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Response404 extends Response implements ResponseInterface {

	protected $status = 404;
	protected $header = 'Content-Type: text/html; charset=UTF-8';
	protected $content = 'Not found';

	/**
	 * Converts response content to string and sends headers
	 *
	 * @return null|string
	 */
	public function __toString() {
		if(isset($_SERVER['SERVER_PROTOCOL'])) {
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', true, 404);
		}
		
		header($this->header);
		return $this->content;
	}
}
