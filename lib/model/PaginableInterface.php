<?php
namespace lib\model;

use \lib\model\CountableInterface;

/**
 * Paginable interface
 * Models implementing this interface allow for splitting returned data into pages
 *
 * @package Moss Model
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
interface PaginableInterface extends CountableInterface {

	/**
	 * Retrieves results page
	 *
	 * @abstract
	 * @param int $page page to retrieve
	 * @param int $limit elements per page
	 * @return \lib\entity\Collection
	 */
	public function getPage($page = 1, $limit = 10);

	/**
	 * Returns generated array with paging data
	 *
	 * @abstract
	 * @param int $page current page
	 * @param int $limit elements per page
	 * @param array $preserve names of url variables to preserve
	 * @param int $sidepages number of pages next to current
	 * @return array
	 */
	public function getPager($page = 1, $limit = 10, $preserve = array(), $sidepages = 2);
}
