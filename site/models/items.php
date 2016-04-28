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
		$paramsC 			= JComponentHelper::getParams('com_phocacart') ;
		$item_pagination	= $paramsC->get( 'item_pagination_default', '20' );
		$item_ordering		= $paramsC->get( 'item_ordering', 1 );
	
		$this->setState('limit', $app->getUserStateFromRequest('com_phocacart.limit', 'limit', $item_pagination, 'int'));
		$this->setState('limitstart', $app->input->get('limitstart', 0, 'int'));
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));
		$this->setState('filter.language',$app->getLanguageFilter());
		$this->setState('filter_order', JRequest::getCmd('filter_order', 'ordering'));
		$this->setState('filter_order_dir', JRequest::getCmd('filter_order_Dir', 'ASC'));
		
		$this->setState('itemordering', $app->getUserStateFromRequest('com_phocacart.itemordering', 'itemordering', $item_ordering, 'int'));
		
		
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
	
	public function getPagination() {
		if (empty($this->pagination)) {
			jimport('joomla.html.pagination');
			$this->pagination = new PhocaCartPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->pagination;
	}
	
	function getOrdering() {
		if(empty($this->ordering)) {
			$this->ordering = PhocaCartOrdering::renderOrderingFront($this->getState('itemordering'), 1);
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
		$user 		= JFactory::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$params 	= $app->getParams();
		$wheres		= array();
		$lefts		= array();
		
		$wheres[] = ' a.published = 1';
		$wheres[] = ' c.published = 1';
		if ($this->getState('filter.language')) {
			$wheres[] =  ' a.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
			$wheres[] =  ' c.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}
		$itemOrdering = $this->getItemOrdering();
		
		
		$wheres[] = " c.access IN (".$userLevels.")";
		$wheres[] = " a.access IN (".$userLevels.")";
		
		
		// =FILTER=
		// -TAG-
		if ($this->getState('tag')) {
			$s = PhocaCartSearch::getSqlParts('int', 'tag', $this->getState('tag'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}
		// -MANUFACTURER-
		if ($this->getState('manufacturer')) {
			$s = PhocaCartSearch::getSqlParts('int', 'manufacturer', $this->getState('manufacturer'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}
		// -PRICE-
		if ($this->getState('price_from')) {
			$s = PhocaCartSearch::getSqlParts('int', 'price_from', $this->getState('price_from'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}
		if ($this->getState('price_to')) {
			$s = PhocaCartSearch::getSqlParts('int', 'price_to', $this->getState('price_to'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}
		
		// -CATEGORY-
		if ($this->getState('id')) {
			$s = PhocaCartSearch::getSqlParts('int', 'id', $this->getState('id'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}
		
		// -CATEGORY MORE-
		if ($this->getState('c')) {
			$s = PhocaCartSearch::getSqlParts('int', 'c', $this->getState('c'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}
		
		// -ATTRIBUTES-
		if ($this->getState('a')) {
			$s = PhocaCartSearch::getSqlParts('array', 'a', $this->getState('a'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}
		
		// -SPECIFICATIONS-
		if ($this->getState('s')) {
			$s = PhocaCartSearch::getSqlParts('array', 's', $this->getState('s'));
			$wheres[]	= $s['where'];
			$lefts[]	= $s['left'];
		}
		
		// =SEARCH=
		if ($this->getState('search')) {
			$s = PhocaCartSearch::getSqlParts('string', 'search', $this->getState('search'));
			$wheres[]	= '('.$s['where'].')';
			$lefts[]	= $s['left'];
		}
		
		
		// Remove empty values:
		$wheres = array_filter($wheres);
		$lefts	= array_filter($lefts);
		
		if ($count == 1) {
			//$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid';
			$lefts[] = ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id';
			$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
			$lefts[] = ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0';
			
			//$query = ' SELECT COUNT(DISTINCT a.id) AS count'; // 2.85ms 0.12mb
			$q = ' SELECT a.id' // 2.42ms 0.12mb
			. ' FROM #__phocacart_products AS a'
			. implode( ' ', $lefts )
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' GROUP BY a.id';
			
		} else {
			
			//$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid';
			$lefts[] = ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id';
			$lefts[] = ' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id';
			$lefts[] = ' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id';
			$lefts[] = ' LEFT JOIN #__phocacart_reviews AS r ON a.id = r.product_id AND r.id > 0';
			//$lefts[] = ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1';
			$lefts[] = ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0';
			
			$q = ' SELECT a.id, a.title, a.image, a.alias, a.unit_amount, a.unit_unit, a.description, c.id AS catid, c.title AS cattitle, c.alias AS catalias, a.price, a.price_original, t.tax_rate as taxrate, t.calculation_type as taxcalculationtype, t.title as taxtitle, a.date, a.sales, a.featured, a.external_id, a.external_link, a.external_text, '
			. ' AVG(r.rating) AS rating,'
			. ' at.required AS attribute_required'
			. ' FROM #__phocacart_products AS a'
			. implode( ' ', $lefts )
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' GROUP BY a.id'
			. ' ORDER BY '.$itemOrdering;	
		}
		//echo "<br><br>" . nl2br(str_replace('#__', 'jos_', $q));
		return $q;
	}
	
	protected function getCategoriesQuery( $categoryId, $subcategories = FALSE ) {
		
		$wheres		= array();
		$app		= JFactory::getApplication();
		$params 	= $app->getParams();
		
		// Get the current category or get parent categories of the current category
		if ($subcategories) {
			$wheres[]			= " c.parent_id = ".(int)$categoryId;
			$categoryOrdering 	= $this->getCategoryOrdering();
		} else {
			$wheres[]	= " c.id= ".(int)$categoryId;
		}

		$wheres[] = " c.published = 1";
		
		if ($this->getState('filter.language')) {
			$wheres[] =  ' c.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}
		
		if ($subcategories) {
			$query = " SELECT  c.id, c.title, c.alias, COUNT(c.id) AS numdoc"
				. " FROM #__phocacart_categories AS c"
				. " LEFT JOIN #__phocacart_products AS a ON a.catid = c.id AND a.published = 1"
				. " WHERE " . implode( " AND ", $wheres )
				. " GROUP BY c.id"
				. " ORDER BY ".$categoryOrdering;
		} else {
			$query = " SELECT c.id, c.title, c.alias, c.description, c.metakey, c.metadesc, cc.title as parenttitle, c.parent_id as parentid, cc.alias as parentalias"
				. " FROM #__phocacart_categories AS c"
				. " LEFT JOIN #__phocacart_categories AS cc ON cc.id = c.parent_id"
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
			$this->item_ordering 		= PhocaCartOrdering::getOrderingText($ordering);
		}
		return $this->item_ordering;
	}
	
	protected function getCategoryOrdering() {
		if (empty($this->category_ordering)) {
			$app						= JFactory::getApplication();
			$params						= $app->getParams();
			$ordering					= $params->get( 'category_ordering', 1 );
			$this->category_ordering 	= PhocaCartOrdering::getOrderingText($ordering, 1);
		}
		return $this->category_ordering;
	}
}
?>