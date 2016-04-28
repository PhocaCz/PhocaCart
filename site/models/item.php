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

class PhocaCartModelItem extends JModelLegacy
{
	var $item 				= null;
	var $category			= null;
	var $itemname			= null;
	var $itemnext			= null;
	var $itemprev			= null;

	function __construct() {
		$app	= JFactory::getApplication();
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
		
		$wheres[]	= " pc.category_id = ".(int) $catid;
		//$wheres[]	= " c.catid= cc.id";
		$wheres[] = " c.published = 1";
		$wheres[] = " cc.published = 1";
		
		if ($direction == 1) {
			$wheres[] = " pc.ordering < " . (int) $ordering;
			$order = 'DESC';
		} else {
			$wheres[] = " pc.ordering > " . (int) $ordering;
			$order = 'ASC';
		}
		
		if ($this->getState('filter.language')) {
			$wheres[] =  ' c.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
			$wheres[] =  ' cc.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}
		
		$query = ' SELECT c.id, c.title, c.alias, c.catid, cc.id AS categoryid, cc.title AS categorytitle, cc.alias AS categoryalias'
				.' FROM #__phocacart_products AS c' 
				.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = c.id'
				.' LEFT JOIN #__phocacart_categories AS cc ON cc.id = pc.category_id'
				.' WHERE ' . implode( ' AND ', $wheres )
				.' ORDER BY pc.ordering '.$order;		
		return $query;
	
	}
	private function getItemQuery( $itemId, $catId ) {
		
		//$app		= JFactory::getApplication();
		//$params 	= $app->getParams();

		$categoryId	= 0;
		$category	= $this->getCategory($itemId, $catId);
		if (isset($category[0]->id)) {
			$categoryId = $category[0]->id;
		}
		
		$wheres[]	= " pc.category_id= ".(int) $categoryId;
		$wheres[]	= " pc.category_id= cc.id";
		$wheres[] 	= " i.published = 1";
		$wheres[] 	= " cc.published = 1";
		$wheres[] 	= " i.id = " . (int) $itemId;
		
		if ($this->getState('filter.language')) {
			$wheres[] =  ' i.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
			$wheres[] =  ' cc.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}
		
		$query = ' SELECT i.id, i.title, i.alias, i.description, i.ordering, i.metadesc, i.metakey, i.image, i.description, i.description_long, i.price, i.price_original, i.stockstatus_a_id, i.stockstatus_n_id, i.min_quantity, i.stock, i.date, i.sales, i.featured, i.external_id, i.unit_amount, i.unit_unit, i.video, i.external_link, i.external_text, cc.id AS catid, cc.title AS cattitle, cc.alias AS catalias, t.tax_rate as taxrate, t.title as taxtitle, t.calculation_type as taxcalculationtype, m.id as manufacturerid, m.title as manufacturertitle'
				.' FROM #__phocacart_products AS i' 
				.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = i.id'
				.' LEFT JOIN #__phocacart_categories AS cc ON cc.id = pc.category_id'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = i.tax_id'
				.' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = i.manufacturer_id'
				.' WHERE ' . implode( ' AND ', $wheres )
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
		
		$wheres		= array();
		//$app		= JFactory::getApplication();
		//$params 	= $app->getParams();

		$wheres[] = " cc.published = 1";
		
		if ($this->getState('filter.language')) {
			$wheres[] =  ' c.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
			$wheres[] =  ' cc.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}
		
		if ((int)$catId > 0) {
			$wheres[]	= " cc.id= ".(int)$catId;
		} else {
			$wheres[]	= " c.id= ".(int)$itemId;
		}
			
		$query = " SELECT cc.id, cc.title, cc.alias, cc.description, cc.parent_id"
				. " FROM #__phocacart_categories AS cc"
				. ' LEFT JOIN #__phocacart_product_categories AS pc ON pc.category_id = cc.id'
				. " LEFT JOIN #__phocacart_products AS c ON c.id = pc.product_id"
				. " WHERE " . implode( " AND ", $wheres )
				. " ORDER BY cc.ordering";		
		return $query;
	}
	
	public function hit($pk = 0) {
		$input = JFactory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount) {
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('product.id');

			$table = JTable::getInstance('PhocaCartItem', 'Table');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}
}
?>