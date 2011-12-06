<?php
namespace lib\response;

/**
 * Response interface
 *
 * @package Moss Response
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ResponseInterface {

	/**
	 * Converts response content to string and sends headers
	 *
	 * @abstract
	 * @return string
	 */
	public function __toString();

	/**
	 * Sets response content
	 * 
	 * @abstract
	 * @param string $content response contents
	 * @return \lib\response\ResponseInterface
	 */
	public function content($content);

	/**
	 * Returns response status
	 *
	 * @return int
	 */
	public function getStatus();

	/**
	 * Returns response header
	 *
	 * @return string
	 */
	public function getHeader();

	/**
	 * Return response content
	 *
	 * @return mixed
	 */
	public function getContent();
}
