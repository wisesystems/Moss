<?php
namespace lib\response;
use \lib\response\ResponseInterface;
use \lib\response\Response;

/**
 * 403 Access forbidden response
 *
 * @package Moss Response
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Response403 extends Response implements ResponseInterface {

	protected $status = 403;
	protected $header = 'Content-Type: text/plain; charset=UTF-8';
	protected $content = 'Access forbidden';

	/**
	 * Converts response content to string and sends headers
	 *
	 * @return null|string
	 */
	public function __toString() {
		if(isset($_SERVER['SERVER_PROTOCOL'])) {
			header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden', true, 403);
		}
		
		header($this->header);
		return $this->content;
	}
}
