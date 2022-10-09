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
use Joomla\CMS\Table\Table;
jimport('joomla.application.component.model');

class PhocaCartModelItem extends BaseDatabaseModel
{
	var $item 				= null;
	var $category			= null;
	var $itemname			= null;
	var $itemnext			= null;
	var $itemprev			= null;

	function __construct() {
		$app	= Factory::getApplication();
		parent::__construct();
		$this->setState('filter.language',$app->getLanguageFilter());
	}

	function getItem( $itemId, $catId) {
		if (empty($this->item)) {
			$query			= $this->getItemQuery( $itemId, $catId);
			$this->item		= $this->_getList( $query, 0 , 1 );

			if (empty($this->item)) {
				return null;
			}
		}
		return $this->item;
	}

	function getItemNext($ordering, $catid) {
		if (empty($this->itemnext)) {
			$query				= $this->getItemQueryOrdering( $ordering, $catid, 2 );
			$this->itemnext		= $this->_getList( $query, 0 , 1 );

			if (empty($this->itemnext)) {
				return null;
			}
		}
		return $this->itemnext;
	}
	function getItemPrev($ordering, $catid) {
		if (empty($this->itemprev)) {
			$query				= $this->getItemQueryOrdering( $ordering, $catid, 1 );
			$this->itemprev	= $this->_getList( $query, 0 , 1 );

			if (empty($this->itemprev)) {
				return null;
			}
		}
		return $this->itemprev;
	}

	private function getItemQueryOrdering($ordering, $catid, $direction) {

		$app		= Factory::getApplication();
		$params 	= $app->getParams();
		$p['hide_products_out_of_stock']	= $params->get( 'hide_products_out_of_stock', 0);

		$skip			        = array();
		$skip['access']	        = $params->get('sql_product_skip_access', 0);
		$skip['group']	        = $params->get('sql_product_skip_group', 0);
		//$skip['attributes']	    = $params->get('sql_product_skip_attributes', 0);
		$skip['category_type']  = $params->get('sql_product_skip_category_type', 0);
		//$skip['tax']   			= $params->get('sql_product_skip_tax', 0);

		$user 		= PhocacartUser::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));


		$wheres[]	= " pc.category_id = ".(int) $catid;
		//$wheres[]	= " c.catid= c.id";
		$wheres[] = " a.published = 1";
		$wheres[] = " c.published = 1";

		if ($direction == 1) {
			$wheres[] = " pc.ordering < " . (int) $ordering;
			$order = 'DESC';
		} else {
			$wheres[] = " pc.ordering > " . (int) $ordering;
			$order = 'ASC';
		}

		if (!$skip['category_type']) {
			$wheres[] = " c.type IN (0,1)";// type: common, onlineshop, pos
		}

		if (!$skip['access']) {
			$wheres[] = " c.access IN (" . $userLevels . ")";
			$wheres[] = " a.access IN (" . $userLevels . ")";
		}

		if (!$skip['group']) {
			$wheres[] = " (ga.group_id IN (" . $userGroups . ") OR ga.group_id IS NULL)";
			$wheres[] = " (gc.group_id IN (" . $userGroups . ") OR gc.group_id IS NULL)";
		}

		if ($this->getState('filter.language')) {
			$lang 		= Factory::getLanguage()->getTag();
			$wheres[] 	= PhocacartUtilsSettings::getLangQuery('a.language', $lang);
			$wheres[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
		}

		if ($p['hide_products_out_of_stock'] == 1) {
			$wheres[] = " a.stock > 0";
		}

		$query = ' SELECT a.id, a.title, a.title_long, a.alias, a.catid, c.id AS categoryid, c.title AS categorytitle, c.alias AS categoryalias'
				.' FROM #__phocacart_products AS a'
				.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
				.' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
		if (!$skip['group']) {
			$query .= ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
					. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
		}

		$query .= ' WHERE ' . implode( ' AND ', $wheres )
				.' ORDER BY pc.ordering '.$order;


		return $query;

	}
	private function getItemQuery( $itemId, $catId ) {

		$app		= Factory::getApplication();
		$params 	= $app->getParams();
		$p['hide_products_out_of_stock']	= $params->get( 'hide_products_out_of_stock', 0);


		$user 		= PhocacartUser::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));

		$categoryId	= 0;
		$category	= $this->getCategory($itemId, $catId);

		if (isset($category[0]->id)) {
			$categoryId = $category[0]->id;
		}

		$skip			        = array();
		$skip['access']	        = $params->get('sql_product_skip_access', 0);
		$skip['group']	        = $params->get('sql_product_skip_group', 0);
		//$skip['attributes']	    = $params->get('sql_product_skip_attributes', 0);
		$skip['category_type']  = $params->get('sql_product_skip_category_type', 0);
		$skip['tax']   			= $params->get('sql_product_skip_tax', 0);

		$wheres		= array();
		$wheres[]	= " pc.category_id= ".(int) $categoryId;
		$wheres[]	= " pc.category_id= c.id";
		$wheres[] 	= " i.published = 1";
		$wheres[] 	= " c.published = 1";
		$wheres[] 	= " i.id = " . (int) $itemId;

		if (!$skip['category_type']) {
			$wheres[] = " c.type IN (0,1)";// type: common, onlineshop, pos
		}

		if (!$skip['access']) {
			$wheres[] = " c.access IN (" . $userLevels . ")";
			$wheres[] = " i.access IN (" . $userLevels . ")";
		}

		if (!$skip['group']) {
			$wheres[] = " (ga.group_id IN (" . $userGroups . ") OR ga.group_id IS NULL)";
			$wheres[] = " (gc.group_id IN (" . $userGroups . ") OR gc.group_id IS NULL)";
		}


		if ($this->getState('filter.language')) {
			$wheres[] =  ' i.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
			$wheres[] =  ' c.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}

		if ($p['hide_products_out_of_stock'] == 1) {
			$wheres[] = " i.stock > 0";
		}

		// Views Plugin can load additional columns
		$additionalColumns = array();
		$pluginLayout 	= PluginHelper::importPlugin('pcv');
		if ($pluginLayout) {
			$pluginOptions 				= array();
			$eventData 					= array();
			Factory::getApplication()->triggerEvent('onPCVonItemBeforeLoadColumns', array('com_phocacart.items', &$pluginOptions, $eventData));

			if (isset($pluginOptions['columns']) && $pluginOptions['columns'] != '') {
				if (!empty($pluginOptions['columns'])) {
					foreach ($pluginOptions['columns'] as $k => $v) {
						$additionalColumns[] = PhocacartText::filterValue($v, 'alphanumeric3');
					}
				}
			}
		}

		$baseColumns = array('i.id', 'i.title', 'i.title_long', 'i.alias', 'i.description', 'i.features', 'i.metatitle', 'i.metadesc', 'i.metakey', 'i.metadata', 'i.type', 'i.image', 'i.weight', 'i.height', 'i.width', 'i.length', 'i.min_multiple_quantity', 'i.min_quantity_calculation', 'i.volume', 'i.description', 'i.description_long', 'i.price', 'i.price_original', 'i.stockstatus_a_id', 'i.stockstatus_n_id', 'i.stock_calculation', 'i.min_quantity', 'i.min_multiple_quantity', 'i.stock', 'i.sales', 'i.featured', 'i.external_id', 'i.unit_amount', 'i.unit_unit', 'i.video', 'i.external_link', 'i.external_text', 'i.external_link2', 'i.external_text2', 'i.public_download_file', 'i.public_download_text', 'i.public_play_file', 'i.public_play_text', 'i.sku', 'i.upc', 'i.ean', 'i.jan', 'i.isbn', 'i.mpn', 'i.serial_number', 'i.points_needed', 'i.points_received', 'i.date', 'i.date_update', 'i.delivery_date', 'i.gift_types');

		$col = array_merge($baseColumns, $additionalColumns);
		$col = array_unique($col);



		$columns	= implode(',', $col) . ', pc.ordering, c.id AS catid, c.title AS cattitle, c.alias AS catalias, i.catid AS preferred_catid, m.id as manufacturerid, m.title as manufacturertitle, m.link as manufacturerlink,';

		if (!$skip['tax']) {
            $columns .= ' t.id as taxid, t.tax_rate as taxrate, t.calculation_type as taxcalculationtype, t.title as taxtitle,';
        } else {
            $columns .= ' NULL as taxid, NULL as taxrate, NULL as taxcalculationtype, NULL as taxtitle,';
        }

        if (!$skip['group']) {
            $columns .= ' MIN(ppg.price) as group_price, MAX(pptg.points_received) as group_points_received';
        } else {
            $columns .= ' NULL as group_price, NULL as group_points_received';
        }


		$groupsFull	= implode(',', $col) .',pc.ordering, c.id, c.title, c.alias, m.id, m.title, m.link';

        if (!$skip['tax']) {
            $groupsFull .= ', t.id, t.tax_rate, t.calculation_type, t.title';
        }

        $groupsFast	= 'i.id';
		$groups		= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;


		$query = ' SELECT '.$columns
				.' FROM #__phocacart_products AS i'
				.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = i.id'
				.' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
				.' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = i.manufacturer_id';

		if (!$skip['tax']) {
            $query .= ' LEFT JOIN #__phocacart_taxes AS t ON t.id = i.tax_id';
        }

		if (!$skip['group']) {
			$query .= ' LEFT JOIN #__phocacart_item_groups AS ga ON i.id = ga.item_id AND ga.type = 3';// type 3 is product
			$query .= ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
			// user is in more groups, select lowest price by best group
			$query .= ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON i.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = i.id AND group_id IN ('.$userGroups.') AND type = 3)';
			// user is in more groups, select highest points by best group
			$query .= ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON i.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = i.id AND group_id IN ('.$userGroups.') AND type = 3)';
		}





		$query .= ' WHERE ' . implode( ' AND ', $wheres )
				.' GROUP BY '.$groups
				.' ORDER BY pc.ordering';

		//echo nl2br(str_replace('#__', 'jos_', $query));

		return $query;

	}

	function getCategory($itemId, $catId) {
		if (empty($this->category)) {
			$query			= $this->getCategoryQuery( $itemId, $catId );

			$this->category		= $this->_getList( $query, 0, 1 );
		}
		return $this->category;
	}

	function getCategoryQuery($itemId, $catId) {

		$user 		= PhocacartUser::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));

		$wheres		= array();
		//$app		= JFactory::getApplication();
		//$params 	= $app->getParams();

		$wheres[] = " c.published = 1";

		$wheres[] = " c.type IN (0,1)";// type: common, onlineshop, pos

		if ($this->getState('filter.language')) {
			$lang 		= Factory::getLanguage()->getTag();
			//$wheres[] 	= PhocacartUtilsSettings::getLangQuery('a.language', $lang);
			$wheres[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
		}

		if ((int)$catId > 0) {
			$wheres[]	= " c.id= ".(int)$catId;
		} else {
			$wheres[]	= " a.id= ".(int)$itemId;
		}

		$wheres[] = " c.access IN (".$userLevels.")";
		$wheres[] = " a.access IN (".$userLevels.")";

		$wheres[] = " (ga.group_id IN (".$userGroups.") OR ga.group_id IS NULL)";
		$wheres[] = " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";

		$query = " SELECT c.id, c.title, c.alias, c.description, c.parent_id"
				. " FROM #__phocacart_categories AS c"
				. ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.category_id = c.id'
				. " LEFT JOIN #__phocacart_products AS a ON a.id = pc.product_id"
				. ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
				. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
				. " WHERE " . implode( " AND ", $wheres )
				. " ORDER BY c.ordering";

		return $query;
	}

	public function hit($pk = 0) {
		$input = Factory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount) {
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('product.id');

			$table = Table::getInstance('PhocaCartItem', 'Table');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}
}
?>
