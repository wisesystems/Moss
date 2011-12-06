<?php
namespace lib\model;

use \lib\Container;
use \lib\entity\EntityInterface;
use \lib\entity\CollectionInterface;

/**
 * Model interface
 *
 * @package Moss Model
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface ModelInterface {

	/**
	 * Creates model instance
	 *
	 * @abstract
	 * @param \lib\Container $Container
	 */
	public function __construct(Container $Container);

	/**
	 * Resets model
	 *
	 * @abstract
	 * @return \lib\model\ModelInterface
	 */
	public function reset();
}
