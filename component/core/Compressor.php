<?php
namespace component\core;

use \lib\response\Response;
use \lib\response\ResponseInterface;

/**
 * Compressor
 * Response compressor
 * Allow for minimalize response content and gzip compression
 *
 * @package Moss Core Component
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class Compressor extends Response implements ResponseInterface {

	protected $status;
	protected $header;
	protected $content;

	protected $removeNewLine = false;
	protected $removeTab = false;
	protected $removeComment = true;
	protected $ignoredTags = array('script', 'pre', 'textarea');
	protected $marker = '@@@TAG::%s@@@';
	protected $tags;

	/**
	 * Creates compressed response instance
	 * Compressor acts as response which will compress itself when sent
	 *
	 * @param \lib\response\ResponseInterface $Response
	 * @param bool $removeNewLine if true, removes new line chars
	 * @param bool $removeTab if true, trims tab chars
	 * @param bool $removeComment if true, removes comments
	 */
	public function __construct(\lib\response\ResponseInterface $Response, $removeNewLine = false, $removeTab = false, $removeComment = false) {
		$this->status = $Response->getStatus();
		$this->header = $Response->getHeader();
		$this->content = $Response->getContent();
		$this->removeNewLine = (bool) $removeNewLine;
		$this->removeTab = (bool) $removeTab;
	}

	/**
	 * Compresses request content if zlib/gzip is supported
	 * Else - no changes are made
	 *
	 * @return string
	 */
	public function __toString() {
		if($this->removeNewLine || $this->removeTab || $this->removeComment) {
			$this->content = $this->pullTags($this->content);

			if($this->removeTab) {
				$this->content = $this->removeTab($this->content);
			}

			if($this->removeNewLine) {
				$this->content = $this->removeNewLine($this->content);
			}

			if($this->removeComment) {
				$this->content = $this->removeComment($this->content);
			}

			$this->content = trim($this->content);

			$this->content = $this->putTags($this->content);
		}

		if(isset($_SERVER['SERVER_PROTOCOL'])) {
			switch($this->status) {
				case 200:
					header($_SERVER['SERVER_PROTOCOL'].' 200 OK', true, 200);
				break;
				case 403:
					header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden', true, 403);
				break;
				case 404:
					header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found', true, 404);
				break;
				case 500:
					header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
				break;
			}
		}
		header($this->header);

		if(!extension_loaded("zlib") || get_cfg_var('zlib.output_compression') || !isset($_SERVER['HTTP_ACCEPT_ENCODING']) || strpos($_SERVER["HTTP_ACCEPT_ENCODING"], 'gzip') === false || headers_sent()) {
			return $this->content;
		}

		$this->content = gzencode($this->content, 9);
		header('Content-Encoding: gzip');
		header('Content-Length: '.strlen($this->content));

		return $this->content;
	}

	/**
	 * Removes tab (\t) from response content
	 * Preserves tabs in formatted tags e.g. <pre>
	 *
	 * @param string $response
	 * @return string
	 */
	protected function removeTab($response) {
		$response = preg_replace('/ {2,}/imu', ' ', $response);
		return str_replace("\t", null, $response);
	}

	/**
	 * Removes new line (\r, \n) from response content
	 * Preserves new line chars in formatted tags e.g. <pre>
	 *
	 * @param string $response
	 * @return string
	 */
	protected function removeNewLine($response) {
		return str_replace(array("\n", "\r"), null, $response);
	}

	/**
	 * Removes comment from response content
	 *
	 * @param string $response
	 * @return string
	 */
	protected function removeComment($response) {
		$type = trim(preg_replace('/^.*Content-Type\:([^;]+).*$/i', '$1', $this->header));

		switch($type) {
			case 'text/html':
			case 'text/xml':
				$response = preg_replace('/<!--[^[].*-->/imsu', null, $response);
				break;
			case 'text/javascript':
				$response = preg_replace('/\/\/.*$/imU', null, $response);
			case 'text/css':
				$response = preg_replace('/\/\*.*\*\//imsU', null, $response);
				break;
		}

		return $response;
	}

	/**
	 * Pulls ignored tags from response and replaces them with marker
	 *
	 * @param string $response
	 * @return string
	 */
	protected function pullTags($response) {
		foreach($this->ignoredTags as $tag) {
			$regexp = (sprintf('#<%1$s[^>]*>(.*?)</%1$s>#ims', $tag));

			preg_match_all($regexp, $response, $match);
			$this->tags[$tag] = $match[0];

			$response = preg_replace($regexp, sprintf($this->marker, $tag), $response);
		}

		return $response;
	}

	/**
	 * Puts tags back in place
	 *
	 * @param string $response
	 * @return string
	 */
	protected function putTags($response) {
		foreach($this->ignoredTags as $tag) {
			$len = strlen(sprintf($this->marker, $tag));
			$pos = 0;
			for($i = 0, $count = count($this->tags[$tag]); $i < $count; $i++) {
				if(($pos = strpos($response, sprintf($this->marker, $tag), $pos)) !== false) {
					$response = substr_replace($response, $this->tags[$tag][$i], $pos, $len);
				}
				else {
					break;
				}
			}
		}

		return $response;
	}
}
