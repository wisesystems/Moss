<?php
namespace lib\model;

use \lib\model\PaginableInterface;

/**
 * Abstract paginable model prototype
 *
 * @package Moss Model
 * @author  Michal Wachowski <wachowski.michal@gmail.com>
 */
abstract class PaginablePrototype implements PaginableInterface {

	protected $elements;

	/**
	 * Returns generated array with paging data
	 *
	 * @param int $page current page
	 * @param int $limit elements per page
	 * @param array $preserve names of url variables to preserve
	 * @param int $sidepages number of pages next to curren
	 * @return array
	 */
	public function getPager($page = 1, $limit = 10, $preserve = array(), $sidepages = 2) {
		if(!$this->elements) {
			$this->elements = $this->getCount();
		}

		$arguments = array();
		foreach($preserve as $node) {
			if(!isset($this->Container->getComponent('Request')->query->$node)) {
				continue;
			}

			$arguments[$node] = $this->Container->getComponent('Request')->query->$node;
		};

		 $output = array(
			 'count' => $this->elements,
			 'limit' => (int) $limit,
			 'page' => $page > ceil($this->elements / $limit) || $page < 1 ? 1 : (int) $page,
			 'offset' => 0,
			 'pages' => (int) ceil($this->elements / $limit),
		 );

		 $output['offset'] = ($output['page'] - 1) * $limit;
		 $output['from'] = ($output['page'] - 1) * $limit;
		 $output['to'] = $output['page'] * $limit;

		if($output['page'] - 1 >= 1) {
			 $output['prev'] = array(
				'name' => $output['page'] - 1,
				'url' => $this->Container->getComponent('Router')->make('', array_merge($arguments, array('page' => $output['page'] - 1)))
			 );
		}

		if($output['page'] + 1 <= $output['pages']) {
			 $output['next'] = array(
				 'name' => $output['page'],
				 'url' =>  $this->Container->getComponent('Router')->make('', array_merge($arguments, array('page' => $output['page'] + 1)))
			);
		}

		if($output['page'] * 2 - 1 > $output['pages']) {
			 $output['first'] = array(
				'name' => 1,
				'url' =>  $this->Container->getComponent('Router')->make('', array_merge($arguments, array('page' => 1)))
			 );
		}

		if($output['page'] * 2 + 1 <= $output['pages']) {
			 $output['last'] = array(
				'name' => $output['pages'],
				'url' => $this->Container->getComponent('Router')->make('', array_merge($arguments, array('page' => $output['pages'])))
			 );
		}

		 $start = $page - $sidepages;
		 $end = $page + $sidepages;

		 if($start < 1) {
			 $end = $end + 1 - $start;
			 $start = 1;
		 }

		 if($end > $output['pages']) {
			 $end = $output['pages'];
		 }

		 $output['list'] = array();
		 for($i = $start; $i <= $end; $i++) {
			 $output['list'][] = array(
				'name' => $i,
		 		'url' => $this->Container->getComponent('Router')->make('', array_merge($arguments, array('page' => $i)))
		 	);
		 }

		 return $output;
	 }
}
