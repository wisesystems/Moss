<?php
namespace console\controller;

use \lib\ControllerInterface;

use \lib\Container;
use \lib\Config;
use \lib\Router;
use \lib\Request;

/**
 * MOSS Mapping controller
 * Allows for:
 * 	- entity and storage creation based on database tables
 * 	- database table creation based on storage structure
 *
 * @package Moss Console
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 *
 * @todo - databse from storage
 */
class Mapping implements ControllerInterface {

	/**
	 * @var \lib\Config|\lib\Container
	 */
	protected $Container;

	/**
	 * @var \lib\Config
	 */
	protected $Config;

	/**
	 * @var \lib\Router
	 */
	protected $Rotuter;

	/**
	 * @var \lib\Request
	 */
	protected $Request;

	/**
	 * @var \lib\storage\AdapterInterface
	 */
	protected $Adapter;

	/**
	 * Constructor, calls init function
	 *
	 * @param \lib\Container $Container
	 * @param \lib\Config $Config
	 * @param \lib\Router $Router
	 * @param \lib\Request $Request
	 */
	public function __construct(Container $Container, Config $Config, Router $Router, Request $Request) {
		$this->Container = &$Container;
		$this->Config = &$Config;
		$this->Rotuter = &$Router;
		$this->Request = &$Request;
		
		$this->Adapter = $this->Container->getComponent('ImportAdapter');
	}

	/**
	 * Method for initialisation operations
	 * Called at the end of constructor
	 *
	 * @return void
	 */
	public function init() {
		// TODO: Implement init() method.
	}

	/**
	 * Controllers default action
	 *
	 * @return ResponseInterface
	 */
	public function index() {
		// TODO: Implement index() method.
	}

	/**
	 * Creates Entity and Storage objects in given namespace based on database structure
	 *
	 * @return ResponseInterface
	 */
	public function import() {
		if(!isset($this->Request->query->namespace)) {
			throw new \InvalidArgumentException('Console: namespace not defined');
		}

		$response = array();
		$regExpFilter = null
		;
		if(isset($this->Request->query->filter)) {
			$regExpFilter = sprintf('/^(%s)$/', implode('|', (array) $this->Request->query->filter));
		}

		$Collection = $this->Adapter->tables()->execute(null, 'console\entity\Table', 'lib\entity\Collection');
		foreach($Collection as $Entity) {
			if($regExpFilter && !preg_match($regExpFilter, $Entity->identify())) {
				continue;
			}

			$Entity->prefix(isset($this->Request->query->prefix) ? $this->Request->query->prefix : $this->Request->query->namespace);

			$Entity->Field = $this->Adapter
				->describe()
				->execute($Entity->identify(), 'console\Entity\Field', 'lib\entity\Collection')
			;

			$Entity->buildEntity(
				$this->Container->getComponent('View'),
				$this->Request->query->namespace
			);

			$Entity->buildStorage(
				$this->Container->getComponent('View'),
				$this->Request->query->namespace
			);

			$Entity->buildModel(
				$this->Container->getComponent('View'),
				$this->Request->query->namespace
			);

			file_put_contents($this->makePath($this->Request->query->namespace, 'entity', $Entity->identifyEntity().'.php'), $Entity->get('Entity'));
			file_put_contents($this->makePath($this->Request->query->namespace, 'storage', $Entity->identifyStorage().'.php'), $Entity->get('Storage'));
			file_put_contents($this->makePath($this->Request->query->namespace, 'model', $Entity->identifyModel().'.php'), $Entity->get('Model'));

			$response[] = $Entity->identify().' OK';
		}

		return new \lib\response\Response200(empty($response) ? 'FAIL' : implode("\n", $response));
	}

	public function makePath($namespace, $suffix, $file) {
		$path = trim('../component/', '\\/').'/'.trim($namespace, '\\/').'/'.trim($suffix, '\\/').'/';

		if(!is_dir($path)) {
			if(!mkdir($path, null, true)) {
				throw new \RuntimeException(sprintf('Could not create directory %s', $path));
			}
		}

		return $path.$file;
	}
}
