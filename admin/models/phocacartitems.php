<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport( 'joomla.application.component.modellist' );
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

class PhocaCartCpModelPhocaCartItems extends JModelList
{
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
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'ordering', 'pc.ordering',
				'language', 'a.language',
				'hits', 'a.hits',
				'date', 'a.date',
				'published','a.published',
				'image', 'a.image',
				'price', 'a.price',
				'price_original', 'a.price_original',
				'stock', 'a.stock',
				'sku', 'a.sku'
			);

			// ASSOCIATION
            $assoc = JLanguageAssociations::isEnabled();
            if ($assoc){
                $config['filter_fields'][] = 'association';
            }

		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// ASSOCIATION
        $forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');
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

		$state = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $state);

		$categoryId = $app->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', null);
		$this->setState('filter.category_id', $categoryId);

		$language = $app->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_phocacart');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.title', 'asc');

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
		$id	.= ':'.$this->getState('filter.state');
		$id	.= ':'.$this->getState('filter.category_id');
        $id .= ':'.$this->getState('filter.language');
		$id	.= ':'.$this->getState('filter.item_id');

		return parent::getStoreId($id);
	}


	protected function getListQuery()
	{


		$paramsC 					    = PhocacartUtils::getComponentParameters();
		$search_matching_option_admin	= $paramsC->get( 'search_matching_option_admin', 'exact' );

		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		$columns	= 'DISTINCT a.id, a.title, a.image, a.published, a.checked_out, a.checked_out_time, a.alias, a.featured, a.price, a.price_original, a.language, a.hits, a.sku, a.stock';
		// GROUP BY not used
		//$groupsFull	= $columns . ', ' .'a.tax_id, a.manufacturer_id, a.description, l.title, uc.name, ag.title';
		//$groupsFast	= 'a.id';
		//$groups		= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;


		$query->select($this->getState('list.select', $columns));

		$query->from('`#__phocacart_products` AS a');

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
		// They are more ways to get the info about categories
		// 1) GROUP BY + GROUP_CONCAT - slow, when only_full_group_by rule is used - much more slower than slow
		// 2) SUBQUERIES - faster even they look complicated
		// 3) SPECIFIC QUERY used in view: PhocacartCategoryMultiple::getCategoriesByProducts($idItems) (no loop used) DISTINCT needs to be used

		/*
		 * GROUP BY
		$query->select('GROUP_CONCAT(c.title) AS category_title, GROUP_CONCAT(c.id) AS category_id');
		$query->join('LEFT', '#__phocacart_product_categories AS pc ON a.id = pc.product_id');
		$query->join('LEFT', '#__phocacart_categories AS c ON c.id = pc.category_id');

		* SUBQUERIES
		$query->select('(SELECT GROUP_CONCAT(c.id) FROM jos_phocacart_product_categories AS pc
		 				LEFT JOIN jos_phocacart_categories AS c ON c.id = pc.category_id
						WHERE a.id = pc.product_id) AS category_id');

		$query->select('(SELECT GROUP_CONCAT(c.title) FROM jos_phocacart_product_categories AS pc
		 				LEFT JOIN jos_phocacart_categories AS c ON c.id = pc.category_id
						WHERE a.id = pc.product_id) AS category_title');

		$query->select('(SELECT GROUP_CONCAT(c.id, ":", c.title) FROM jos_phocacart_product_categories AS pc
		 				LEFT JOIN jos_phocacart_categories AS c ON c.id = pc.category_id
						WHERE a.id = pc.product_id) AS category_title');
	*/



		// Not used
		//$query->select("group_concat(c.id, '|^|', c.alias, '|^|', c.title SEPARATOR '|~|') as categories");

		//$query->select('v.average AS ratingavg');
		//$query->join('LEFT', '#__phocadownload_img_votes_statistics AS v ON v.imgid = a.id');



        // ASSOCIATION
        // Join over the associations.
        $assoc = JLanguageAssociations::isEnabled();
        if ($assoc) {
            $query->select('COUNT(' . $db->quoteName('asso2.id') . ') > 1 as ' . $db->quoteName('association'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__associations', 'asso') . ' ON ' . $db->quoteName('asso.id') . ' = ' . $db->quoteName('a.id')
                    . ' AND ' . $db->quoteName('asso.context') . ' = ' . $db->quote('com_phocacart.item')
                )
                ->join(
                    'LEFT',
                    $db->quoteName('#__associations', 'asso2') . ' ON ' . $db->quoteName('asso2.key') . ' = ' . $db->quoteName('asso.key')
                )
                ->group(
                    $db->quoteName(
                        array(
                            'a.id',
                            'a.title',
                            'a.alias',
                            'a.checked_out',
                            'a.checked_out_time',
                            'a.published',
                            'a.access',
                            'a.ordering',
                            'a.featured',
                            'a.language',
                            'l.title' ,
                            'l.image' ,
                            'uc.name' ,
                            'ag.title'
                        )
                    )
                );
        }


		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = '.(int) $access);
		}

		// Filter by published state.
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.published = '.(int) $published);
		}
		else if ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}


		// When category is selected, we need to get info about selected category
		// When it is not selected, don't ask for it to make the query faster
		// pc.ordering is set as default ordering and it can be set (even igonered) even whey category not selected
		// is complicated but loads much faster
		$orderCol	= $this->state->get('list.ordering', 'title');
		$orderDirn	= $this->state->get('list.direction', 'asc');
		$categoryId = $this->getState('filter.category_id');

		// Filter by category.
		if ($orderCol == 'pc.ordering' || is_numeric($categoryId)) {
			// Ask only when really needed
			$query->select('pc.ordering');
			$query->join('LEFT', '#__phocacart_product_categories AS pc ON a.id = pc.product_id');
			$query->join('LEFT', '#__phocacart_categories AS c ON c.id = pc.category_id');
		}


		if (is_numeric($categoryId)) {
			//$query->where('a.catid = ' . (int) $categoryId);
			$query->where('pc.category_id = ' . (int) $categoryId);
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

				switch ($search_matching_option_admin) {
					case 'all':
					case 'any':

						$words	= explode(' ', $search);
						$wheres = array();
						foreach ($words as $word) {

							if (!$word = trim($word)) {
								continue;
							}

							$word			= $db->quote('%'.$db->escape($word, true).'%', false);
							$wheresSub 		= array();
							$wheresSub[]	= 'a.title LIKE '.$word;
							$wheresSub[]	= 'a.alias LIKE '.$word;
							$wheresSub[]	= 'a.metakey LIKE '.$word;
							$wheresSub[]	= 'a.metadesc LIKE '.$word;
							$wheresSub[]	= 'a.description LIKE '.$word;
							$wheresSub[]	= 'a.sku LIKE '.$word;
							$wheres[]		= implode(' OR ', $wheresSub);
						}

						$query->where('(' . implode(($search_matching_option_admin == 'all' ? ') AND (' : ') OR ('), $wheres) . ')');

					break;

					case 'exact':
					default:
						$text		= $db->quote('%'.$db->escape($search, true).'%', false);
						$wheresSub	= array();
						$wheresSub[]	= 'a.title LIKE '.$text;
						$wheresSub[]	= 'a.alias LIKE '.$text;
						$wheresSub[]	= 'a.metakey LIKE '.$text;
						$wheresSub[]	= 'a.metadesc LIKE '.$text;
						$wheresSub[]	= 'a.description LIKE '.$text;
						$wheresSub[]	= 'a.sku LIKE '.$text;
						$query->where('(' . implode(') OR (', $wheresSub) . ')');

					break;
				}
			}
		}

	///	$query->group($groups);

		// Add the list ordering clause.
		//$orderCol	= $this->state->get('list.ordering', 'title');
		//$orderDirn	= $this->state->get('list.direction', 'asc');

		//if ($orderCol == 'pc.ordering' || $orderCol == 'category_title') {
			//$orderCol = 'category_title '.$orderDirn.', pc.ordering';
		//}
		$query->order($db->escape($orderCol.' '.$orderDirn));

		//echo nl2br(str_replace('#__', 'jos_', $query->__toString()));


		return $query;
	}
}
?>
