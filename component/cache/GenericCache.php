<?php
namespace component\cache;

use \lib\component\CacheInterface;
use \lib\component\PersistentInterface;

use \lib\Config;

/**
 * Simple XML cache
 *
 * @throws \LengthException|\OutOfRangeException|\RuntimeException
 * @package Moss Cache
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
class GenericCache implements CacheInterface, PersistentInterface {

	protected $path;
	protected $registry = 'cache_registry.xml';


	protected $hit = 0;
	protected $miss = 0;
	protected $cache = array();

	/**
	 * Constructor
	 * Creates cache instance
	 *
	 * @param \lib\Config $Config
	 */
	public function __construct(Config $Config) {
		$this->path = $Config->getDirectory('cache');
		$this->makePath($this->path);
		$this->registry = rtrim($this->path, '/').'/'.$this->registry;
	}

	/**
	 * Adds new element to cache
	 * If element identifier exists throws exception
	 *
	 * @throws \OutOfRangeException
	 * @param string $index element identifier
	 * @param mixed $value cached element
	 * @param int $ttl time to live in seconds
	 * @return GenericCache
	 */
	public function add($index, $value = null, $ttl = 0) {
		if($this->exists($index)) {
			throw new \OutOfRangeException('Index exists');
		}

		$this->store($index, $value, $ttl);
		return $this;
	}

	/**
	 * Stores element in cache
	 *
	 * @param string $index element identifier
	 * @param mixed $value cached element
	 * @param int $ttl time to live in seconds
	 * @return GenericCache
	 */
	public function store($index, $value = null, $ttl = 0) {
		$this->cache[$index] = array(
			'index' => $index,
			'file' => sprintf('%s/%s.cache', rtrim($this->path, '/'), $index),
			'value' => serialize($value),
			'ttl' => time() + (int) $ttl,
		);

		file_put_contents($this->cache[$index]['file'], $this->cache[$index]['value']);

		return $this;
	}

	/**
	 * Removes element from cache
	 *
	 * @throws \OutOfRangeException
	 * @param string $index element identifier
	 * @return GenericCache
	 */
	public function delete($index) {
		if(!$this->exists($index)) {
			throw new \OutOfRangeException('Index does not exists');
		}

		if(is_file($this->cache[$index]['file'])) {
			unlink($this->cache[$index]['file']);
		}
		
		unset($this->cache[$index]);

		return $this;
	}

	/**
	 * Retrieves element from cache
	 *
	 * @throws \OutOfRangeException|\RuntimeException
	 * @param string $index element identifier
	 * @return mixed
	 */
	public function fetch($index) {
		if(!$this->exists($index)) {
			$this->miss++;
			throw new \OutOfRangeException('Index does not exists');
		}

		$this->hit++;

		if($this->cache[$index]['value']) {
			return unserialize($this->cache[$index]['value']);
		}

		if(!is_file($this->cache[$index]['file'])) {
			throw new \RuntimeException(sprintf('Cached data for index %s does not exists', $index));
		}

		$this->cache[$index]['value'] = file_get_contents($this->cache[$index]['file']);

		return unserialize($this->cache[$index]['value']);
	}

	/**
	 * Checks if identifier exists in cache
	 *
	 * @param string $index element identifier
	 * @return bool
	 */
	public function exists($index) {
		return isset($this->cache[$index]);
	}

	/**
	 * Returns cache info
	 *
	 * @return array
	 */
	public function info() {
		$output = array('hit' => $this->hit, 'miss' => $this->miss, 'nodes' => array());
		foreach($this->cache as $node) {
			$output['nodes'][] = array(
				'index' => $node['index'],
				'path' => $node['file'],
				'ttl' => $node['ttl']
			);
		}

		return $output;
	}

	/**
	 * Removes all data from cache
	 *
	 * @return GenericCache
	 */
	public function clear() {
		$this->cache = array();
		return $this;
	}

	/**
	 * Reads data from cache
	 *
	 * @throws \DomainException|\LengthException
	 * @return GenericCache
	 */
	protected function read() {
		if(empty($this->registry)) {
			throw new \LengthException('File path not set');
		}

		if(!is_file($this->registry)) {
			$this->write();
		}

		$xml = new \SimpleXMLElement(file_get_contents($this->registry));

		$this->hit = (int) $xml->hit;
		$this->miss = (int) $xml->miss;

		foreach($xml->nodes->children() as $node) {
			if((int) $node->attributes()->ttl < time()) {
				continue;
			}

			$this->cache[(string) $node->attributes()->index] = array(
				'index' => (string) $node->attributes()->index,
				'file' => (string) $node->attributes()->file,
				'value' => null,
				'ttl' => (string) $node->attributes()->ttl,
			);
		}
		return $this;
	}

	/**
	 * Writes data into cache
	 *
	 * @return GenericCache
	 */
	protected function write() {
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><cache></cache>');

		$xml->addChild('hit', $this->hit);
		$xml->addChild('miss', $this->miss);

		$nodes = $xml->addChild('nodes');
		foreach($this->cache as $item) {
			$node = $nodes->addChild('node');
			$node->addAttribute('index', $item['index']);
			$node->addAttribute('file', $item['file']);
			$node->addAttribute('ttl', $item['ttl']);
		}

		$dom = new \DOMDocument('1.0');
		$dom->preserveWhiteSpace = false;
		$dom->loadXML($xml->asXML());
		$dom->formatOutput = true;
		$data = $dom->saveXML();

		file_put_contents($this->registry, $data);

		return $this;
	}

	/**
	 * Recovers object state
	 *
	 * @return void
	 */
	public function recover() {
		$this->read();
	}


	/**
	 * Saves object state
	 *
	 * @return void
	 */
	public function persist() {
		$this->write();
	}

	/**
	 * Creates defined directory if does not exists
	 *
	 * @param string $path path to directory
	 * @return mixed
	 * @throws \RuntimeException
	 */
	protected function makePath($path) {
		if(is_dir($path)) {
			return;
		}

		if(!mkdir($path, 0644, true)) {
			throw new \RuntimeException(sprintf('Unable to create cache dir %s', $path));
		}
	}
}
