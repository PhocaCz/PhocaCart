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

	function getItem( $itemId) {
		if (empty($this->item)) {			
			$query			= $this->getItemQuery( $itemId );
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
		
		$wheres[]	= " c.catid= ".(int) $catid;
		//$wheres[]	= " c.catid= cc.id";
		$wheres[] = " c.published = 1";
		$wheres[] = " cc.published = 1";
		
		if ($direction == 1) {
			$wheres[] = " c.ordering < " . (int) $ordering;
			$order = 'DESC';
		} else {
			$wheres[] = " c.ordering > " . (int) $ordering;
			$order = 'ASC';
		}
		
		if ($this->getState('filter.language')) {
			$wheres[] =  ' c.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
			$wheres[] =  ' cc.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}
		
		$query = ' SELECT c.id, c.title, c.alias, c.catid, cc.id AS categoryid, cc.title AS categorytitle, cc.alias AS categoryalias'
				.' FROM #__phocacart_products AS c' 
				.' LEFT JOIN #__phocacart_categories AS cc ON cc.id = c.catid'
				.' WHERE ' . implode( ' AND ', $wheres )
				.' ORDER BY c.ordering '.$order;		
		return $query;
	
	}
	private function getItemQuery( $itemId ) {
		
		//$app		= JFactory::getApplication();
		//$params 	= $app->getParams();

		$categoryId	= 0;
		$category	= $this->getCategory($itemId);
		if (isset($category[0]->id)) {
			$categoryId = $category[0]->id;
		}
		
		$wheres[]	= " i.catid= ".(int) $categoryId;
		$wheres[]	= " i.catid= cc.id";
		$wheres[] 	= " i.published = 1";
		$wheres[] 	= " cc.published = 1";
		$wheres[] 	= " i.id = " . (int) $itemId;
		
		if ($this->getState('filter.language')) {
			$wheres[] =  ' i.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
			$wheres[] =  ' cc.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}
		
		$query = ' SELECT i.id, i.title, i.alias, i.catid, i.description, i.ordering, i.metadesc, i.metakey, i.image, i.description, i.description_long, i.price, i.price_original, i.stockstatus_a_id, i.stockstatus_n_id, i.min_quantity, i.stock, cc.id AS categoryid, cc.title AS categorytitle, cc.alias AS categoryalias, t.tax_rate as taxrate, t.title as taxtitle, t.calculation_type as taxcalculationtype, m.id as manufacturerid, m.title as manufacturertitle'
				.' FROM #__phocacart_products AS i' 
				.' LEFT JOIN #__phocacart_categories AS cc ON cc.id = i.catid'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = i.tax_id'
				.' LEFT JOIN #__phocacart_manufacturers AS m ON m.id = i.manufacturer_id'
				.' WHERE ' . implode( ' AND ', $wheres )
				.' ORDER BY i.ordering';		
		return $query;
	}
	
	function getCategory($itemId) {
		if (empty($this->category)) {			
			$query			= $this->getCategoryQuery( $itemId );
			$this->category		= $this->_getList( $query, 0, 1 );
		}
		return $this->category;
	}
	
	function getCategoryQuery($itemId) {
		
		$wheres		= array();
		//$app		= JFactory::getApplication();
		//$params 	= $app->getParams();

		$wheres[]	= " c.id= ".(int)$itemId;
		$wheres[] = " cc.published = 1";
		
		if ($this->getState('filter.language')) {
			$wheres[] =  ' c.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
			$wheres[] =  ' cc.language IN ('.$this->_db->Quote(JFactory::getLanguage()->getTag()).','.$this->_db->Quote('*').')';
		}
		
		$query = " SELECT cc.id, cc.title, cc.alias, cc.description"
				. " FROM #__phocacart_categories AS cc"
				. " LEFT JOIN #__phocacart_products AS c ON c.catid = cc.id"
				. " WHERE " . implode( " AND ", $wheres )
				. " ORDER BY cc.ordering";		
		return $query;
	}
	
	
}
?>