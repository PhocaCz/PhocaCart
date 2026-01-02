<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Constants\GroupType;
use Phoca\PhocaCart\Constants\ProductType;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;
use Phoca\PhocaCart\I18n\I18nHelper;
//use PhocacartCalculation;

jimport('joomla.application.component.model');

class PhocaCartModelItem extends BaseDatabaseModel
{
	var $item 				= null;
	var $category			= null;
	var $itemnext			= null;
	var $itemprev			= null;

	function __construct() {
		$app	= Factory::getApplication();
		parent::__construct();
		$this->setState('filter.language',$app->getLanguageFilter());
	}

	public function getItem( $itemId, $catId) {
		if (empty($this->item)) {
			$query			= $this->getItemQuery($itemId, $catId);
			$this->item		= $this->_getList( $query, 0 , 1 );

			if (empty($this->item)) {
				return null;
			}

			if (isset($this->item[0]->taxhide)) {
				$registry = new Registry;
				$registry->loadString($this->item[0]->taxhide);
				$this->item[0]->taxhide = $registry->toArray();
			}

			if (isset($this->item[0])) {
				PhocacartCalculation::changePrice($this->item[0]);
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


        $columns = [
			'a.id',
            'a.catid',
            'c.id AS categoryid'
		];

        $columns = array_merge($columns, [
            I18nHelper::sqlCoalesce(['title'], 'a'),
            I18nHelper::sqlCoalesce(['alias'], 'a'),
            I18nHelper::sqlCoalesce(['title_long'], 'a'),
            I18nHelper::sqlCoalesce(['title'], 'c', 'category'),
            I18nHelper::sqlCoalesce(['alias'], 'c', 'category')
        ]);




		$query = ' SELECT ' . implode(',', $columns)
				.' FROM #__phocacart_products AS a'
				.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
				.' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
		if (!$skip['group']) {
			$query .= ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
					. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
		}

        $query .= I18nHelper::sqlJoin('#__phocacart_products_i18n', 'a');
        $query .= I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'c');

		$query .= ' WHERE ' . implode( ' AND ', $wheres )
				.' ORDER BY pc.ordering '.$order;


		return $query;
	}

	private function dispatchLoadColumns(array &$columns)
	{
		$pluginOptions = [];
		Dispatcher::dispatch(new Event\View\Item\BeforeLoadColumns('com_phocacart.items', $pluginOptions));

		$pluginColumns = $pluginOptions['columns'] ?? [];
		array_walk($pluginColumns, function($column) {
			return PhocacartText::filterValue($column, 'alphanumeric3');
		});

		$columns = array_merge($columns, $pluginColumns);
	}

	private function getItemQuery($itemId, $catId)
	{
		$app		= Factory::getApplication();
		$params 	= $app->getParams();
		$user 		= PhocacartUser::getUser();
		$db 		= $this->getDatabase();
		$lang       = $app->getLanguage()->getTag();

		$categoryId	= 0;
		$category	= $this->getCategory($itemId, $catId);

		if (isset($category[0]->id)) {
			$categoryId = $category[0]->id;
		}

		$where		= [];
		$where[] 	= 'i.id = ' . (int) $itemId;
		$where[]	= 'pc.category_id= ' . (int)$categoryId;
		$where[]	= 'pc.category_id= c.id';
		$where[] 	= 'i.published in (1, 2)';
		$where[] 	= 'c.published = 1';

		if (!$params->get('sql_product_skip_category_type', false)) {
			$where[] = 'c.type IN (' . implode(', ', [ProductType::Common, ProductType::Shop]) . ')';
		}

		if (!$params->get('sql_product_skip_access', false)) {
			$userLevels	= implode(',', $user->getAuthorisedViewLevels());
			$where[] = 'c.access IN (' . $userLevels . ')';
			$where[] = 'i.access IN (' . $userLevels . ')';
		}

		if (!$params->get('sql_product_skip_group', false)) {
			$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, GroupType::User, 1));
			$where[] = '(ga.group_id IN (' . $userGroups . ') OR ga.group_id IS NULL)';
			$where[] = '(gc.group_id IN (' . $userGroups . ') OR gc.group_id IS NULL)';
		}

		if ($this->getState('filter.language')) {
			$where[] =  'i.language IN (' . $db->quote($lang) . ', ' . $db->quote('*') . ')';
			$where[] =  'c.language IN (' . $db->quote($lang) . ', ' . $db->quote('*') . ')';
		}

		if ($params->get( 'hide_products_out_of_stock', false)) {
			$where[] = 'i.stock > 0';
		}

		// Views Plugin can load additional columns
		$columns = [
			'i.id', 'i.published', 'i.metadata',
			'i.type', 'i.image', 'i.weight', 'i.height', 'i.width', 'i.length', 'i.min_multiple_quantity', 'i.min_quantity_calculation', 'i.volume',
			'i.price', 'i.price_original', 'i.stockstatus_a_id', 'i.stockstatus_n_id', 'i.stock_calculation',
			'i.min_quantity', 'i.min_multiple_quantity', 'i.max_quantity', 'i.max_quantity_calculation', 'i.stock', 'i.sales', 'i.featured', 'i.external_id', 'i.unit_amount', 'i.unit_unit', 'i.video',
			'i.external_link', 'i.external_text', 'i.external_link2', 'i.external_text2', 'i.public_download_file', 'i.public_download_text', 'i.public_play_file', 'i.public_play_text',
			'i.sku', 'i.upc', 'i.ean', 'i.jan', 'i.isbn', 'i.mpn', 'i.serial_number', 'i.points_needed', 'i.points_received', 'i.date', 'i.date_update', 'i.delivery_date',
			'i.gift_types', 'i.redirect_product_id', 'i.redirect_url',
			'i.subscription_period', 'i.subscription_unit', 'i.subscription_signup_fee', 'i.subscription_renewal_discount', 'i.subscription_renewal_discount_calculation_type', 'i.subscription_grace_period_days',
			'pc.ordering', 'c.id AS catid', 'i.catid AS preferred_catid',
			'm.id as manufacturerid', 'm.image as manufacturerimage',
		];

		/*if (I18nHelper::isI18n()) {
			$columns = array_merge($columns, [
				'coalesce(i18n_i.alias, i.alias) as alias', 'coalesce(i18n_i.title, i.title) as title', 'i18n_i.title_long', 'i18n_i.description', 'i18n_i.description_long', 'i18n_i.features', 'i18n_i.metatitle', 'i18n_i.metadesc', 'i18n_i.metakey',
				'coalesce(i18n_c.title, c.title) AS cattitle', 'coalesce(i18n_c.alias, c.alias) AS catalias',
				'coalesce(i18n_m.title, m.title) as manufacturertitle', 'coalesce(i18n_m.link, m.link) as manufacturerlink',
			]);
		} else {
			$columns = array_merge($columns, [
				'i.alias', 'i.title', 'i.title_long', 'i.description', 'i.description_long', 'i.features', 'i.metatitle', 'i.metadesc', 'i.metakey',
				'c.title AS cattitle', 'c.alias AS catalias',
				'm.title as manufacturertitle', 'm.link as manufacturerlink',
			]);
		}*/

        $columns = array_merge($columns, [
            I18nHelper::sqlCoalesce(['title'], 'i'),
            I18nHelper::sqlCoalesce(['alias'], 'i'),
            I18nHelper::sqlCoalesce(['title_long'], 'i'),
            I18nHelper::sqlCoalesce(['description'], 'i'),
            I18nHelper::sqlCoalesce(['description_long'], 'i'),
            I18nHelper::sqlCoalesce(['features'], 'i'),
            I18nHelper::sqlCoalesce(['metatitle'], 'i'),
            I18nHelper::sqlCoalesce(['metadesc'], 'i'),
            I18nHelper::sqlCoalesce(['metakey'], 'i'),
            I18nHelper::sqlCoalesce(['title'], 'c', 'cat'),
            I18nHelper::sqlCoalesce(['alias'], 'c', 'cat'),
            I18nHelper::sqlCoalesce(['title'], 'm', 'manufacturer'),
            I18nHelper::sqlCoalesce(['link'], 'm', 'manufacturer'),
            I18nHelper::sqlCoalesce(['description'], 'm', 'manufacturer')
        ]);



		if (!$params->get('sql_product_skip_tax', false)) {
			$columns = array_merge($columns, [
				't.id as taxid', 't.tax_rate as taxrate', 't.calculation_type as taxcalculationtype', I18nHelper::sqlCoalesce(['title'], 't', 'tax'), 't.tax_hide as taxhide'
			]);
		} else {
			$columns = array_merge($columns, [
				'NULL as taxid', 'NULL as taxrate', 'NULL as taxcalculationtype', 'NULL as taxtitle', 'NULL as taxhide'
			]);
		}

		if (!$params->get('sql_product_skip_group', false)) {
			$columns = array_merge($columns, [
				' MIN(ppg.price) as group_price', 'MAX(pptg.points_received) as group_points_received'
			]);
		} else {
			$columns = array_merge($columns, [
				'NULL as group_price', 'NULL as group_points_received'
			]);
		}

		$this->dispatchLoadColumns($columns);
		$columns = array_unique($columns);

		$query = ' SELECT ' . implode(',', $columns)
				.' FROM #__phocacart_products AS i'
				.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = i.id'
				.' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
				.' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = i.manufacturer_id';

        $query .= I18nHelper::sqlJoin('#__phocacart_products_i18n', 'i');
        $query .= I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'c');
        $query .= I18nHelper::sqlJoin('#__phocacart_manufacturers_i18n', 'm');


		if (!$params->get('sql_product_skip_tax', false)) {
            $query .= ' LEFT JOIN #__phocacart_taxes AS t ON t.id = i.tax_id';
            $query .= I18nHelper::sqlJoin('#__phocacart_taxes_i18n', 't');
        }

		if (!$params->get('sql_product_skip_group', false)) {
			$query .= ' LEFT JOIN #__phocacart_item_groups AS ga ON i.id = ga.item_id AND ga.type = ' . GroupType::Product;
			$query .= ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = ' . GroupType::Category;
			// user is in more groups, select lowest price by best group
			$query .= ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON i.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = i.id AND group_id IN ('.$userGroups.') AND type = ' . GroupType::Product . ')';
			// user is in more groups, select highest points by best group
			$query .= ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON i.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = i.id AND group_id IN ('.$userGroups.') AND type = ' . GroupType::Product . ')';
		}

		$query .= ' WHERE ' . implode(' AND ', $where);
		// No need to group, there is limit 1

		return $query;
	}

	function getCategory($itemId, $catId) {
		if (empty($this->category)) {
			$query = $this->getCategoryQuery( $itemId, $catId );
			$this->category = $this->_getList( $query, 0, 1 );
		}
		return $this->category;
	}

	private function getCategoryQuery($itemId, $catId) {
		$app		= Factory::getApplication();
		$user 		= PhocacartUser::getUser();
		$db 		= $this->getDatabase();
		$lang       = $app->getLanguage()->getTag();

		$where		= [];
		$where[] = ' c.published = 1';
		$where[] = ' c.type IN (' . implode(', ', [ProductType::Common, ProductType::Shop]) . ')';

		if ($this->getState('filter.language')) {
			$lang 		= Factory::getLanguage()->getTag();
			$where[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
		}

		if ((int)$catId > 0) {
			$where[]	= ' c.id = ' . (int)$catId;
		} else {
			$where[]	= ' a.id = ' . (int)$itemId;
		}

		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$where[] = 'c.access IN (' . $userLevels . ')';
		$where[] = 'a.access IN (' . $userLevels . ")";

		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, GroupType::User, 1));
		$where[] = '(ga.group_id IN (' . $userGroups . ') OR ga.group_id IS NULL)';
		$where[] = '(gc.group_id IN (' . $userGroups . ') OR gc.group_id IS NULL)';

		$columns = ['c.id', 'c.parent_id'];
		/*if (I18nHelper::isI18n()) {
			$columns = array_merge($columns, [
				'coalesce(i18n.title, c.title) as title', 'coalesce(i18n.alias, c.alias) as alias', 'i18n.description'
			]);
		} else {
			$columns = array_merge($columns, [
				'c.title', 'c.alias', 'c.description'
			]);
		}*/

         $columns = array_merge($columns, [
            I18nHelper::sqlCoalesce(['title'], 'c'),
            I18nHelper::sqlCoalesce(['alias'], 'c'),
            I18nHelper::sqlCoalesce(['description'], 'c'),
        ]);

		$query = ' SELECT ' . implode(', ', $columns)
				. ' FROM #__phocacart_categories AS c'
				. ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.category_id = c.id'
				. ' LEFT JOIN #__phocacart_products AS a ON a.id = pc.product_id'
				. ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = ' . GroupType::Product
				. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = ' . GroupType::Category;
		$query .= I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'c');
		$query .= ' WHERE ' . implode( ' AND ', $where)
				. ' ORDER BY c.ordering';

		return $query;
	}

	public function hit($pk = 0) {
		$input = Factory::getApplication()->getInput();
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
