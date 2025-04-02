<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

    use Joomla\CMS\Form\Form;
    use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
    use Phoca\PhocaCart\Dispatcher\Dispatcher;
    use Phoca\PhocaCart\I18n\I18nHelper;
use Phoca\PhocaCart\I18n\I18nListModelTrait;

class PhocaCartCpModelPhocaCartCategories extends ListModel
{
    use I18nListModelTrait;

	protected $option 	= 'com_phocacart';

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
				'featured', 'a.featured',
                'parent_id', 'a.parent_id',
                'category_type', 'a.category_type'
			);

			// ASSOCIATION
			if (I18nHelper::associationsEnabled()){
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

		$categoryId = $app->getUserStateFromRequest($this->context.'.filter.parent_id', 'filter_parent_id');
		$this->setState('filter.parent_id', $categoryId);

		$language = $app->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

        $categoryType = $app->getUserStateFromRequest($this->context.'.filter.category_type', 'filter_category_type');
        $this->setState('filter.category_type', $categoryType);

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
		$id	.= ':'.$this->getState('filter.parent_id');
        $id	.= ':'.$this->getState('filter.category_type');
		$id .= ':'.$this->getState('filter.language');
		$id	.= ':'.$this->getState('filter.level');

		return parent::getStoreId($id);
	}

	private function buildCategoryTree(array &$items, array $categories, int $level = 1, string $treeTitle = '', array $parents = []): void {
		foreach ($categories as $idx => $category) {
			$title = ($treeTitle ? $treeTitle . ' - ' : '') . $category->title;
			$category->level = $level;
			$category->title = $title;
			$category->orderup = $idx > 0;
			$category->orderdown = $idx < count($categories);
            $category->parents = array_merge($parents, [$category->id]);
			$category->parentstree = implode(':', $category->parents);
			$items[] = $category;
			if ($category->children)
				$this->buildCategoryTree($items, $category->children, $level + 1, $title, $category->parents);
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
        //return parent::getItems();
		// Get a storage key.
		$store = $this->getStoreId();

		// Try to load the data from internal storage.
		if (!empty($this->cache[$store])) {
			return $this->cache[$store];
		}

		// Load the list items.
		try {
			$this->getDatabase()->setQuery($this->getListQuery());
			$categories	= $this->getDatabase()->loadObjectList('id');
		} catch (RuntimeException $e) {
			throw new Exception($e->getMessage(), 500);
		}

		if ($this->getState('filter.search')) {
			$items = $categories;
		} else {
			array_walk($categories, function ($category) use ($categories) {
				if ($category->parent_id && isset($categories[$category->parent_id])) {
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

            // Filter by max level
			if ($level = $this->getState('filter.level')) {
				$items = array_filter($items, function ($category) use ($level) {
					return $category->level <= $level;
				});
			}

            // Filter by parent category.
            $categoryId = $this->getState('filter.parent_id');
            if (is_numeric($categoryId)) {
                $items = array_filter($items, function ($category) use ($categoryId) {
                    return in_array($categoryId, (array)$category->parents);
                });
            }
        }

        // Filter by published state.
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $items = array_filter($items, function ($category) use ($published) {
                return $category->published == $published;
            });
        } else if ($published === '') {
            $items = array_filter($items, function ($category) use ($published) {
                return in_array($category->published, [0, 1]);
            });
        }

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $items = array_filter($items, function ($category) use ($access) {
                return $category->access == $access;
            });
        }

        // Filter by category type.
        $categoryType = $this->getState('filter.category_type');
        if (is_numeric($categoryType)) {
            $items = array_filter($items, function ($category) use ($categoryType) {
                return $category->category_type == $categoryType;
            });
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $items = array_filter($items, function ($category) use ($language) {
                return $category->language == $language;
            });
        }

        $this->setTotal(count($items));
        $pagination = $this->getPagination();
        $items = array_slice($items, $pagination->limitstart, $pagination->limit);

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

		// Join over the parent categories
		$query->select('c.title AS parentcat_title, c.id AS parentcat_id');
		$query->join('LEFT', '#__phocacart_categories AS c ON c.id = a.parent_id');

        // Join over the content types
        $query->select('a.category_type, ct.title AS category_type_title');
        $query->join('INNER', '#__phocacart_content_types AS ct ON ct.id = a.category_type');

		$query->select('cc.countid AS countid');
		$query->join('LEFT', '(SELECT cc.parent_id, COUNT(*) AS countid'
		. ' FROM #__phocacart_categories AS cc'
		.' GROUP BY cc.parent_id ) AS cc'
		.' ON a.parent_id = cc.parent_id');

		// ASSOCIATION
		// Join over the associations.
		if (I18nHelper::associationsEnabled()) {
			$subQuery = $db->getQuery(true)
				->select('COUNT(' . $db->quoteName('asso2.id') . ')')
				->from($db->quoteName('#__associations', 'asso'))
				->join('LEFT', $db->quoteName('#__associations', 'asso2') . ' ON ' . $db->quoteName('asso2.key') . ' = ' . $db->quoteName('asso.key'))
				->where($db->quoteName('asso.id') . ' = ' . $db->quoteName('a.id'))
				->where($db->quoteName('asso.context') . ' = ' . $db->quote('com_phocacart.category'));

			$query->select('(' . $subQuery . ') AS ' . $db->quoteName('association'));
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

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'title');
		$orderDirn	= $this->state->get('list.direction', 'asc');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}

	public function setTotal($total) {
		// When we use new total and new pagination, we need to clean their cache
		unset($this->cache[$this->getStoreId('getstart')]);
		unset($this->cache[$this->getStoreId('getPagination')]);

        $this->cache[$this->getStoreId('getTotal')] = (int)$total;
	}

    public function getBatchForm(): Form
    {
        return $this->loadForm($this->context . '.batch', 'batch_category', ['control' => '', 'load_data' => false]);
    }
}

