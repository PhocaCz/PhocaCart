<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

defined('_JEXEC') or die();
jimport('joomla.application.component.model');

class PhocaCartModelCategory extends BaseDatabaseModel
{

	protected $item 				= null;
	protected $item_ordering		= null;
	protected $layout_type			= null;
	protected $category 			= null;
	protected $subcategories 		= null;
	protected $category_ordering	= null;
	protected $pagination			= null;
	protected $total				= null;
	protected $ordering				= null;
	protected $category_layout_plugin	= '';

	public function __construct() {
		parent::__construct();

		$app					= Factory::getApplication();
		$config 				= Factory::getConfig();
		$paramsC 				= $app->getParams();
		$item_pagination		= $paramsC->get( 'item_pagination_default', '20' );
		$item_ordering			= $paramsC->get( 'item_ordering', 1 );
		$layout_type			= $paramsC->get( 'layout_type', 'grid' );

		$this->category_layout_plugin	= $paramsC->get( 'category_layout_plugin', '' );

		$manufacturer_alias	= $paramsC->get( 'manufacturer_alias', 'manufacturer');
		$manufacturer_alias	= $manufacturer_alias != '' ? trim(PhocacartText::filterValue($manufacturer_alias, 'alphanumeric'))  : 'manufacturer';

		$limit					= PhocacartPagination::getMaximumLimit($app->getUserStateFromRequest('com_phocacart.limit', 'limit', $item_pagination, 'int'));

		$this->setState('limit', $limit);
		$this->setState('limitstart', $app->input->get('limitstart', 0, 'int'));
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));
		$this->setState('filter.language',$app->getLanguageFilter());
		$this->setState('filter_order', $app->input->get('filter_order', 'ordering'));
		$this->setState('filter_order_dir', $app->input->get('filter_order_Dir', 'ASC'));
		$this->setState('itemordering', $app->getUserStateFromRequest('com_phocacart.itemordering', 'itemordering', $item_ordering, 'int'));
		$this->setState('layouttype', $app->getUserStateFromRequest('com_phocacart.layouttype', 'layouttype', $layout_type, 'string'));


	}

	public function getLayoutType() {
		$layoutType 	= $this->getState('layouttype');
		$layoutType		= PhocacartRenderFront::getLayoutType($layoutType);
		return $layoutType;
	}

	public function getPagination($categoryId) {
		if (empty($this->pagination)) {
			jimport('joomla.html.pagination');
			$this->pagination = new PhocacartPagination( $this->getTotal($categoryId), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->pagination;
	}

	function getOrdering() {
		if(empty($this->ordering)) {
			$this->ordering = PhocacartOrdering::renderOrderingFront($this->getState('itemordering'), 0);
		}
		return $this->ordering;
	}

	public function getTotal($categoryId) {
		if (empty($this->total)) {
			$query = $this->getItemListQuery($categoryId, 1);
			$this->total = $this->_getListCount($query);
		}
		return $this->total;
	}

	public function getItemList($categoryId) {
		if (empty($this->item)) {
			$query			= $this->getItemListQuery( $categoryId);
			$this->item		= $this->_getList( $query ,$this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->item;
	}

	public function getCategory($categoryId) {
		if (empty($this->category)) {
			$query					= $this->getCategoriesQuery( $categoryId, FALSE );
			$this->category 		= $this->_getList( $query, 0, 1 );
		}
		return $this->category;
	}

	public function getSubcategories($categoryId) {
		if (empty($this->subcategories)) {
			$query					= $this->getCategoriesQuery( $categoryId, TRUE );
			$this->subcategories 	= $this->_getList( $query );
		}
		return $this->subcategories;
	}

	protected function getItemListQuery($categoryId, $count = 0) {

		$app		= Factory::getApplication();
		$user 		= PhocacartUser::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
		$params 	= $app->getParams();
		$wheres		= array();
		$lefts		= array();


		$skip			        = array();
		$skip['access']	        = $params->get('sql_products_skip_access', 0);
		$skip['group']	        = $params->get('sql_products_skip_group', 0);
		$skip['attributes']	    = $params->get('sql_products_skip_attributes', 0);
		$skip['category_type']  = $params->get('sql_products_skip_category_type', 0);
		$skip['tax']   			= $params->get('sql_products_skip_tax', 0);

		$p = array();
		$p['hide_products_out_of_stock']			= $params->get( 'hide_products_out_of_stock', 0);
		$p['switch_image_category_items']			= $params->get( 'switch_image_category_items', 0 );
		$p['display_products_all_subcategories']	= $params->get( 'display_products_all_subcategories', 0 );
		$leftImages = '';
		$selImages = '';
		if ($p['switch_image_category_items'] == 1) {

			//$leftImages = ' LEFT JOIN #__phocacart_product_images AS im ON a.id = im.product_id';
			// To have ordering of additional images
			$leftImages = ' LEFT JOIN #__phocacart_product_images AS im ON im.id = (SELECT id FROM #__phocacart_product_images WHERE product_id = a.id ORDER BY ordering ASC LIMIT 1)';
			$selImages	= ' GROUP_CONCAT(im.image) as additional_image,';
		}


		$wheres			= array();
		$subWherePcCat 	= '';

		if ((int)$categoryId > 0) {

			// Standard - only products from one category
			$subWherePcCat 		= ' AND pc.category_id = '.(int)$categoryId;

			// Display products not only from current category but even from all subcategories
			if ($p['display_products_all_subcategories'] == 1) {
				$categoryChildrenId = PhocacartCategoryMultiple::getCategoryChildrenString((int)$categoryId, (string)$categoryId);
				if ($categoryChildrenId !== '') {
					$wheres[]			= " c.id IN (".$categoryChildrenId.")";
					$subWherePcCat		= " AND pc.category_id IN (".$categoryChildrenId.")";
				} else {
					$wheres[]			= " c.id = ".(int)$categoryId;
				}
			} else {
				$wheres[]			= " c.id = ".(int)$categoryId;
			}

		}
		$wheres[] = ' a.published = 1';
		$wheres[] = ' c.published = 1';

		if (!$skip['category_type']) {
            $wheres[] = " c.type IN (0,1)";// type: common, onlineshop, pos
        }

		if ($this->getState('filter.language')) {
			$lang 		= Factory::getLanguage()->getTag();
			$wheres[] 	= PhocacartUtilsSettings::getLangQuery('a.language', $lang);
			$wheres[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
		}
		$itemOrdering = $this->getItemOrdering();

		// BE AWARE
		// g.item_id is in this case product_id: phocacart_item_groups table is used for more instances: user, product, category
		// so item_id in case type = 3 is product_id, items_id in case type = 2 is category_id
		// see types: administrator\components\com_phocacart\libraries\phocacart\group\group.php


		if (!$skip['access']) {
			$wheres[] = " c.access IN (".$userLevels.")";
			$wheres[] = " a.access IN (".$userLevels.")";
		}

		if (!$skip['group']) {
			$wheres[] = " (ga.group_id IN (".$userGroups.") OR ga.group_id IS NULL)";
			$wheres[] = " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";
		}

		if ($p['hide_products_out_of_stock'] == 1) {
			$wheres[] = " a.stock > 0";
		}


		// Category Layout Plugin can change ordering
		// Category Layout Plugin can load additional columns
		$additionalColumns = array();
		if ($this->category_layout_plugin != '') {
			$this->category_layout_plugin = PhocacartText::filterValue($this->category_layout_plugin, 'alphanumeric2');

			$pluginOptions = [];
			Dispatcher::dispatch(new Event\Layout\Category\GetOptions('com_phocacart.category', $pluginOptions, [
				'pluginname' => $this->category_layout_plugin,
			]));

			if (isset($pluginOptions['ordering']) && $pluginOptions['ordering'] != '') {
				$pluginOrdering = PhocacartText::filterValue($pluginOptions['ordering'], 'alphanumeric5');
				if ($pluginOrdering != '') {
					$itemOrdering = $pluginOrdering . ',' . $itemOrdering;
				}
			}

			if (isset($pluginOptions['columns']) && $pluginOptions['columns'] != '') {
				if (!empty($pluginOptions['columns'])) {
					foreach ($pluginOptions['columns'] as $k => $v) {
						$additionalColumns[] = PhocacartText::filterValue($v, 'alphanumeric3');
					}
				}
			}
		}

		// Views Plugin can load additional columns
		$pluginOptions = [];
		Dispatcher::dispatch(new Event\View\Category\BeforeLoadColumns('com_phocacart.category', $pluginOptions));

		if (isset($pluginOptions['columns']) && $pluginOptions['columns'] != '') {
			if (!empty($pluginOptions['columns'])) {
				foreach ($pluginOptions['columns'] as $v) {
					$additionalColumns[] = PhocacartText::filterValue($v, 'alphanumeric3');
				}
			}
		}

		$baseColumns = array('a.id', 'a.title', 'a.image', 'a.alias', 'a.unit_amount', 'a.unit_unit', 'a.description',
			'a.sku', 'a.ean', 'a.upc', 'a.type', 'a.points_received', 'a.price_original',
			'a.stock', 'a.stock_calculation', 'a.min_quantity', 'a.min_multiple_quantity',
			'a.stockstatus_a_id', 'a.stockstatus_n_id','a.date', 'a.sales', 'a.featured',
			'a.external_id', 'a.unit_amount', 'a.unit_unit', 'a.external_link', 'a.external_text', 'a.price', 'a.gift_types');


		$col = array_merge($baseColumns, $additionalColumns);
		$col = array_unique($col);



		if ($count == 1) {
			//$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid';
			$lefts[] = ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id'.$subWherePcCat;
			$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
			$lefts[] = ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id';


			if (!$skip['attributes']) {
				// see below for explanation
				// LEFT JOIN (SELECT id, product_id, MAX(required) AS required FROM jos_phocacart_attributes GROUP BY product_id) AS at ON a.id = at.product_id AND at.id > 0
			    $lefts[] = ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1';
            }

			if (!$skip['group']) {
				$lefts[] = ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3';// type 3 is product
				$lefts[] = ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
			}


			//$query = ' SELECT COUNT(DISTINCT a.id) AS count'; // 2.85ms 0.12mb
			$q = ' SELECT a.id' // 2.42ms 0.12mb
			. ' FROM #__phocacart_products AS a'
			. implode( ' ', $lefts )
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' GROUP BY a.id';

		} else {

			//$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid';
			$lefts[] = ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'.$subWherePcCat;
			$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
			$lefts[] = ' LEFT JOIN #__phocacart_reviews AS r ON a.id = r.product_id AND r.id > 0';
			$lefts[] = ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id';

			if (!$skip['tax']) {
				$lefts[] = ' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id';
			}

			if (!$skip['attributes']) {

				// We need to get information if at least one of the attributes of selected product is required

				// 1) Select more rows - one product is displayed e.g. in two rows
				//$lefts[] = ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0';

				// 2) right solution as it select only the maximal value and if maximal value is 1 then one of product attribute is required
				// LEFT JOIN (SELECT id, product_id, MAX(required) AS required FROM jos_phocacart_attributes GROUP BY product_id) AS at ON a.id = at.product_id AND at.id > 0

				// 3) faster version of 2)
				$lefts[] = ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1';
            }

			if (!$skip['group']) {
				$lefts[] = ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3';// type 3 is product
				$lefts[] = ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
				// user is in more groups, select lowest price by best group
				$lefts[] = ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON a.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN (' . $userGroups . ') AND type = 3)';
				// user is in more groups, select highest points by best group
				$lefts[] = ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON a.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN (' . $userGroups . ') AND type = 3)';
			}


			$columns	= implode(',', $col) . ','
						.' GROUP_CONCAT(DISTINCT c.id) AS catid, GROUP_CONCAT(DISTINCT c.title) AS cattitle,'
						.' GROUP_CONCAT(DISTINCT c.alias) AS catalias,';

			if (!$skip['tax']) {
				$columns	.= ' t.id as taxid, t.tax_rate as taxrate, t.calculation_type as taxcalculationtype, t.title as taxtitle, t.tax_hide as taxhide,';
			} else {
				$columns	.= ' NULL as taxid, NULL as taxrate, NULL as taxcalculationtype, NULL as taxtitle, NULL as taxhide,';
			}

			if (!$skip['attributes']) {
                $columns	.= 'at.required AS attribute_required, ';
            }

			if (!$skip['group']) {
                $columns	.= ' MIN(ppg.price) as group_price, MAX(pptg.points_received) as group_points_received,';
            } else {
                $columns	.= ' NULL as group_price, NULL as group_points_received,';
            }


			$columns	.= ' m.id as manufacturerid, m.title as manufacturertitle, m.alias as manufactureralias,'
						. $selImages
						.' AVG(r.rating) AS rating';


			$groupsFull	= implode(',', $col) ;

			if (!$skip['tax']) {
                $groupsFull	.= ', t.id, t.tax_rate, t.calculation_type, t.title';
            }
			if (!$skip['attributes']) {
                $groupsFull	.= ', at.required';
            }

			$groupsFast	= 'a.id';
			$groups		= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;


			$q = ' SELECT '.$columns
			. ' FROM #__phocacart_products AS a'
			. implode( ' ', $lefts )
			. $leftImages
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' GROUP BY '.$groups
			. ' ORDER BY '.$itemOrdering;
		}


		//echo nl2br(str_replace('#__', 'jos_', $q->__toString()));

		return $q;
	}

	protected function getCategoriesQuery($categoryId, $subcategories = FALSE) {

		$wheres		= array();
		$app		= Factory::getApplication();
		$params 	= $app->getParams();
		$user 		= PhocacartUser::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());

		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));

		// Get the current category or get parent categories of the current category
		if ($subcategories) {
			$wheres[]			= " c.parent_id = ".(int)$categoryId;
			$categoryOrdering 	= $this->getCategoryOrdering();
		} else {
			$wheres[]			= " c.id= ".(int)$categoryId;
		}

		$wheres[] = " c.published = 1";
		$wheres[] = " c.type IN (0,1)";// type: common, onlineshop, pos
		$wheres[] = " c.access IN (".$userLevels.")";
		$wheres[] = " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";

		if ($this->getState('filter.language')) {
			$lang 		= Factory::getLanguage()->getTag();
			$wheres[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
		}

		if ($subcategories) {

			$columns	= 'c.id, c.parent_id, c.title, c.title_long, c.alias, c.image, COUNT(c.id) AS numdoc';
			$groupsFull	= 'c.id, c.parent_id, c.title, c.title_long, c.alias, c.image';
			$groupsFast	= 'c.id';
			$groups		= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

			$query = " SELECT ".$columns
				. " FROM #__phocacart_categories AS c"
				//. " LEFT JOIN #__phocacart_product_categories AS pc ON pc.category_id = c.id"
				//. " LEFT JOIN #__phocacart_products AS a ON a.id = pc.product_id AND a.published = 1 AND a.access IN (".$userLevels.")"
				. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
				. " WHERE " . implode( " AND ", $wheres )
				. " GROUP BY ".$groups
				. " ORDER BY ".$categoryOrdering;
		} else {
			$query = " SELECT c.id, c.parent_id, c.title, c.title_long, c.alias, c.image, c.description, c.metatitle, c.metakey, c.metadesc, c.metadata, cc.title as parenttitle, c.parent_id as parentid, cc.alias as parentalias"
				. " FROM #__phocacart_categories AS c"
				. " LEFT JOIN #__phocacart_categories AS cc ON cc.id = c.parent_id"
				. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
				. " WHERE " . implode( " AND ", $wheres )
				. " ORDER BY c.ordering";
		}
		//echo nl2br(str_replace('#__', 'jos_', $query));
		return $query;
	}


	protected function getItemOrdering() {
		if (empty($this->item_ordering)) {
			$app						= Factory::getApplication();
			$params						= $app->getParams();
			//$ordering					= $params->get( 'item_ordering', 1 );
			$ordering					= $this->getState('itemordering');
			$this->item_ordering 		= PhocacartOrdering::getOrderingText($ordering);
		}
		return $this->item_ordering;
	}

	protected function getCategoryOrdering() {
		if (empty($this->category_ordering)) {
			$app						= Factory::getApplication();
			$params						= $app->getParams();
			$ordering					= $params->get( 'category_ordering', 1 );
			$this->category_ordering 	= PhocacartOrdering::getOrderingText($ordering, 1);
		}
		return $this->category_ordering;
	}

	public function hit($pk = 0) {
		$input = Factory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount) {
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('cateogry.id');

			$table = Table::getInstance('PhocacartCategory', 'Table');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}
}
?>
