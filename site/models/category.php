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

class PhocaCartModelCategory extends JModelLegacy
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
	}
	
	public function getPagination($categoryId) {
		if (empty($this->pagination)) {
			jimport('joomla.html.pagination');
			$this->pagination = new PhocaCartPagination( $this->getTotal($categoryId), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->pagination;
	}
	
	function getOrdering() {
		if(empty($this->ordering)) {
			$this->ordering = PhocaCartOrdering::renderOrderingFront($this->getState('itemordering'), 1);
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
	
		$app		= JFactory::getApplication();
		$user 		= JFactory::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$params 	= $app->getParams();
		$wheres		= array();
		if ((int)$categoryId > 0) {
			$wheres[]			= " c.id = ".(int)$categoryId;
		}
		$wheres[] = ' a.published = 1';
		$wheres[] = ' c.published = 1';
		if ($this->getState('filter.language')) {
			$wheres[] =  ' a.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
			$wheres[] =  ' c.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}
		$itemOrdering = $this->getItemOrdering();
		
		
		$wheres[] = " c.access IN (".$userLevels.")";
		$wheres[] = " a.access IN (".$userLevels.")";
		
		if ($count == 1) {
			$q = ' SELECT a.id'
			. ' FROM #__phocacart_products AS a'
			//. ' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid'
			. " LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id"
			. " LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id"
			. ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1'
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' GROUP BY a.id';
			
		} else {
			$q = ' SELECT a.id, a.title, a.image, a.alias, a.description, a.catid, c.id AS categoryid, c.title AS categorytitle, c.alias AS categoryalias, a.price, a.price_original, t.tax_rate as taxrate, t.calculation_type as taxcalculationtype, t.title as taxtitle, a.date, a.sales, a.featured, a.external_id, a.unit_amount, a.unit_unit, a.external_link, a.external_text, '
			. ' AVG(r.rating) AS rating,'
			. ' at.required AS attribute_required'
			. ' FROM #__phocacart_products AS a'
			//. ' LEFT JOIN #__phocacart_categories AS c ON c.id = a.catid'
			. " LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id AND pc.category_id = ".(int)$categoryId
			. " LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id"
			. ' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id'
			. ' LEFT JOIN #__phocacart_reviews AS r ON a.id = r.product_id AND r.id > 0'
			. ' LEFT JOIN #__phocacart_attributes AS at ON a.id = at.product_id AND at.id > 0 AND at.required = 1'
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' GROUP BY a.id'
			. ' ORDER BY '.$itemOrdering;
		}
		
		//echo nl2br(str_replace('#__', 'jos_', $q->__toString()));
		return $q;
	}
	
	protected function getCategoriesQuery($categoryId, $subcategories = FALSE) {
		
		$wheres		= array();
		$app		= JFactory::getApplication();
		$params 	= $app->getParams();
		$user 		= JFactory::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		
		// Get the current category or get parent categories of the current category
		if ($subcategories) {
			$wheres[]			= " c.parent_id = ".(int)$categoryId;
			$categoryOrdering 	= $this->getCategoryOrdering();
		} else {
			$wheres[]			= " c.id= ".(int)$categoryId;
		}

		$wheres[] 		= " c.published = 1";
		$wheres[] 		= " c.access IN (".$userLevels.")";
		
		if ($this->getState('filter.language')) {
			$wheres[] 	=  ' c.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}
		
		if ($subcategories) {
			
			$query = " SELECT  c.id, c.parent_id, c.title, c.alias, COUNT(c.id) AS numdoc"
				. " FROM #__phocacart_categories AS c"
				//. " LEFT JOIN #__phocacart_product_categories AS pc ON pc.category_id = c.id"
				//. " LEFT JOIN #__phocacart_products AS a ON a.id = pc.product_id AND a.published = 1 AND a.access IN (".$userLevels.")"
				. " WHERE " . implode( " AND ", $wheres )
				. " GROUP BY c.id"
				. " ORDER BY ".$categoryOrdering;
		} else {
			$query = " SELECT c.id, c.parent_id, c.title, c.alias, c.description, c.metakey, c.metadesc, cc.title as parenttitle, c.parent_id as parentid, cc.alias as parentalias"
				. " FROM #__phocacart_categories AS c"
				. " LEFT JOIN #__phocacart_categories AS cc ON cc.id = c.parent_id"
				. " WHERE " . implode( " AND ", $wheres )
				. " ORDER BY c.ordering";
		}
		//echo nl2br(str_replace('#__', 'jos_', $query));
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
	
	public function hit($pk = 0) {
		$input = JFactory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount) {
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('cateogry.id');

			$table = JTable::getInstance('PhocaCartCategory', 'Table');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}
}
?>