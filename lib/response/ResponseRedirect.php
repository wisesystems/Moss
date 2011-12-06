<?php
namespace lib\response;
use \lib\response\ResponseInterface;
use \lib\response\Response;

/**
 * Redirecting Response
 * Response redirects (Status 302) client to given address
 *
 * @package Moss Response
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class ResponseRedirect extends Response implements ResponseInterface {

	protected $status = 302;
	protected $header = 'Content-Type: text/plain; charset=UTF-8';
	protected $delay;
	protected $address;
	protected $content = 'Redirecting...';

	/**
	 * Constructor
	 * Sets redirection address, delay and response content
	 *
	 * @param string $address redirection address
	 * @param int $delay redirection delay in seconds
	 * @param string $content response content
	 */
	public function __construct($address, $delay = 0, $content = null) {
		$this->address($address);
		$this->delay($delay);

		if($content) {
			$this->content = $content;
		}
	}

	/**
	 * Converts response content to string and sends headers
	 *
	 * @throws \LengthException
	 * @return string
	 */
	public function __toString() {
		if(headers_sent()) {
			return '<script type="text/javascript" language="javascript">setTimeout("window.location.href = \''.$this->address.'\'", '.($this->delay * 1000).');</script>'.$this->content;
		}

		if($this->delay) {
			header('Refresh: '.$this->delay.'; URL='.$this->address);
		}
		else {
			header('Location: '.$this->address);
		}

		return $this->content;
	}

	/**
	 * Sets redirection delay
	 *
	 * @param int $delay redirection delay in seconds
	 * @return ResponseRedirect
	 */
	public function delay($delay) {
		$this->delay = (int) $delay;
		return $this;
	}

	/**
	 * Sets redirection address
	 *
	 * @param string $address redirection address
	 * @return ResponseRedirect
	 */
	public function address($address) {
		$this->address = str_replace('&amp;', '&', $address);
		return $this;
	}
}
