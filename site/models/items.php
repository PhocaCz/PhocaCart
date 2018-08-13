<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

class PhocaCartModelItems extends JModelLegacy
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
		
		$app				= JFactory::getApplication();
		$config 			= JFactory::getConfig();		
		$paramsC 			= $app->getParams();
		$item_pagination	= $paramsC->get( 'item_pagination_default', '20' );
		$item_ordering		= $paramsC->get( 'item_ordering', 1 );
		$layout_type		= $paramsC->get( 'layout_type', 'grid' );
		
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
		$this->setState('manufacturer', $app->input->get('manufacturer', '', 'string'));
		$this->setState('price_from', $app->input->get('price_from', '', 'string'));
		$this->setState('price_to', $app->input->get('price_to', '', 'string'));
		$this->setState('c', $app->input->get('c', '', 'string')); // Category More (All Categories)
		$this->setState('id', $app->input->get('id', '', 'int')); // Category ID (Active Category)
		$this->setState('a', $app->input->get('a', '', 'array')); // Attributes
		$this->setState('s', $app->input->get('s', '', 'array')); // Specifications
		
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
	
		$app		= JFactory::getApplication();
		$user 		= PhocacartUser::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
		$params 	= $app->getParams();
		$wheres		= array();
		$lefts		= array();
		
		$p['hide_products_out_of_stock']	= $params->get( 'hide_products_out_of_stock', 0);
		$p['switch_image_category_items']	= $params->get( 'switch_image_category_items', 0 );
		
		$wheres[] = ' a.published = 1';
		$wheres[] = ' c.published = 1';
		
		$wheres[] = " c.type IN (0,1)";// type: common, onlineshop, pos
		
		
		if ($this->getState('filter.language')) {
			$lang 		= JFactory::getLanguage()->getTag();
			$wheres[] 	= PhocacartUtilsSettings::getLangQuery('a.language', $lang);
			$wheres[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
		}
		$itemOrdering = $this->getItemOrdering();
		
		
		$wheres[] = " c.access IN (".$userLevels.")";
		$wheres[] = " a.access IN (".$userLevels.")";
		
		$wheres[] = " (ga.group_id IN (".$userGroups.") OR ga.group_id IS NULL)";
		$wheres[] = " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";
		
		if ($p['hide_products_out_of_stock'] == 1) {
			$wheres[] = " a.stock > 0";
		}
		
		// =FILTER=
		// -TAG-
		if ($this->getState('tag')) {
			$s = PhocacartSearch::getSqlParts('int', 'tag', $this->getState('tag'));
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
			$s = PhocacartSearch::getSqlParts('string', 'search', $this->getState('search'));
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
		
		
		// Remove empty values:
		$wheres = array_filter($wheres);
		$lefts	= array_filter($lefts);
		
		if ($count == 1) {
			//$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid';
			$lefts[] = ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id';
			$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
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
			$lefts[] = ' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id';
			$lefts[] = ' LEFT JOIN #__phocacart_reviews AS r ON a.id = r.product_id AND r.id > 0';
			//$lefts[] = ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1';
			$lefts[] = ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0';
			$lefts[] = ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3';// type 3 is product
			$lefts[] = ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2';// type 2 is category
			// user is in more groups, select lowest price by best group
			$lefts[] = ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON a.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN ('.$userGroups.') AND type = 3)';
			// user is in more groups, select highest points by best group
			$lefts[] = ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON a.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN ('.$userGroups.') AND type = 3)';
			
			
			$columns	= 'a.id, a.title, a.image, a.alias, a.unit_amount, a.unit_unit,  a.description,'
						.' GROUP_CONCAT(DISTINCT c.id) AS catid, GROUP_CONCAT(DISTINCT c.title) AS cattitle,'
						.' GROUP_CONCAT(DISTINCT c.alias) AS catalias, a.price, MIN(ppg.price) as group_price,' 
						.' MAX(pptg.points_received) as group_points_received, a.points_received, a.price_original,' 
						.' t.id as taxid, t.tax_rate as taxrate, t.calculation_type as taxcalculationtype, t.title as taxtitle,' 
						.' a.stock, a.stock_calculation, a.min_quantity, a.min_multiple_quantity, a.stockstatus_a_id, a.stockstatus_n_id,' 
						.' a.date, a.sales, a.featured, a.external_id, a.unit_amount, a.unit_unit, a.external_link, a.external_text,'. $selImages
						.' AVG(r.rating) AS rating, at.required AS attribute_required';
			
			$groupsFull	= 'a.id, a.title, a.image, a.alias, a.description, a.price, a.points_received, a.price_original, a.stock, a.stock_calculation, a.min_quantity, a.min_multiple_quantity, a.stockstatus_a_id, a.stockstatus_n_id, a.date, a.sales, a.featured, a.external_id, a.unit_amount, a.unit_unit, a.external_link, a.external_text, t.id, t.tax_rate, t.calculation_type, t.title, at.required';
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
		$app		= JFactory::getApplication();
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
			$lang 		= JFactory::getLanguage()->getTag();
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
			$app						= JFactory::getApplication();
			$params						= $app->getParams();
			//$ordering					= $params->get( 'item_ordering', 1 );
			$ordering					= $this->getState('itemordering');
			$this->item_ordering 		= PhocacartOrdering::getOrderingText($ordering);
		}
		return $this->item_ordering;
	}
	
	protected function getCategoryOrdering() {
		if (empty($this->category_ordering)) {
			$app						= JFactory::getApplication();
			$params						= $app->getParams();
			$ordering					= $params->get( 'category_ordering', 1 );
			$this->category_ordering 	= PhocacartOrdering::getOrderingText($ordering, 1);
		}
		return $this->category_ordering;
	}
}
?>