<?php
namespace lib\response;
use \lib\response\ResponseInterface;
use \lib\response\Response;

/**
 * 401 Unauthorized response
 *
 * @package Moss Response
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Response401 extends Response implements ResponseInterface {

	protected $status = 401;
	protected $header = 'Content-Type: text/plain; charset=UTF-8';
	protected $content = 'Unauthorized';

	/**
	 * Converts response content to string and sends headers
	 *
	 * @return null|string
	 */
	public function __toString() {
		if(isset($_SERVER['SERVER_PROTOCOL'])) {
			header($_SERVER['SERVER_PROTOCOL'].' 401 Forbidden', true, 401);
		}
		
		header($this->header);
		return $this->content;
	}
}
