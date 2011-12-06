<?php
/**
 * Efficiency tester
 *
 * @author Michal Wachowski <michal.wachowski@gmail.com>
 */
class StressTest {

	protected $type = null;
	protected $url;
	protected $succeed = 0;
	protected $failed = 0;
	protected $redirect_count = 0;
	protected $total_time = 0;
	protected $namelookup_time = 0;
	protected $connect_time = 0;
	protected $pretransfer_time = 0;
	protected $size_upload = 0;
	protected $size_download = 0;
	protected $speed_upload = 0;
	protected $speed_download = 0;
	protected $upload_content_length = 0;
	protected $download_content_length = 0;
	protected $starttransfer_time = 0;
	protected $redirect_time = 0;

	/**
	 * Creates object instance
	 * @param string $url tested url
	 */
	public function __construct($url) {
		$this->url = $url;
	}

	/**
	 * Checks address strength.
	 * Sends specified number of requests at set interval, without waiting for previous requests
	 *
	 * @param int $probes number of probes
	 * @param float $delay send delay in seconds
	 * @return StressTest
	 */
	public function horde($probes, $delay = 0.01) {
		$pArr = array();
		$delay = $delay * 1000000;
		$handle = curl_multi_init();

		for($i = 0; $i < $probes; $i++) {
			$pArr[$i] = $this->buildProbe();
			curl_multi_add_handle($handle, $pArr[$i]);
		}

		$active = NULL;
		do {
			usleep($delay);
			curl_multi_exec($handle, $active);
		} while($active > 0);

		for($i = 0; $i < $probes; $i++) {
			$this->gatherStats(curl_getinfo($pArr[$i]));
		}

		$this->calculateStats($probes);

		$this->type = sprintf('Horde: time required to handle %u request (delay %.2Fs)', $probes, $delay/1000000);

		return $this;
	}

	/**
	 * Checks address responsiveness
	 * For specified time sends probes, one per time
	 *
	 * @param int $limit time limit in seconds
	 * @return StressTest
	 */
	public function siege($limit = 1) {
		$pArr = array();
		$probe = $this->buildProbe();

		$start = microtime(true);
		while(microtime(true) < $start+$limit) {
			curl_exec($probe);
			$pArr[] = curl_getinfo($probe);
		}

		foreach($pArr as $data) {
			$this->gatherStats($data);
		}

		$this->calculateStats(count($pArr));

		$this->type = sprintf('Siege: maximum number of requests in %u second(s)', $limit);

		return $this;
	}

	/**
	 * Retrieves current statistics
	 *
	 * @return array
	 */
	public function retrieve() {
		return get_object_vars($this);
	}

	/**
	 * Resets current statistics
	 *
	 * @return StressTest
	 */
	public function reset() {
		foreach(get_object_vars($this) as $field => $value) {
			if(is_numeric($value)) {
				$this->$field = 0;
			}
		}

		return $this;
	}

	/**
	 * Creates single probe
	 * @return resource
	 */
	protected function buildProbe() {
		$probe = curl_init($this->url);

		curl_setopt($probe, CURLOPT_NOBODY, false);
		curl_setopt($probe, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($probe, CURLOPT_HEADER, true);
		curl_setopt($probe, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($probe, CURLOPT_ENCODING, 'gzip,deflate');

		if(isset($_SERVER['HTTP_USER_AGENT'])) {
			curl_setopt($probe, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		}

		return $probe;
	}

	/**
	 * Gathers data from response
	 *
	 * @param array $data response data
	 * @return void
	 */
	protected function gatherStats($data) {
		if(!isset($data['http_code'])) {
			return;
		}
		
		$data['http_code'] >= 200 && $data['http_code'] < 300 ? $this->succeed++ : $this->failed++;

		foreach($data as $field => $value) {
			if(!property_exists($this, $field) || !is_numeric($value)) {
				continue;
			}

			$this->$field += $value;
		}
	}

	/**
	 * Calculates average values
	 *
	 * @param int $probes number of probes
	 * @return void
	 */
	protected function calculateStats($probes) {
		foreach(get_object_vars($this) as $field => $value) {
			if(is_float($value)) {
				$this->$field = (float) number_format($value / $probes, 4);
			}
		}
	}
}


$Stress = new StressTest('http://localhost/!repo/web/stresstest.html');
$siege = $Stress->reset()->siege(1)->retrieve();
$horde = $Stress->reset()->horde(10)->retrieve();

var_dump($siege);
var_dump($horde);