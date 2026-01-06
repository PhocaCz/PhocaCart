<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
jimport('joomla.application.component.model');
use Joomla\Utilities\ArrayHelper;

class PhocaCartModelPos extends BaseDatabaseModel
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

	public function __construct() {
		parent::__construct();

		$app				= Factory::getApplication();
		$config 			= Factory::getConfig();
		$paramsC 			= $app->getParams();
		$item_pagination	= (int)$paramsC->get( 'pos_pagination_default', 24 );
		$item_ordering		= $paramsC->get( 'pos_ordering', 1 );

		$manufacturer_alias	= $paramsC->get( 'manufacturer_alias', 'manufacturer');
		$manufacturer_alias = $manufacturer_alias != '' ? trim(PhocacartText::filterValue($manufacturer_alias, 'alphanumeric'))  : 'manufacturer';

		$this->setState('page', $app->getInput()->get('page', 'main.content.products'));
		//$limit					= PhocacartPagination::getMaximumLimit($app->getUserStateFromRequest('com_phocacart.limit', 'limit', $item_pagination, 'int'), 1);

		$toDay = date('Y-m-d');
		$this->setState('date', $app->getInput()->get('date', $toDay, 'string'));

		$limitId 		= 'com_phocacart.'.$this->getState('page').'.limit';
		$limitStartId 	= 'com_phocacart.'.$this->getState('page').'.limitstart';
		$orderingId 	= 'com_phocacart.'.$this->getState('page').'.itemordering';

		switch($this->getState('page')){

			case 'section':
				$limit	= 0;
				$limitStart = 0;
			break;

			default:


				//$limitStart	= $app->getUserStateFromRequest($limitStartId, 'limit',0, 'int');

				$limit		= PhocacartPagination::getMaximumLimit($app->getUserStateFromRequest($limitId, 'limit', $item_pagination, 'int'), 1);

			break;
		}

		$this->setState('limit', $limit);

		$this->setState('limitstart', $app->getInput()->get('limitstart', 0, 'int'));
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));


		$this->setState('filter.language',$app->getLanguageFilter());
		$this->setState('filter_order', $app->getInput()->get('filter_order', 'ordering'));
		$this->setState('filter_order_dir', $app->getInput()->get('filter_order_Dir', 'ASC'));
		$this->setState('itemordering', $app->getUserStateFromRequest($orderingId, 'itemordering', $item_ordering, 'int'));






		// =FILTER=
		$this->setState('tag', $app->getInput()->get('tag', '', 'string'));
        $this->setState('label', $app->getInput()->get('label', '', 'string'));
		$this->setState('manufacturer', $app->getInput()->get($manufacturer_alias, '', 'string'));
		$this->setState('price_from', $app->getInput()->get('price_from', '', 'float'));
		$this->setState('price_to', $app->getInput()->get('price_to', '', 'float'));
		// Javascript update url has problems with "c", so changed to "category"
		//$this->setState('c', $app->getInput()->get('c', '', 'string')); // Category More (All Categories)
		$this->setState('c', $app->getInput()->get('category', '', 'string')); // Category More (All Categories)
		//$this->setState('id', $app->getInput()->get('id', '', 'int')); // Category ID (Active Category) ID IS VARIABLE - different for different pages
		$this->setState('a', $app->getInput()->get('a', '', 'array')); // Attributes
		$this->setState('s', $app->getInput()->get('s', '', 'array')); // Specifications


		// =SEARCH=
		$this->setState('search', $app->getInput()->get('search', '', 'string'));


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
			switch($this->getState('page')){

				case 'section':
					$this->ordering = PhocacartOrdering::renderOrderingFront($this->getState('itemordering'), 8);
				break;

				case 'main.content.customers':
					$this->ordering = PhocacartOrdering::renderOrderingFront($this->getState('itemordering'), 7);
				break;

				case 'main.content.orders':
					$this->ordering = PhocacartOrdering::renderOrderingFront($this->getState('itemordering'), 11);
				break;

				case 'main.content.products':
				default:
					$this->ordering = PhocacartOrdering::renderOrderingFront($this->getState('itemordering'), 0);
				break;
			}

		}
		return $this->ordering;
	}

	public function getTotal() {
		if (empty($this->total)) {

			switch($this->getState('page')){

				case 'section':
					$query = $this->getItemListQueryUnits(1);
				break;

				case 'main.content.customers':
					$query = $this->getItemListQueryCustomers(1);
				break;

				case 'main.content.orders':
					$query = $this->getItemListQueryOrders(1);
				break;

				case 'main.content.products':
				default:
					$query = $this->getItemListQuery(1);
				break;
			}


			$this->total = $this->_getListCount($query);

		}
		return $this->total;
	}


	public function getItemList($userId = 0, $vendorId = 0, $ticketId = 0, $unitId = 0, $sectionId = 0) {

		// Section and Unit can be reset if not exists
		$this->setState('ticketid', $ticketId);
		$this->setState('sectionid', $sectionId);
		$this->setState('unitid', $unitId);
		$this->setState('vendorid', $vendorId);
		$this->setState('userid', $userId);


		if (empty($this->item)) {

			switch($this->getState('page')){

				case 'section':
					$query = $this->getItemListQueryUnits();
				break;

				case 'main.content.customers':
					$query = $this->getItemListQueryCustomers();
				break;

				case 'main.content.orders':
					$query = $this->getItemListQueryOrders();

				break;

				case 'main.content.products':
				default:
					$query = $this->getItemListQuery();
				break;
			}

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

		// POS FILTER
		$p 							= array();
		$p['pos_categories']		= $params->get( 'pos_categories', array(-1) );
		$p['sql_search_skip_id']	= $params->get( 'sql_search_skip_id', 1 );
		$p['search_deep']			= $params->get( 'search_deep', 0 );
		$p['search_custom_fields']	= $params->get( 'search_custom_fields', 0 );

		$p['sql_search_skip_id_specific_type'] = 1;// POS or Online Shop (POS)
		if ((int)$p['sql_search_skip_id'] != 1 && (int)$p['sql_search_skip_id'] != 3){
			$p['sql_search_skip_id_specific_type'] = 0;

		}

		if (in_array(-1, $p['pos_categories'])) {
			// All categories selected


		} else if (in_array(0, $p['pos_categories'])) {
			// No category selected - dummy select to not break framework rules
			$this->setState('limitstart', 0);
			$this->setState('limit', 0);
			return 'SELECT id FROM #__phocacart_products WHERE 1 <> 1;';
		} else {
			// Only some selected
			$wheres[] = ' c.id IN ('.implode(',', $p['pos_categories']).')';

		}




		//$p['switch_image_category_items']	= $params->get( 'switch_image_category_items', 0 );

		$wheres[] = ' a.published = 1';
		$wheres[] = ' c.published = 1';
		$wheres[] = ' c.type IN (0,2)';// default categories or pos categories only
		if ($this->getState('filter.language')) {


			$lang 		= Factory::getLanguage()->getTag();
			$wheres[] 	= PhocacartUtilsSettings::getLangQuery('a.language', $lang);
			$wheres[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
		}
		$itemOrdering = $this->getItemOrdering();


		$wheres[] = " c.access IN (".$userLevels.")";
		$wheres[] = " a.access IN (".$userLevels.")";

		$wheres[] = " (ga.group_id IN (".$userGroups.") OR ga.group_id IS NULL)";
		$wheres[] = " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";

		// =FILTER=
		// -TAG-
		if ($this->getState('tag')) {
			$s = PhocacartSearch::getSqlParts('int', 'tag', $this->getState('tag'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}
        // -TAG-
        if ($this->getState('label')) {
            $s = PhocacartSearch::getSqlParts('int', 'label', $this->getState('label'));
            $wheres[]	= $s['where'];
            $lefts[]	= $s['left'];
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
	/*	if ($this->getState('id')) {
			$s = PhocacartSearch::getSqlParts('int', 'id', $this->getState('id'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}*/

		// -CATEGORY MORE-
		if ($this->getState('c')) {
			$s = PhocacartSearch::getSqlParts('int', 'c', $this->getState('c'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}

		// -ATTRIBUTES-
		if ($this->getState('a')) {
			$s = PhocacartSearch::getSqlParts('array', 'a', $this->getState('a'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}

		// -SPECIFICATIONS-
		if ($this->getState('s')) {
			$s = PhocacartSearch::getSqlParts('array', 's', $this->getState('s'));
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

		/*if ($p['switch_image_category_items'] == 1) {
			$leftImages = ' LEFT JOIN #__phocacart_product_images AS im ON a.id = im.product_id';
			$selImages	= ' GROUP_CONCAT(im.image) as additional_image,';

		}*/


		// Remove empty values:
		$wheres = array_filter($wheres);
		$lefts	= array_filter($lefts);

		if ($count == 1) {
			//$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid';
			$lefts[] = ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id';
			$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';

			if ((int)$p['sql_search_skip_id_specific_type'] == 0){
				$lefts[] = ' LEFT JOIN #__phocacart_product_stock AS ps ON a.id = ps.product_id';// search sku ean in advanced stock management
			}

			$lefts[] = ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1';
			$lefts[] = ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3';// type 3 is product
			$lefts[] = ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category

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

			if ((int)$p['sql_search_skip_id_specific_type'] == 0){
				$lefts[] = ' LEFT JOIN #__phocacart_product_stock AS ps ON a.id = ps.product_id';// search sku ean in advanced stock management
			}
			$lefts[] = ' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id';
			$lefts[] = ' LEFT JOIN #__phocacart_reviews AS r ON a.id = r.product_id AND r.id > 0';

			// We need to get information if at least one of the attributes of selected product is required

			// 1) Select more rows - one product is displayed e.g. in two rows
			//$lefts[] = ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0';

			// 2) right solution as it select only the maximal value and if maximal value is 1 then one of product attribute is required
			// LEFT JOIN (SELECT id, product_id, MAX(required) AS required FROM jos_phocacart_attributes GROUP BY product_id) AS at ON a.id = at.product_id AND at.id > 0

			// 3) faster version of 2)
			$lefts[] = ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1';



			$lefts[] = ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3';// type 3 is product
			$lefts[] = ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
			// user is in more groups, select lowest price by best group
			$lefts[] = ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON a.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN ('.$userGroups.') AND type = 3)';
			// user is in more groups, select highest points by best group
			$lefts[] = ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON a.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN ('.$userGroups.') AND type = 3)';


			$columns	= 'a.id, a.title, a.image, a.alias, a.unit_amount, a.unit_unit, a.description, a.type,'
						.' GROUP_CONCAT(DISTINCT c.id) AS catid, GROUP_CONCAT(DISTINCT c.title) AS cattitle,'
						.' GROUP_CONCAT(DISTINCT c.alias) AS catalias, a.catid AS preferred_catid, a.price, MIN(ppg.price) as group_price,'
						.' MAX(pptg.points_received) as group_points_received, a.points_received, a.price_original,'
						.' t.id as taxid, t.tax_rate as taxrate, t.calculation_type as taxcalculationtype, t.title as taxtitle, t.tax_hide as taxhide,'
						.' a.stock, a.stock_calculation, a.min_quantity, a.min_multiple_quantity, a.max_quantity, a.stockstatus_a_id, a.stockstatus_n_id,'
						.' a.date, a.sales, a.featured, a.external_id, a.unit_amount, a.unit_unit, a.external_link, a.external_text,'. $selImages
						.' AVG(r.rating) AS rating, at.required AS attribute_required';

			$groupsFull	= 'a.id, a.title, a.image, a.alias, a.description, a.type, a.price, a.points_received, a.price_original, a.stock, a.stock_calculation, a.min_quantity, a.min_multiple_quantity, a.max_quantity, a.stockstatus_a_id, a.stockstatus_n_id, a.date, a.sales, a.featured, a.external_id, a.unit_amount, a.unit_unit, a.external_link, a.external_text, t.id, t.tax_rate, t.calculation_type, t.tax_hide, t.title, at.required';
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


	protected function getItemListQueryCustomers($count = 0) {

		$app		= Factory::getApplication();
		$db			= Factory::getDBO();
		$user 		= PhocacartUser::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
		$params 	= $app->getParams();

		$pos_customers	= $params->get('pos_customers', '');

		$customers		= array();
		if (!empty($pos_customers)) {
			foreach($pos_customers as $k => $v) {
				$customersA = Access::getUsersByGroup((int)$v);
				$customers = array_merge($customers, $customersA);

			}
		}
		$customers = ArrayHelper::toInteger($customers);
		$customers = array_unique($customers);
		$customerList = implode (',', $customers);


		$wheres		= array();
		$lefts		= array();
		$phrase 	= 'any';
		//$p['switch_image_category_items']	= $params->get( 'switch_image_category_items', 0 );

		$wheres[] = ' a.block = 0';


		$itemOrdering = $this->getItemOrdering();



		// =FILTER=

		// =SEARCH=

		if ($this->getState('search')) {

			$in 	= $this->getState('search');
			$words	= explode(' ', $in);
			$wheresS = array();
			foreach ($words as $word) {

				if (!$word = trim($word)) {
					continue;
				}

				$word		= $db->quote('%'.$db->escape($word, true).'%', false);
				$wheresS2	= array();
				$wheresS2[]	= 'a.name LIKE '.$word;
				$wheresS2[]	= 'a.username LIKE '.$word;

				$wheresS[]	= implode(' OR ', $wheresS2);
			}
			$wheres[]	= '(' . implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheresS) . ')';
			$lefts[] 	= '';



		}

		// Customers
		if ($customerList != '') {
			$wheres[] = ' a.id IN ('.$customerList.')';
		}




		// Remove empty values:
		$wheres = array_filter($wheres);
		$lefts	= array_filter($lefts);

		if ($count == 1) {

			//$query = ' SELECT COUNT(DISTINCT a.id) AS count'; // 2.85ms 0.12mb
			$q = ' SELECT a.id' // 2.42ms 0.12mb
			. ' FROM #__users AS a'
			. implode( ' ', $lefts )
			. ' WHERE ' . implode( ' AND ', $wheres );
			//. ' GROUP BY a.id';

		} else {

			//$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid';



			$columns	= 'a.id, a.name, a.username';

			$groupsFull	= 'a.id, a.name, a.username';
			$groupsFast	= 'a.id';
			$groups		= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

			$q = ' SELECT '.$columns
			. ' FROM #__users AS a'
			. implode( ' ', $lefts )
			. ' WHERE ' . implode( ' AND ', $wheres )
			//. ' GROUP BY '.$groups
			. ' ORDER BY '.$itemOrdering;

		}
		//echo "<br><br>" . nl2br(str_replace('#__', 'jos_', $q));

		return $q;
	}

	protected function getItemListQueryUnits($count = 0) {

		$app		= Factory::getApplication();
		$db			= Factory::getDBO();
		$user 		= PhocacartUser::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
		$params 	= $app->getParams();
		$wheres		= array();
		$lefts		= array();
		$phrase 	= 'any';

		$itemOrdering = $this->getItemOrdering();


		$wheres[]	= 'a.section_id = '.(int)$this->getState('sectionid');
		$wheres[]	= 'a.published = 1';


		// Get info about cart for each unit
		//$wheres[]	= 'cm.vendor_id = '.(int)$this->getState('vendorid');// we need to load empty units too
		$wheres[]	= '';// ticket_id - no specific ticket - we get ticket list
		$wheres[]	= '';// unit_id - set in ON
		$wheres[]	= 'a.section_id = '.(int)$this->getState('sectionid');
		$lefts[] 	= ' LEFT JOIN #__phocacart_cart_multiple AS cm ON cm.unit_id = a.id AND cm.vendor_id = '.(int)$this->getState('vendorid');


		// Remove empty values:
		$wheres = array_filter($wheres);
		$lefts	= array_filter($lefts);



		if ($count == 1) {
			$q = ' SELECT a.id' // 2.42ms 0.12mb
			. ' FROM #__phocacart_units AS a'
			. implode( ' ', $lefts )
			. ' WHERE ' . implode( ' AND ', $wheres );
		} else {

			$columns	= 'a.id, a.title, cm.user_id, cm.vendor_id, cm.ticket_id, cm.unit_id, cm.section_id, cm.cart';

			$q = ' SELECT '.$columns
			. ' FROM #__phocacart_units AS a'
			. implode( ' ', $lefts )
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' ORDER BY '.$itemOrdering;

		}
	//	echo "<br><br>" . nl2br(str_replace('#__', 'jos_', $q));

		return $q;
	}


	protected function getItemListQueryOrders($count = 0) {

		$app		= Factory::getApplication();
		$db			= Factory::getDBO();
		$user 		= PhocacartUser::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
		$params 	= $app->getParams();
		$wheres		= array();
		$lefts		= array();
		$phrase 	= 'any';

		$itemOrdering = $this->getItemOrdering();


		// =SEARCH=
		if ($this->getState('date')) {

			$wheres[]	= 'DATE(a.date) = DATE('.$db->quote($this->getState('date')).')';

		}


		$wheres[]	= 'a.vendor_id = '.(int)$this->getState('vendorid');
		$wheres[]	= 'a.published = 1';
		$wheres[]	= 't.type = '.$db->quote('brutto');


		$lefts[]	= ' LEFT JOIN #__phocacart_order_total AS t ON a.id = t.order_id';
		$lefts[]	= ' LEFT JOIN #__phocacart_sections AS s ON s.id = a.section_id';
		$lefts[]	= ' LEFT JOIN #__phocacart_units AS un ON un.id = a.unit_id';
		$lefts[]	= ' LEFT JOIN #__users AS u1 ON u1.id = a.user_id';
		$lefts[]	= ' LEFT JOIN #__users AS u2 ON u2.id = a.vendor_id';




		// Remove empty values:
		$wheres = array_filter($wheres);
		$lefts	= array_filter($lefts);



		if ($count == 1) {
			$q = ' SELECT a.id' // 2.42ms 0.12mb
			. ' FROM #__phocacart_orders AS a'
			. implode( ' ', $lefts )
			. ' WHERE ' . implode( ' AND ', $wheres );
		} else {

			$columns	= 'a.id, a.title, a.user_id, a.vendor_id, a.ticket_id, a.unit_id, a.section_id, a.currency_id,'
						.' s.title AS section_title, un.title AS unit_title, u1.name AS user_title, u2.name AS vendor_title, a.date, t.amount AS total_amount, t.amount_currency AS total_amount_currency';
			$q = ' SELECT '.$columns
			. ' FROM #__phocacart_orders AS a'
			. implode( ' ', $lefts )
			. ' WHERE ' . implode( ' AND ', $wheres )
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
		$wheres[] = " c.type IN (0,2)";
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
			switch($this->getState('page')){

				case 'section':
					$this->item_ordering 		= PhocacartOrdering::getOrderingText($ordering, 8);
				break;

				case 'main.content.customers':
					$this->item_ordering 		= PhocacartOrdering::getOrderingText($ordering, 7);
				break;

				case 'main.content.orders':
					$this->item_ordering 		= PhocacartOrdering::getOrderingText($ordering, 11);
				break;

				case 'main.content.products':
				default:
					$this->item_ordering 		= PhocacartOrdering::getOrderingText($ordering);

				break;
			}

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















	// ACTIONS
	public function saveShipping($shippingId) {

		$app	= Factory::getApplication();
		$user	= $vendor = $ticket = $unit = $section = array();
		$dUser 	= PhocacartUser::defineUser($user, $vendor, $ticket, $unit, $section);


		$data['shipping']	= (int)$shippingId;
		$data['user_id']	= (int)$user->id;
		$shipping 			= new PhocacartShipping();
		$shipping->setType(array(0,2));

		if ((int)$shippingId == 0) {
			// Deselect Shipping
		} else {
			$isValidShipping = $shipping->checkAndGetShippingMethod($shippingId);
			if (!$isValidShipping) {
				$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_SHIPPING_METHOD_NOT_AVAILABLE'), 'error');
				return false;
			}
		}
		$row = $this->getTable('PhocacartCart', 'Table');
		if (!$row->load(array('user_id' => (int)$user->id, 'vendor_id' => (int)$vendor->id, 'ticket_id' => (int)$ticket->id, 'unit_id' => (int)$unit->id, 'section_id' => (int)$section->id))) {}

		if (empty($row->cart)) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_CART_IS_EMPTY_SHIPPING_METHOD_CANNOT_BE_SET'), 'error');
			return false;
		}

		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		$row->date = gmdate('Y-m-d H:i:s');

		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		if ((int)$shippingId == 0) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_SUCCESS_SHIPPING_METHOD_DESELECTED'), 'success');
		} else {
			$app->enqueueMessage(Text::_('COM_PHOCACART_SUCCESS_SHIPPING_METHOD_SELECTED'), 'success');
		}


		return true;
	}

	public function savePaymentAndCouponAndReward($paymentId, $couponId, $reward) {

		$app	= Factory::getApplication();
		$user	= $vendor = $ticket = $unit = $section = array();
		$dUser 	= PhocacartUser::defineUser($user, $vendor, $ticket, $unit, $section);


		$data['payment'] 	= (int)$paymentId;
		$data['coupon'] 	= (int)$couponId;
		$data['user_id']	= (int)$user->id;
		$data['reward'] 	= (int)$reward;
		$payment 			= new PhocacartPayment();
		$payment->setType(array(0,2));

		if ((int)$paymentId == 0) {
			// Deselect Payment
		} else {
			$isValidPayment	= $payment->checkAndGetPaymentMethod($paymentId);
			if (!$isValidPayment) {
				$app->enqueueMessage( $paymentId . Text::_('COM_PHOCACART_ERROR_PAYMENT_METHOD_NOT_AVAILABLE'), 'error');
				return false;
			}
		}


		$row = $this->getTable('PhocacartCart', 'Table');
		if (!$row->load(array('user_id' => (int)$user->id, 'vendor_id' => (int)$vendor->id, 'ticket_id' => (int)$ticket->id, 'unit_id' => (int)$unit->id, 'section_id' => (int)$section->id))) {}


		if (empty($row->cart)) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_CART_IS_EMPTY_PAYMENT_METHOD_CANNOT_BE_SET'), 'error');
			return false;
		}

		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		$row->date = gmdate('Y-m-d H:i:s');

		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		if ((int)$paymentId == 0) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_SUCCESS_PAYMENT_METHOD_DESELECTED'), 'success');
		} else {
			$app->enqueueMessage(Text::_('COM_PHOCACART_SUCCESS_PAYMENT_METHOD_SELECTED'), 'success');
		}


		return true;
	}
}
?>
