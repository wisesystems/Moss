<?php
namespace lib\response;
use \lib\response\ResponseInterface;
use \lib\response\Response;

/**
 * JSON Response
 * Converts contents into JSON
 *
 * @package Moss Security
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class ResponseJSON extends Response implements ResponseInterface {

	protected $status = 200;
	protected $header = 'Content-type: application/json; charset=UTF-8';
	protected $content = 'HTML IS OK';

	/**
	 * Sets response content
	 *
	 * @param string $content
	 */
	public function __construct($content) {
		$this->content = json_encode($content);
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
