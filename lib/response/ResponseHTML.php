<?php
namespace lib\response;
use \lib\response\ResponseInterface;
use \lib\response\Response;

/**
 * HTML Response
 *
 * @package Moss Response
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class ResponseHTML extends Response implements ResponseInterface {

	protected $status = 200;
	protected $header = 'Content-Type: text/html; charset=UTF-8';
	protected $content = 'HTML IS OK';

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
