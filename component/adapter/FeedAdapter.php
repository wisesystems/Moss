<?php
namespace component\adapter;

class FeedAdapter extends XMLAdapter {

	/**
	 * Reads data from associated XML
	 *
	 * @param string $container container name
	 * @return \SimpleXMLElement[]
	 */
	protected function readXML($container) {
		$curl = curl_init($this->path);
		curl_setopt($curl, CURLOPT_NOBODY, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip,deflate');
		$data = curl_exec($curl);

		curl_close($curl);
		
		$xml = new \SimpleXMLElement($data);

		$result = $xml->xpath(str_replace('_', '/', $container));

		return $result;
	}
}