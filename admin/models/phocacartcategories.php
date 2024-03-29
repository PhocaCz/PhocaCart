<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Factory;
jimport('joomla.application.component.modellist');

class PhocaCartCpModelPhocaCartCategories extends ListModel
{
	protected $option 	= 'com_phocacart';
	protected $total		= 0;
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'category_id', 'category_id',
				'access', 'a.access', 'access_level',
				'count_products', 'a.count_products',
				'ordering', 'a.ordering',
				'language', 'a.language',
				'hits', 'a.hits',
				'published','a.published',
				'parentcat_title', 'parentcat_title',
				'featured', 'a.featured'
			);

			// ASSOCIATION
			if (Associations::isEnabled()){
				$config['filter_fields'][] = 'association';
			}
		}
		parent::__construct($config);
	}

	protected function populateState($ordering = 'a.title', $direction = 'ASC')
	{
		// Initialise variables.
		$app = Factory::getApplication('administrator');

		// ASSOCIATION
		$forcedLanguage = $app->input->getCmd('forcedLanguage');
		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout')) {
			$this->context .= '.' . $layout;
		}
		// Adjust the context to support forced languages.
		if ($forcedLanguage){
			$this->context .= '.' . $forcedLanguage;
		}

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$accessId = $app->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);

		$state = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '', 'string');
		$this->setState('filter.published', $state);
		// Not used in SQL - used in view in recursive category tree function
		$levels = $app->getUserStateFromRequest($this->context.'.filter.level', 'filter_level', '', 'string');
		$this->setState('filter.level', $levels);

		$categoryId = $app->getUserStateFromRequest($this->context.'.filter.parent_id', 'filter_parent_id', null);
		$this->setState('filter.parent_id', $categoryId);

		$language = $app->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// Load the parameters.
		$params = PhocacartUtils::getComponentParameters();
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);

		// ASSOCIATION
		if (!empty($forcedLanguage)) {
			$this->setState('filter.language', $forcedLanguage);
		}
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.category_id');
		$id .= ':'.$this->getState('filter.language');
		$id	.= ':'.$this->getState('filter.category_id');

		return parent::getStoreId($id);
	}

	private function buildCategoryTree(array &$items, array $categories, int $level = 1, string $treeTitle = '', string $parentsTree = ''): void {
		foreach ($categories as $idx => $category) {
			$title = ($treeTitle ? $treeTitle . ' - ' : '') . $category->title;
			$category->level = $level;
			$category->title = $title;
			$category->orderup = $idx > 0;
			$category->orderdown = $idx < count($categories);
			$category->parentstree = ($parentsTree ? $parentsTree . ' ' : '') . $category->id;
			$items[] = $category;
			if ($category->children)
				$this->buildCategoryTree($items, $category->children, $level + 1, $title, $category->parentstree);
		}
	}
	/*
	 * Because of tree we need to load all the items
	 *
	 * We need to load all items because of creating tree
	 * After creating tree we get info from pagination
	 * and will set displaying of categories for current pagination
	 * E.g. pagination is limitstart 5, limit 5 - so only categories from 5 to 10 will be displayed (in Default.php)
	 */
	public function getItems()
	{
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (!empty($this->cache[$store])) {
			return $this->cache[$store];
		}

		// Load the list items.
		try {
			$this->getDbo()->setQuery($this->getListQuery());
			$categories	= $this->getDbo()->loadObjectList('id');
		} catch (RuntimeException $e) {
			throw new Exception($e->getMessage(), 500);
		}

		if ($this->getState('filter.search')) {
			$items = $categories;
		} else {
			array_walk($categories, function ($category) use ($categories) {
				if ($category->parent_id) {
					if ($categories[$category->parent_id]->children === null)
						$categories[$category->parent_id]->children = [];
					$categories[$category->parent_id]->children[] = $category;
				}
			});

			$rootCategories = array_filter($categories, function ($category) {
				return !$category->parent_id;
			});

			$items = [];
			$this->buildCategoryTree($items, $rootCategories);

			if ($level = $this->getState('filter.level')) {
				$items = array_filter($items, function ($category) use ($level) {
					return $category->level <= $level;
				});

				$this->setTotal(count($items));
			}
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		$columns	= 'a.id, a.title, a.parent_id, a.alias, a.ordering, a.access, a.count, a.checked_out, a.hits, a.params, ' .
			'a.image, a.description, a.published, a.checked_out_time, a.language, a.count_products, a.count_date, a.featured, ' .
			'null AS children, 0 AS orderup, 0 AS orderdown, 1 AS level, a.id AS parentstree, a.title AS title_self, 0 AS groupname';

		// Select the required fields from the table.
		$query->select($this->getState('list.select', $columns));
		$query->from('`#__phocacart_categories` AS a');

		// Join over the language
    $query->select('l.title AS language_title, l.image AS language_image');
		$query->join('LEFT', '`#__languages` AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the categories.
		$query->select('c.title AS parentcat_title, c.id AS parentcat_id');
		$query->join('LEFT', '#__phocacart_categories AS c ON c.id = a.parent_id');

		$query->select('cc.countid AS countid');
		$query->join('LEFT', '(SELECT cc.parent_id, COUNT(*) AS countid'
		. ' FROM #__phocacart_categories AS cc'
		.' GROUP BY cc.parent_id ) AS cc'
		.' ON a.parent_id = cc.parent_id');

		// ASSOCIATION
		// Join over the associations.
		if (Associations::isEnabled()) {
			$subQuery = $db->getQuery(true)
				->select('COUNT(' . $db->quoteName('asso2.id') . ')')
				->from($db->quoteName('#__associations', 'asso'))
				->join('LEFT', $db->quoteName('#__associations', 'asso2') . ' ON ' . $db->quoteName('asso2.key') . ' = ' . $db->quoteName('asso.key'))
				->where($db->quoteName('asso.id') . ' = ' . $db->quoteName('a.id'))
				->where($db->quoteName('asso.context') . ' = ' . $db->quote('com_phocacart.category'));

			$query->select('(' . $subQuery . ') AS ' . $db->quoteName('association'));
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = '.(int) $access);
		}

		// Filter by published state.
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = '.(int) $published);
		}
		else if ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}

		// Filter by category.
		$categoryId = $this->getState('filter.parent_id');
		if (is_numeric($categoryId)) {
			$query->where('a.parent_id = ' . (int) $categoryId);
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language')) {
			$query->where('a.language = ' . $db->quote($language));
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%'.$db->escape($search, true).'%');
				$query->where('( a.title LIKE '.$search.' OR a.alias LIKE '.$search.')');
			}
		}

		//- $query->group($groups);

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'title');
		$orderDirn	= $this->state->get('list.direction', 'asc');
		/*if ($orderCol == 'a.ordering' || $orderCol == 'parentcat_title') {
			$orderCol = 'parentcat_title '.$orderDirn.', a.ordering';
		}*/
		$query->order($db->escape($orderCol.' '.$orderDirn));

		//echo nl2br(str_replace('#__', 'jos_', $query->__toString()));


		return $query;
	}

	public function getTotal() {
		$store = $this->getStoreId('getTotal');
		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		// PHOCAEDIT
		if (isset($this->total) && (int)$this->total > 0) {
			$total = (int)$this->total;
		} else {
			$query = $this->_getListQuery();

			try {
				$total = (int) $this->_getListCount($query);
			}
			catch (RuntimeException $e) {
				$this->setError($e->getMessage());

				return false;
			}
		}

		$this->cache[$store] = $total;
		return $this->cache[$store];
	}

	public function setTotal($total) {
		// When we use new total and new pagination, we need to clean their cache
		$store1 = $this->getStoreId('getTotal');
		$store2 = $this->getStoreId('getStart');
		$store3 = $this->getStoreId('getPagination');

		unset($this->cache[$store1]);
		unset($this->cache[$store2]);
		unset($this->cache[$store3]);
		$this->total = (int)$total;
	}
}
?>
