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

class PhocaCartModelItems extends BaseDatabaseModel
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
	protected $items_layout_plugin	= '';

	public function __construct() {
		parent::__construct();


		$app				= Factory::getApplication();
		$config 			= Factory::getConfig();
		$paramsC 			= $app->getParams();
		$item_pagination	= $paramsC->get( 'item_pagination_default', '20' );
		$item_ordering		= $paramsC->get( 'item_ordering', 1 );
		$layout_type		= $paramsC->get( 'layout_type', 'grid' );

		// Items View Menu link parameters
		$items_view_id_cats	= $paramsC->get( 'items_view_id_cats', array() );
		$this->items_layout_plugin	= $paramsC->get( 'items_layout_plugin', '' );


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

		// =FILTER=
		$this->setState('tag', $app->input->get('tag', '', 'string'));
		$this->setState('label', $app->input->get('label', '', 'string'));
		$manufacturerParameter = '';
		$this->setState('manufacturer', $app->input->get($manufacturer_alias, '', 'string'));
		$this->setState('price_from', $app->input->get('price_from', '', 'float'));
		$this->setState('price_to', $app->input->get('price_to', '', 'float'));

		// CATEGORIES
		// 1) there can be set one category per ID
		// 2) there can be set more categories per c parameter
		// 3) there can be set more categories by menu link parameters. If menu link parameter is used, then 2) is deactivated
		//    because if somebody wants to force displaying only some categories, another cannot be displayed e.g. per URL parameters
		//    E.g. we want to display only category 1 and 3 in items view and user even set c=1,2,3 in URL - so the 2 will be just ignored

		// 1)
		$this->setState('id', $app->input->get('id', '', 'int')); // Category ID (Active Category)

		// 2) 3)
		if (!empty($items_view_id_cats)) {
			$this->setState('c', implode(',', $items_view_id_cats));
		} else {
			$this->setState('c', $app->input->get('c', '', 'string')); // Category More (All Categories)
		}


		$this->setState('a', $app->input->get('a', '', 'array')); // Attributes
		$this->setState('s', $app->input->get('s', '', 'array')); // Specifications
		$parameters = PhocacartParameter::getAllParameters();
		$this->setState('parameter', $parameters);

		// =SEARCH=
		$this->setState('search', $app->input->get('search', '', 'string'));

	}

	public function getLayoutType() {
		$layoutType 	= $this->getState('layouttype');
		$layoutType		= PhocacartRenderFront::getLayoutType($layoutType);
		return $layoutType;
	}

	public function getPagination() {
		if (empty($this->pagination)) {
			jimport('joomla.html.pagination');
			$this->pagination = new PhocacartPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->pagination;
	}

	function getOrdering() {
		if(empty($this->ordering)) {
			$this->ordering = PhocacartOrdering::renderOrderingFront($this->getState('itemordering'), 0);
		}
		return $this->ordering;
	}

	public function getTotal() {
		if (empty($this->total)) {
			$query = $this->getItemListQuery(1);
			$this->total = $this->_getListCount($query);
		}
		return $this->total;
	}

	public function getItemList() {
		if (empty($this->item)) {
			$query			= $this->getItemListQuery( );
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

	protected function getItemListQuery($count = 0) {

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
		$p['hide_products_out_of_stock']	= $params->get( 'hide_products_out_of_stock', 0);
		$p['switch_image_category_items']	= $params->get( 'switch_image_category_items', 0 );
		$p['join_tag_label_filter']			= $params->get( 'join_tag_label_filter', 0 );
		$p['search_matching_option']		= $params->get( 'search_matching_option', 'any' );
		$p['search_deep']					= $params->get( 'search_deep', 0);
		$p['sql_search_skip_id']			= $params->get( 'sql_search_skip_id', 1 );
		$p['search_custom_fields']			= $params->get( 'search_custom_fields', 0 );

		$p['sql_search_skip_id_specific_type'] = 1;// POS or Online Shop (Online Shop)
		if ($p['sql_search_skip_id'] != 1 && $p['sql_search_skip_id'] != 2){
			$p['sql_search_skip_id_specific_type'] = 0;

		}

		$p['sql_filter_method_tag']				= $params->get('sql_filter_method_tag', 0);
		$p['sql_filter_method_label']			= $params->get('sql_filter_method_label', 0);
		$p['sql_filter_method_parameter']		= $params->get('sql_filter_method_parameter', 0);
		$p['sql_filter_method_attribute']		= $params->get('sql_filter_method_attribute', 0);
		$p['sql_filter_method_specification']	= $params->get('sql_filter_method_specification', 0);

		$wheres		= array();
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

		// =FILTER=
		// -TAG- -LABEL-
		if ($p['join_tag_label_filter'] == 1) {

			// -TAG-
			$wheresTL = array();
			if ($this->getState('tag')) {
				$s = PhocacartSearch::getSqlParts('int', 'tag', $this->getState('tag'), $p);
				$wheresTL[]	= $s['where'];
				$lefts[]	= $s['left'];
			}
			// -LABEL-
			if ($this->getState('label')) {
				$s = PhocacartSearch::getSqlParts('int', 'label', $this->getState('label'), $p);
				$wheresTL[]	= $s['where'];
				$lefts[]	= $s['left'];
			}

			if ($this->getState('tag') || $this->getState('label')) {
				$startP = '';
				$endP 	= '';
				if (count($wheresTL) > 1) {
					$startP = '(';
					$endP 	= ')';
				}


				if (!empty($wheresTL)) {
					$wheresTL = array_filter($wheresTL);
					if (!empty($wheresTL)) {
						$wheres[] = $startP . implode(' OR ', $wheresTL) . $endP;
					}
				}
			}
		} else {

			// -TAG-
			if ($this->getState('tag')) {
				$s = PhocacartSearch::getSqlParts('int', 'tag', $this->getState('tag'), $p);
				$wheres[]	= $s['where'];
				$lefts[]	= $s['left'];

			}
			// -LABEL-
			if ($this->getState('label')) {
				$s = PhocacartSearch::getSqlParts('int', 'label', $this->getState('label'), $p);
				$wheres[]	= $s['where'];
				$lefts[]	= $s['left'];
			}

		}

		// -PARAMETER
		// Custom parameters set by user in administrator
		// All custom parameters are stored in one table so they are unique
		// So we can use one left for all parameters

		/*if ($this->getState('parameter')) {
			$parameterValues = array();
			foreach ($this->getState('parameter') as $k => $v) {
				$alias = PhocacartText::filterValue($v->alias, 'url');
				$parameter = $app->input->get($alias, '', 'string');

				if($parameter != '') {
					$parameterValues[] = $parameter;
				}
			}
			if (!empty($parameterValues)) {
				$parameterValuesString = implode(',', $parameterValues);//Join all custom parameters together because of SQL query - all should be in one IN(): AND pr.parameter_id IN (1,2,3)
				if ($parameterValuesString != '') {
					$s = PhocacartSearch::getSqlParts('int', 'parameter', $parameterValuesString);
					$wheres[] = $s['where'];
					$lefts[] = $s['left'];

				}
			}
		}*/

		if ($this->getState('parameter')) {
			//$leftOnce = 0;
			foreach ($this->getState('parameter') as $k => $v) {
				$alias = trim(PhocacartText::filterValue($v->alias, 'alphanumeric'));
				$parameter = $app->input->get($alias, '', 'string');

				if($parameter != '') {
					$s = PhocacartSearch::getSqlParts('int', 'parameter', $parameter, $p, $v->id);
					$wheres[] = $s['where'];// There must be AND between custom parameters
					//if ($leftOnce < 1) {
						$lefts[] = $s['left'];
						//$leftOnce = 1;
					//}
				}
			}

		}


		// -MANUFACTURER-
		if ($this->getState('manufacturer')) {
			$s = PhocacartSearch::getSqlParts('int', 'manufacturer', $this->getState('manufacturer'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}
		// -PRICE-
		if ($this->getState('price_from')) {
			$s = PhocacartSearch::getSqlParts('int', 'price_from', $this->getState('price_from'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}
		if ($this->getState('price_to')) {
			$s = PhocacartSearch::getSqlParts('int', 'price_to', $this->getState('price_to'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}

		// -CATEGORY-
		if ($this->getState('id')) {
			$s = PhocacartSearch::getSqlParts('int', 'id', $this->getState('id'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}

		// -CATEGORY MORE-
		if ($this->getState('c')) {
			$s = PhocacartSearch::getSqlParts('int', 'c', $this->getState('c'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}

		// -ATTRIBUTES-
		if ($this->getState('a')) {
			$s = PhocacartSearch::getSqlParts('array', 'a', $this->getState('a'), $p);
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}

		// -SPECIFICATIONS-
		if ($this->getState('s')) {
			$s = PhocacartSearch::getSqlParts('array', 's', $this->getState('s'), $p);
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}

		// =SEARCH=
		if ($this->getState('search')) {
			$s = PhocacartSearch::getSqlParts('string', 'search', $this->getState('search'), $p);
			$wheres[]	= '('.$s['where'].')';
			$lefts[]	= $s['left'];

			// Hit only one time
			if ($count == 0) {
				PhocacartStatisticsHits::searchHit($this->getState('search'));
			}
		}

		// Additional Images
		$leftImages = '';
		$selImages = '';

		if ($p['switch_image_category_items'] == 1) {
			$leftImages = ' LEFT JOIN #__phocacart_product_images AS im ON a.id = im.product_id';
			$selImages	= ' GROUP_CONCAT(im.image) as additional_image,';

		}

		// Items Layout Plugin can change ordering
		// Items Layout Plugin can load additional columns
		$additionalColumns = array();
		if ($this->items_layout_plugin != '') {
			$this->items_layout_plugin = PhocacartText::filterValue($this->items_layout_plugin, 'alphanumeric2');
			$pluginLayout 	= PluginHelper::importPlugin('pcl', $this->items_layout_plugin);
			if ($pluginLayout) {
				$pluginOptions 				= array();
				$eventData 					= array();
				$eventData['pluginname'] 	= $this->items_layout_plugin;
				Factory::getApplication()->triggerEvent('onPCLonItemsGetOptions', array('com_phocacart.items', &$pluginOptions, $eventData));

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
		}

		// Views Plugin can load additional columns
		$pluginLayout 	= PluginHelper::importPlugin('pcv');
		if ($pluginLayout) {
			$pluginOptions 				= array();
			$eventData 					= array();
			Factory::getApplication()->triggerEvent('onPCVonItemsBeforeLoadColumns', array('com_phocacart.items', &$pluginOptions, $eventData));

			if (isset($pluginOptions['columns']) && $pluginOptions['columns'] != '') {
				if (!empty($pluginOptions['columns'])) {
					foreach ($pluginOptions['columns'] as $k => $v) {
						$additionalColumns[] = PhocacartText::filterValue($v, 'alphanumeric3');
					}
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


		// Remove empty values:
		$wheres = array_filter($wheres);
		$lefts	= array_filter($lefts);

		if ($count == 1) {
			//$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid';
			$lefts[] = ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id';
			$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
			$lefts[] = ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id';

			if ($p['sql_search_skip_id_specific_type'] == 0){
				$lefts[] = ' LEFT JOIN #__phocacart_product_stock AS ps ON a.id = ps.product_id';// search sku ean in advanced stock management
			}


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
			$lefts[] = ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id';
			$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
			$lefts[] = ' LEFT JOIN #__phocacart_reviews AS r ON a.id = r.product_id AND r.id > 0';
			$lefts[] = ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id';

			if ($p['sql_search_skip_id_specific_type'] == 0){
				$lefts[] = ' LEFT JOIN #__phocacart_product_stock AS ps ON a.id = ps.product_id';// search sku ean in advanced stock management
			}

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
						.' GROUP_CONCAT(DISTINCT c.alias) AS catalias, a.catid AS preferred_catid,';

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

		//echo "<br><br>" . nl2br(str_replace('#__', 'jos_', $q));

		return $q;
	}

	protected function getCategoriesQuery( $categoryId, $subcategories = FALSE ) {

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
			$wheres[]	= " c.id= ".(int)$categoryId;
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

			$columns	= 'c.id, c.title, c.alias, COUNT(c.id) AS numdoc';
			$groupsFull	= 'c.id, c.title, c.alias';
			$groupsFast	= 'c.id';
			$groups		= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

			$query = "SELECT ".$columns
				. " FROM #__phocacart_categories AS c"
				. " LEFT JOIN #__phocacart_products AS a ON a.catid = c.id AND a.published = 1"
				. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
				. " WHERE " . implode( " AND ", $wheres )
				. " GROUP BY ".$groups
				. " ORDER BY ".$categoryOrdering;
		} else {
			$query = " SELECT c.id, c.title, c.alias, c.description, c.metatitle, c.metakey, c.metadesc, c.metadata, cc.title as parenttitle, c.parent_id as parentid, cc.alias as parentalias"
				. " FROM #__phocacart_categories AS c"
				. " LEFT JOIN #__phocacart_categories AS cc ON cc.id = c.parent_id"
				. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
				. " WHERE " . implode( " AND ", $wheres )
				. " ORDER BY c.ordering";
		}
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
}
?>
