<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die();
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
jimport('joomla.application.component.model');

class PhocaCartModelCategories extends BaseDatabaseModel
{
	protected $categories 			= null;
	protected $categories_ordering	= null;
	protected $category_ordering		= null;

	public function __construct() {
		parent::__construct();
		$app	= Factory::getApplication();
		$this->setState('filter.language',$app->getLanguageFilter());
	}

	public function getCategoriesList($displaySubcategories = 0) {
		if (empty($this->categories)) {
			$categoriesOrdering = $this->getCategoryOrdering();

			if ((int)$displaySubcategories > 0) {
				$id = -1; // display subcategories - -1 means to load all items
			} else {
				$id = 0;// display only parent categories
			}

			$query			= $this->getCategoriesListQuery($id, $categoriesOrdering);
			$categories 	= $this->_getList($query);

			if (!empty($categories)) {

				// Parent Only
				foreach ($categories as $k => $v) {
					if ($v->parent_id == 0) {
						$this->categories[$v->id] = $categories[$k];
					}
				}

				// Subcategories
				foreach ($categories as $k => $v) {
					if (isset($this->categories[$v->parent_id])) {
						$this->categories[$v->parent_id]->subcategories[] = $categories[$k];
						$this->categories[$v->parent_id]->numsubcat++;
					}
				}
			}
			/*
			$this->categories 	= $this->_getList( $query );
			if (!empty($this->categories)) {
				foreach ($this->categories as $key => $value) {
					$query	= $this->getCategoriesListQuery( $value->id, $categoriesOrdering );
					$this->categories[$key]->subcategories = $this->_getList( $query );
				}
			}*/

		}
		return $this->categories;
	}

	public function getCategoriesListQuery($id, $categoriesOrdering) {

		$wheres				= array();
		$user 				= PhocacartUser::getUser();
		$userLevels			= implode (',', $user->getAuthorisedViewLevels());
		$userGroups 		= implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
		$app				= Factory::getApplication();
		$params 			= $app->getParams();


		$display_categories = $params->get('display_categories', '');
		$hide_categories 	= $params->get('hide_categories', '');

		if (!empty($display_categories)) {
			$display_categories = implode(',', $display_categories);
		}
		if (!empty($hide_categories)) {
			$hide_categories = implode(',', $hide_categories);
		}

		if ( $display_categories != '' ) {
			$wheres[] = " c.id IN (".$display_categories.")";
		}

		if ( $hide_categories != '' ) {
			$wheres[] = " c.id NOT IN (".$hide_categories.")";
		}

		$wheres[] = " c.type IN (0,1)";// type: common, onlineshop, pos

		if ($id == -1) {
			// No limit for parent_id - load all categories include subcategories
		} else {
			$wheres[] = " c.parent_id = " . (int)$id;
		}

		$wheres[] = " c.published = 1";

		if ($this->getState('filter.language')) {
			$wheres[] =  ' c.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}

		$wheres[] = " c.access IN (".$userLevels.")";
		$wheres[] = " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";

		/*$query =  " SELECT c.id, c.title, c.alias, c.image, c.description, c.image as image, c.parent_id as parentid, COUNT(c.id) AS numdoc"
		. " FROM #__phocacart_categories AS c"
		. " LEFT JOIN #__phocacart_products AS a ON a.catid = c.id AND a.published = 1"
		. " WHERE " . implode( " AND ", $wheres )
		. " GROUP BY c.id"
		. " ORDER BY c.".$categoriesOrdering;*/


		// Views Plugin can load additional columns
		$additionalColumns = array();
		$pluginLayout 	= PluginHelper::importPlugin('pcv');
		if ($pluginLayout) {
			$pluginOptions 				= array();
			$eventData 					= array();
			Factory::getApplication()->triggerEvent('onPCVonCategoriesBeforeLoadColumns', array('com_phocacart.categories', &$pluginOptions, $eventData));

			if (isset($pluginOptions['columns']) && $pluginOptions['columns'] != '') {
				if (!empty($pluginOptions['columns'])) {
					foreach ($pluginOptions['columns'] as $k => $v) {
						$additionalColumns[] = PhocacartText::filterValue($v, 'alphanumeric3');
					}
				}
			}
		}

		$baseColumns = array('c.id', 'c.title', 'c.alias', 'c.image', 'c.description', 'c.icon_class');

		$col = array_merge($baseColumns, $additionalColumns);
		$col = array_unique($col);


		$columns	= implode(',', $col) . ', c.parent_id as parentid, COUNT(c.id) AS numdoc, c.parent_id, 0 AS numsubcat';
		$groupsFull	= implode(',', $col) . ', c.parent_id';
		$groupsFast	= 'c.id';
		$groups		= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;


		$query =  'SELECT '.$columns

		. " FROM #__phocacart_categories AS c"
		//. " LEFT JOIN #__phocacart_categories AS s ON s.parent_id = c.id AND s.published = 1"

		//. " LEFT JOIN #__phocacart_product_categories AS pc ON pc.category_id = c.id"
		//. " LEFT JOIN #__phocacart_products AS a ON a.id = pc.product_id AND a.published = 1"
		//. " LEFT JOIN #__phocacart_products AS a ON a.catid = c.id AND a.published = 1"
		. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
		. " WHERE " . implode( " AND ", $wheres )
		. " GROUP BY ".$groups
		. " ORDER BY ".$categoriesOrdering;
		/*
		$query =  "SELECT c.id, c.title, group_concat(s.title) as subtitle, group_concat(s.id, ':', s.title, ':', s.alias) as subalias
					FROM #__phocacart_categories as c LEFT JOIN
						 #__phocacart_categories as s
						 on s.parent_id = c.id
					group by c.id";*/

		//echo nl2br(str_replace('#__', 'jos_', $query->__toString()));

		return $query;
	}

	public function getCategoryOrdering() {
		if (empty($this->category_ordering)) {
			$app						= Factory::getApplication();
			$params 					= $app->getParams();
			$ordering					= $params->get( 'category_ordering', 1 );
			$this->category_ordering 	= PhocacartOrdering::getOrderingText($ordering, 1);
		}
		return $this->category_ordering;
	}
}
?>
