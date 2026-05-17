<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */


use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Phoca\PhocaCart\Constants\GroupType;
use Phoca\PhocaCart\Constants\ProductType;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;
use Phoca\PhocaCart\I18n\I18nHelper;
//use PhocacartCalculation;

defined('_JEXEC') or die();

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
		$item_pagination	= (int)$paramsC->get( 'item_pagination_default', '20' );
		$item_ordering		= $paramsC->get( 'item_ordering', 1 );
		$layout_type		= $paramsC->get( 'layout_type', 'grid' );

		// Items View Menu link parameters
		$items_view_id_cats	= $paramsC->get( 'items_view_id_cats', array() );
		$this->items_layout_plugin	= $paramsC->get( 'items_layout_plugin', '' );


		$manufacturer_alias	= $paramsC->get( 'manufacturer_alias', 'manufacturer');
		$manufacturer_alias	= $manufacturer_alias != '' ? trim(PhocacartText::filterValue($manufacturer_alias, 'alphanumeric'))  : 'manufacturer';

		$limit					= PhocacartPagination::getMaximumLimit($app->getUserStateFromRequest('com_phocacart.limit', 'limit', $item_pagination, 'int'));

		$this->setState('limit', $limit);
		$this->setState('limitstart', $app->getInput()->get('limitstart', 0, 'int'));
		$this->setState('limitstart', ($this->getState('limit') != 0 ?  (int)(floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));

		$this->setState('filter.language',$app->getLanguageFilter());
		$this->setState('filter_order', $app->getInput()->get('filter_order', 'ordering'));
		$this->setState('filter_order_dir', $app->getInput()->get('filter_order_Dir', 'ASC'));
		$this->setState('itemordering', $app->getUserStateFromRequest('com_phocacart.itemordering', 'itemordering', $item_ordering, 'int'));
		$this->setState('layouttype', $app->getUserStateFromRequest('com_phocacart.layouttype', 'layouttype', $layout_type, 'string'));

		// =FILTER=
		$this->setState('tag', $app->getInput()->get('tag', '', 'string'));
		$this->setState('label', $app->getInput()->get('label', '', 'string'));
		$this->setState('manufacturer', $app->getInput()->get($manufacturer_alias, '', 'string'));
		$this->setState('price_from', $app->getInput()->get('price_from', '', 'float'));
		$this->setState('price_to', $app->getInput()->get('price_to', '', 'float'));

		// CATEGORIES
		// 1) there can be set one category per ID
		// 2) there can be set more categories per c parameter
		// 3) there can be set more categories by menu link parameters. If menu link parameter is used, then 2) is deactivated
		//    because if somebody wants to force displaying only some categories, another cannot be displayed e.g. per URL parameters
		//    E.g. we want to display only category 1 and 3 in items view and user even set c=1,2,3 in URL - so the 2 will be just ignored

		// 1)
		$this->setState('id', $app->getInput()->get('id', '', 'int')); // Category ID (Active Category)

		// 2) 3)
		if (!empty($items_view_id_cats)) {
			$this->setState('c', implode(',', $items_view_id_cats));
		} else {
			$this->setState('c', $app->getInput()->get('c', '', 'string')); // Category More (All Categories)
		}


		$this->setState('a', $app->getInput()->get('a', '', 'array')); // Attributes
		$this->setState('s', $app->getInput()->get('s', '', 'array')); // Specifications
		$parameters = PhocacartParameter::getAllParameters();
		$this->setState('parameter', $parameters);

		// =SEARCH=
		$this->setState('search', $app->getInput()->get('search', '', 'string'));

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

		// Joomla 5.1.3 4.4.7 ???
		// Default:     format option view layout tpl id Itemid
		$app 		= Factory::getApplication();
		$state      = $this->getState();

		$paramsC 			= $app->getParams();
		$manufacturer_alias	= $paramsC->get( 'manufacturer_alias', 'manufacturer');
		$manufacturer_alias	= $manufacturer_alias != '' ? trim(PhocacartText::filterValue($manufacturer_alias, 'alphanumeric'))  : 'manufacturer';

		$this->pagination->hideEmptyLimitstart = true;

		if (!empty($this->pagination)) {
			if ($state->get('price_from') != '') { $this->pagination->setAdditionalUrlParam('price_from', $state->get('price_from'));}
			if ($state->get('price_to') != '') { $this->pagination->setAdditionalUrlParam('price_to', $state->get('price_to'));}
			//if ($state->get('limit') != '') { $this->pagination->setAdditionalUrlParam('limit', $state->get('limit'));}
			//if ($state->get('limitstart') != '') { $this->pagination->setAdditionalUrlParam('limitstart', $state->get('limitstart'));}
			if ($state->get('tag') != '') { $this->pagination->setAdditionalUrlParam('tag', $state->get('tag'));}
			if ($state->get('label') != '') { $this->pagination->setAdditionalUrlParam('label', $state->get('label'));}
			if ($state->get('manufacturer') != '') { $this->pagination->setAdditionalUrlParam($manufacturer_alias, $state->get('manufacturer'));}
			if ($state->get('c') != '') { $this->pagination->setAdditionalUrlParam('c', $state->get('c'));}
			if ($state->get('a') != '') { $this->pagination->setAdditionalUrlParam('a', $state->get('a'));}
			if ($state->get('s') != '') { $this->pagination->setAdditionalUrlParam('s', $state->get('s'));}
			//if ($state->get('parameter') != '') { $this->pagination->setAdditionalUrlParam('parameter', $state->get('parameter'));}
			if ($this->getState('parameter')) {
				foreach ($this->getState('parameter') as $k => $v) {
					$alias = trim(PhocacartText::filterValue($v->alias, 'alphanumeric'));
					$parameter = $app->getInput()->get($alias, '', 'string');
					if($parameter != '') {
						$this->pagination->setAdditionalUrlParam($v->alias, $parameter);
					}
				}
			}
			if ($state->get('search') != '') { $this->pagination->setAdditionalUrlParam('search', $state->get('search'));}

			if ($state->get('id') == 0 || $state->get('id') == '') {
				$this->pagination->setAdditionalUrlParam('id', null);
			}
			if ($state->get('id') != '') { $this->pagination->setAdditionalUrlParam('id', $state->get('id'));}

			// Format is sent per POST - can be removed if needed, works only with patch: https://issues.joomla.org/tracker/joomla-cms/44023
			$this->pagination->setAdditionalUrlParam('format', null);
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
			$query = $this->getItemListQuery(true);
			$this->total = $this->_getListCount($query);
		}
		return $this->total;
	}

	public function getItemList() {
		if (empty($this->item)) {
			$query			= $this->getItemListQuery( );
			$this->item		= $this->_getList( $query ,$this->getState('limitstart'), $this->getState('limit'));

			if (!empty($this->item)) {
				foreach ($this->item as $v) {
					PhocacartCalculation::changePrice($v);
				}
			}

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

	private function dispatchItemsLayout(array &$ordering, array &$columns): void
	{
		if (!$this->items_layout_plugin) {
			return;
		}

		$plugin = PhocacartText::filterValue($this->items_layout_plugin, 'alphanumeric2');
		$pluginOptions = [];

		Dispatcher::dispatch(new Event\Layout\Items\GetOptions('com_phocacart.category', $pluginOptions, [
			'pluginname' => $plugin,
		]));

		$pluginOrdering = PhocacartText::filterValue($pluginOptions['ordering'] ?? '', 'alphanumeric5');
		$pluginColumns = $pluginOptions['columns'] ?? [];
		array_walk($pluginColumns, function($column) {
			return PhocacartText::filterValue($column, 'alphanumeric3');
		});

		if ($pluginOrdering) {
			array_unshift($ordering, $pluginOrdering);
		}

		$columns = array_merge($columns, $pluginColumns);
	}

	private function dispatchLoadColumns(array &$columns)
	{
		$pluginOptions = [];
		Dispatcher::dispatch(new Event\View\Items\BeforeLoadColumns('com_phocacart.items', $pluginOptions));

		$pluginColumns = $pluginOptions['columns'] ?? [];
		array_walk($pluginColumns, function($column) {
			return PhocacartText::filterValue($column, 'alphanumeric3');
		});

		$columns = array_merge($columns, $pluginColumns);
	}

	protected function getItemListQuery(bool $isCountQuery = false)
	{
		$app		= Factory::getApplication();
		$user 		= PhocacartUser::getUser();
		$params 	= $app->getParams();
		$lang 		= $app->getLanguage()->getTag();

		if ($this->getState('search')) {
			// Hit only one time
			if (!$isCountQuery) {
				PhocacartStatisticsHits::searchHit($this->getState('search'));
			}
		}

		// Help parameter because we use one search.php for online shop and for POS
		// And this parameter changes based on where it is run (if in online shop [1,2] or POS [1,3])
		$params->set('sql_search_skip_id_specific_type', 1);// POS or Online Shop (Online Shop)
		if ((int)$params->get('sql_search_skip_id') != 1 && (int)$params->get('sql_search_skip_id') != 2){
			$params->set('sql_search_skip_id_specific_type', 0);

		}

		$searchParams = [
			'hide_products_out_of_stock' => $params->get( 'hide_products_out_of_stock', 0),
			'switch_image_category_items' => $params->get( 'switch_image_category_items', 0 ),
			'join_tag_label_filter' => $params->get( 'join_tag_label_filter', 0 ),
			'search_matching_option' => $params->get( 'search_matching_option', 'any'),
			'search_deep' => $params->get( 'search_deep', 0),
			'sql_search_skip_id' => (int)$params->get( 'sql_search_skip_id', 1),
			'search_custom_fields' => $params->get( 'search_custom_fields', 0 ),
			'sql_search_skip_id_specific_type' => (int)$params->get('sql_search_skip_id_specific_type'),
			'sql_filter_method_tag' => $params->get('sql_filter_method_tag', 0),
			'sql_filter_method_label' => $params->get('sql_filter_method_label', 0),
			'sql_filter_method_parameter' => $params->get('sql_filter_method_parameter', 0),
			'sql_filter_method_attribute' => $params->get('sql_filter_method_attribute', 0),
			'sql_filter_method_specification' => $params->get('sql_filter_method_specification', 0),

		];

		$where		= [];
		$join		= [];

		$where[] = ' a.published = 1';
		$where[] = ' c.published = 1';

		if (!$params->get('sql_products_skip_category_type', false)) {
			$where[] = 'c.type IN (' . implode(', ', [ProductType::Common, ProductType::Shop]) . ')';
        }

		if ($this->getState('filter.language')) {
			$where[] 	= PhocacartUtilsSettings::getLangQuery('a.language', $lang);
			$where[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
		}

		if (!$params->get('sql_products_skip_access', false)) {
			$userLevels	= $user->getAuthorisedViewLevels();
			$where[] = ' c.access IN (' . implode(', ', $userLevels) . ')';
			$where[] = ' a.access IN (' . implode(', ', $userLevels) . ")";
		}

		$userGroups = PhocacartGroup::getGroupsById($user->id, GroupType::User, 1);
		if (!$params->get('sql_products_skip_group', false)) {
			$where[] = ' (ga.group_id IN (' . implode(', ', $userGroups) . ') OR ga.group_id IS NULL)';
			$where[] = ' (gc.group_id IN (' . implode(', ', $userGroups) . ') OR gc.group_id IS NULL)';
		}

		if ($searchParams['hide_products_out_of_stock'] == 1) {
			$where[] = 'a.stock > 0';
		}

		// =FILTER=
		// -TAG- -LABEL-
		if ($searchParams['join_tag_label_filter'] == 1) {
			// -TAG-
			$wheresTL = [];
			if ($this->getState('tag')) {
				$s = PhocacartSearch::getSqlParts('int', 'tag', $this->getState('tag'), $searchParams);
				$wheresTL[]	= $s['where'];
				$join[]	= $s['left'];
			}

			// -LABEL-
			if ($this->getState('label')) {
				$s = PhocacartSearch::getSqlParts('int', 'label', $this->getState('label'), $searchParams);
				$wheresTL[]	= $s['where'];
				$join[]	= $s['left'];
			}

			if ($this->getState('tag') || $this->getState('label')) {
				$wheresTL = array_filter($wheresTL);

				if ($wheresTL) {
					$where[] = '(' . implode(' OR ', $wheresTL) . ')';
				}
			}
		} else {
			// -TAG-
			if ($this->getState('tag')) {
				$s = PhocacartSearch::getSqlParts('int', 'tag', $this->getState('tag'), $searchParams);
				$where[] = $s['where'];
				$join[]	= $s['left'];
			}

			// -LABEL-
			if ($this->getState('label')) {
				$s = PhocacartSearch::getSqlParts('int', 'label', $this->getState('label'), $searchParams);
				$where[] = $s['where'];
				$join[]	= $s['left'];
			}
		}

		if ($this->getState('parameter')) {
			foreach ($this->getState('parameter') as $k => $v) {
				$alias = trim(PhocacartText::filterValue($v->alias, 'alphanumeric'));
				$parameter = $app->getInput()->get($alias, '', 'string');

				if($parameter != '') {
					$s = PhocacartSearch::getSqlParts('int', 'parameter', $parameter, $searchParams, $v->id);
					$where[] = $s['where'];// There must be AND between custom parameters
					$join[] = $s['left'];
				}
			}
		}


		// -MANUFACTURER-
		if ($this->getState('manufacturer')) {
			$s = PhocacartSearch::getSqlParts('int', 'manufacturer', $this->getState('manufacturer'));
			$where[] = $s['where'];
			$join[]	= $s['left'];
		}

		// -PRICE-
		if ($this->getState('price_from')) {
			$s = PhocacartSearch::getSqlParts('int', 'price_from', $this->getState('price_from'));
			$where[] = $s['where'];
			$join[]	= $s['left'];
		}
		if ($this->getState('price_to')) {
			$s = PhocacartSearch::getSqlParts('int', 'price_to', $this->getState('price_to'));
			$where[] = $s['where'];
			$join[]	= $s['left'];
		}

		// -CATEGORY-
		if ($this->getState('id')) {
			$s = PhocacartSearch::getSqlParts('int', 'id', $this->getState('id'));
			$where[] = $s['where'];
			$join[]	= $s['left'];
		}

		// -CATEGORY MORE-
		if ($this->getState('c')) {
			$s = PhocacartSearch::getSqlParts('int', 'c', $this->getState('c'));
			$where[] = $s['where'];
			$join[]	= $s['left'];
		}

		// -ATTRIBUTES-
		if ($this->getState('a')) {
			$s = PhocacartSearch::getSqlParts('array', 'a', $this->getState('a'), $searchParams);
			$where[] = $s['where'];
			$join[]	= $s['left'];
		}

		// -SPECIFICATIONS-
		if ($this->getState('s')) {
			$s = PhocacartSearch::getSqlParts('array', 's', $this->getState('s'), $searchParams);
			$where[] = $s['where'];
			$join[]	= $s['left'];
		}

		// =SEARCH=
		if ($this->getState('search')) {
			$s = PhocacartSearch::getSqlParts('string', 'search', $this->getState('search'), $searchParams);
			$where[] = '(' . $s['where'] . ')';
			$join[]	= $s['left'];
		}

		// Remove empty values:
		$where = array_filter($where);
		$join = array_filter($join);

		$join[] = ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id';
		$join[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
		$join[] = ' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = a.manufacturer_id';

		if ((int)$searchParams['sql_search_skip_id_specific_type'] == 0){
			$join[] = ' LEFT JOIN #__phocacart_product_stock AS ps ON a.id = ps.product_id';// search sku ean in advanced stock management
		}

		if (!$params->get('sql_products_skip_attributes', false)) {
			$join[] = ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1';
		}

		if (!$params->get('sql_products_skip_group', false)) {
			$join[] = ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = ' . GroupType::Product;
			$join[] = ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = ' . GroupType::Category;
		}

		if (I18nHelper::isI18n()) {
			$join[] = I18nHelper::sqlJoin('#__phocacart_products_i18n');
			$join[] = I18nHelper::sqlJoin('#__phocacart_manufacturers_i18n', 'm');
		}

		if ($isCountQuery) {
			$query = ' SELECT a.id'
			. ' FROM #__phocacart_products AS a'
			. ' ' . implode( ' ', $join)
			. ' WHERE ' . implode(' AND ', $where)
			. ' GROUP BY a.id';

		} else {
			$join[] = ' LEFT JOIN #__phocacart_reviews AS r ON a.id = r.product_id AND r.id > 0';

			if (!$params->get('sql_products_skip_tax', false)) {
				$join[] = ' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id';
				$join[] = I18nHelper::sqlJoin('#__phocacart_taxes_i18n', 't');
			}

			if (!$params->get('sql_products_skip_group', false)) {
				// user is in more groups, select lowest price by best group
				$join[] = ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON a.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN (' . implode(', ', $userGroups) . ') AND type = ' . GroupType::Product . ')';
				// user is in more groups, select highest points by best group
				$join[] = ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON a.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN (' . implode(', ', $userGroups) . ') AND type = ' . GroupType::Product . ')';
			}

			$ordering = PhocacartOrdering::getOrdering($this->getState('itemordering'), 0, true);
			$columns = [
				'a.id', 'a.image', 'a.unit_amount', 'a.unit_unit',
				'a.sku', 'a.ean', 'a.upc', 'a.type', 'a.points_received', 'a.price_original',
				'a.stock', 'a.stock_calculation', 'a.min_quantity', 'a.min_multiple_quantity', 'a.max_quantity',
				'a.stockstatus_a_id', 'a.stockstatus_n_id','a.date', 'a.sales', 'a.featured',
				'a.external_id', 'a.unit_amount', 'a.unit_unit', 'a.external_link', 'a.external_text', 'a.price', 'a.gift_types',
				'a.subscription_period', 'a.subscription_unit', 'a.subscription_signup_fee', 'a.subscription_renewal_discount', 'a.subscription_renewal_discount_calculation_type', 'a.subscription_grace_period_days'
            ];

			$this->dispatchItemsLayout($ordering, $columns);
			$this->dispatchLoadColumns($columns);

			$columns = array_unique($columns);
			if (PhocacartUtilsSettings::isFullGroupBy()) {
				$groupBy = $columns;
				if (!$params->get('sql_products_skip_tax', false)) {
					$groupBy[] = 't.id';
					$groupBy[] = 't.tax_rate';
					$groupBy[] = 't.calculation_type';
					$groupBy[] = 't.title';
				}

				if (!$params->get('sql_products_skip_attributes', false)) {
					$groupBy[] = 'at.required';
				}

				$groupBy[] = 'a.title';
				$groupBy[] = 'a.alias';
				$groupBy[] = 'a.description';

				if (I18nHelper::isI18n()) {
					$groupBy[] = 'i18n_a.title';
					$groupBy[] = 'i18n_a.alias';
					$groupBy[] = 'i18n_a.description';
				}
			} else {
				$groupBy = ['a.id'];
			}

			$columns[] = 'm.id as manufacturerid';
			//if (I18nHelper::isI18n()) {
				$columns[] = I18nHelper::sqlCoalesce(['title']);
				$columns[] = I18nHelper::sqlCoalesce(['alias']);
				$columns[] = I18nHelper::sqlCoalesce(['description']);
				$columns[] = I18nHelper::sqlCoalesce(['title'], 'm', 'manufacturer');
				$columns[] = I18nHelper::sqlCoalesce(['alias'], 'm', 'manufacturer');
			/*} else {
				$columns[] = 'a.title';
				$columns[] = 'a.alias';
				$columns[] = 'a.description';
				$columns[] = 'm.title as manufacturertitle';
				$columns[] = 'm.alias as manufactureralias';
			}*/

			if ($searchParams['switch_image_category_items']) {
				$columns[] = '(SELECT im.image FROM #__phocacart_product_images im WHERE im.product_id = a.id ORDER BY im.ordering LIMIT 1) as additional_image';
			}

			$columns[] = 'GROUP_CONCAT(DISTINCT c.id) AS catid';
			$columns[] = 'GROUP_CONCAT(DISTINCT c.title) AS cattitle';
			$columns[] = 'GROUP_CONCAT(DISTINCT c.alias) AS catalias';
			$columns[] = 'a.catid AS preferred_catid';

			if (!$params->get('sql_products_skip_tax', false)) {
				$columns[] = 't.id as taxid';
				$columns[] = 't.tax_rate as taxrate';
				$columns[] = 't.calculation_type as taxcalculationtype';
				//$columns[] = 't.title as taxtitle';
				$columns[] = I18nHelper::sqlCoalesce(['title'], 't', 'tax');
				$columns[] = 't.tax_hide as taxhide';
			} else {
				$columns[] = 'NULL as taxid';
				$columns[] = 'NULL as taxrate';
				$columns[] = 'NULL as taxcalculationtype';
				$columns[] = 'NULL as taxtitle';
				$columns[] = 'NULL as taxhide';
			}

			if (!$params->get('sql_products_skip_attributes', false)) {
				$columns[] = 'at.required AS attribute_required';
			} else {
				$columns[] = '0 AS attribute_required';
			}

			if (!$params->get('sql_products_skip_group', false)) {
				$columns[] = 'MIN(ppg.price) as group_price';
				$columns[] = 'MAX(pptg.points_received) as group_points_received';
			} else {
				$columns[] = 'NULL as group_price';
				$columns[] = 'NULL as group_points_received';
			}

			$columns[] = 'AVG(r.rating) AS rating';

			$query = 'SELECT ' . implode(', ', $columns)
				. ' FROM #__phocacart_products AS a'
				. ' ' . implode(' ', $join)
				. ' WHERE ' . implode(' AND ', $where)
				. ' GROUP BY ' . implode(', ', $groupBy)
				. ' ORDER BY ' . implode(', ', $ordering);
		}

		return $query;
	}

	protected function getCategoriesQuery( $categoryId, $subcategories = false)
	{
		$wheres		= array();
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
