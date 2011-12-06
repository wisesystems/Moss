<?php
namespace component\core;

use \lib\Container;
use \lib\RouteDefinition;

use \lib\storage\OneRelation;

use \cms\storage\CategoryStorage;
use \cms\storage\ArticleStorage;
use \cms\storage\ProjectStorage;

class RouteGenerator {

	/**
	 * @var \lib\Container
	 */
	protected $Container;

	/**
	 * @var \lib\Router
	 */
	protected $Router;

	/**
	 * @var \cms\storage\CategoryStorage
	 */
	protected $CategoryStorage;

	/**
	 * @var \cms\storage\ArticleStorage
	 */
	protected $ArticleStorage;

	/**
	 * @var \cms\storage\ProjectStorage
	 */
	protected $ProjectStorage;

	public function __construct(\lib\Container $Container, \lib\Router $Router) {
		$this->Container = &$Container;
		$this->Router = &$Router;

		$this->CategoryStorage = new CategoryStorage( $this->Container->getComponent('DatabaseAdapter') );
		$this->ArticleStorage = new ArticleStorage( $this->Container->getComponent('DatabaseAdapter') );
		$this->ProjectStorage = new ProjectStorage( $this->Container->getComponent('DatabaseAdapter') );
	}

	public function build() {
		$this->buildArticles();
		$this->buildProject();
		$this->buildCategories();
	}

	protected function buildCategories() {
		$Collection = $this->CategoryStorage
			->reset()
			->read()
			->execute()
		;

		foreach($Collection as $Entity) {
			if($Entity->get('controller') == 'pl:app:front:MainController:index') {
				continue;
			}

			if($Entity->get('controller') == 'pl:cms:front:ArticleController:index') {
				// TODO - blog relations
				// first page
				$this->Router->register(
					new RouteDefinition(
						null,
						sprintf('/blog/', $Entity->get('slug')),
						$Entity->get('controller')
					)
				);
				// other pages
				$this->Router->register(
					new RouteDefinition(
						null,
						sprintf('/blog/{page:\d}/', $Entity->get('slug')),
						$Entity->get('controller')
					)
				);
			}
			elseif($Entity->get('controller') == 'pl:cms:front:ArticleController:search') {
				// first page
				$this->Router->register(
					new RouteDefinition(
						null,
						sprintf('/%s/{text:\w}/', $Entity->get('slug')),
						$Entity->get('controller')
					)
				);
				// other pages
				$this->Router->register(
					new RouteDefinition(
						null,
						sprintf('/%s/{text:\w}/{page:\d}/', $Entity->get('slug')),
						$Entity->get('controller')
					)
				);
			}
			else {
				// first page
				$this->Router->register(
					new RouteDefinition(
						null,
						sprintf('/%s/', $Entity->get('slug')),
						$Entity->get('controller')
					)
				);
				// other pages
				$this->Router->register(
					new RouteDefinition(
						null,
						sprintf('/%s/{page:\d}/', $Entity->get('slug')),
						$Entity->get('controller')
					)
				);
			}
		}
	}

	protected function buildArticles() {
		$Collection = $this->ArticleStorage
			->read()
			->relation( new OneRelation( $this->CategoryStorage, 'Category', array('category_id'), array('id') ))
			->execute()
		;

		foreach($Collection as $Entity) {
			if($Entity->get('controller') == 'pl:cms:front:ArticleController:index') {
				// TODO - blog relations
				$this->Router->register(
					new RouteDefinition(
						null,
						sprintf('/%s/%s.html', $Entity->Category->get('slug'), $Entity->get('slug')),
						$Entity->Category->get('controller'),
						array(
							'id' => $Entity->identify()
						)
					)
				);
			}
			else {
				$this->Router->register(
					new RouteDefinition(
						null,
						sprintf('/%s/%s.html', $Entity->Category->get('slug'), $Entity->get('slug')),
						$Entity->Category->get('controller'),
						array(
							'id' => $Entity->identify()
						)
					)
				);
			}
		}
	}

	protected function buildProject() {
		$Category = $this->CategoryStorage->reset()->condition('controller', 'pl:cms:front:ProjectController:index')->readOne()->execute();
		$Collection = $this->ProjectStorage
			->read()
			->execute()
		;

		foreach($Collection as $Entity) {
			$this->Router->register(
				new RouteDefinition(
					null,
					sprintf('/%s/%s.html', $Category->get('slug'), $Entity->get('slug')),
					$Category->get('controller'),
					array(
						'id' => $Entity->identify()
					)
				)
			);
		}
	}
}
