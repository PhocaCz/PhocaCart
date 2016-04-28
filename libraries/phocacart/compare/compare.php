<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartCompare
{
	protected $items     		= array();

	public function __construct() {
		$session 		= JFactory::getSession();
		$app 			= JFactory::getApplication();
		$this->items	= $session->get('compare', array(), 'phocaCart');
	}
	
	public function addItem($id = 0, $catid = 0) {
		if ($id > 0) {
			$app 			= JFactory::getApplication();
			
			$count = count($this->items);
		
			if ($count > 2) {
				$message = JText::_('COM_PHOCACART_ONLY_THREE_PRODUCTS_CAN_BE_LISTED_IN_COMPARISON_LIST');
				$app->enqueueMessage($message, 'error');
				return false;
			}
			
			if(isset($this->items[$id]) && (int)$this->items[$id] > 0) {
				
				$message = JText::_('COM_PHOCACART_PRODUCT_INCLUDED_IN_COMPARISON_LIST');
				$app->enqueueMessage($message, 'error');
				return false;
			} else {
				$this->items[$id]['id'] = $id;
				$this->items[$id]['catid'] = $catid;
				$session 		= JFactory::getSession();
				$session->set('compare', $this->items, 'phocaCart');
			}
			return true;
		}
		return false;
	}
	
	public function removeItem($id = 0) {
		if ($id > 0) {
			if(isset($this->items[$id]) && (int)$this->items[$id] > 0) {
				unset($this->items[$id]);
				$session 		= JFactory::getSession();
				$session->set('compare', $this->items, 'phocaCart');
				return true;
			} else {
				return false;
			}
			return false;
		}
		return false;
	}
	
	public function emptyCompare() {
		$session 		= JFactory::getSession();
		$session->set('compare', array(), 'phocaCart');
	}
	
	public function getItems() {
		return $this->items;
	}
	
	
	public function getQueryList($items, $full = 0){
		
		$user 		= JFactory::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		
		$itemsS		= $this->getItemsIdString($items);
		
		if ($itemsS == '') {
			return false;
		}
		
		$wheres[] = 'a.id IN ('.(string)$itemsS.')';	
		$wheres[] = " c.access IN (".$userLevels.")";
		$wheres[] = " a.access IN (".$userLevels.")";
		$wheres[] = " c.published = 1";
		$wheres[] = " a.published = 1";
		
		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );
		
		if ($full == 1) {
			$query = 
			 ' SELECT a.id as id, a.title as title, a.alias as alias, a.description, a.price, a.image,'
			.' c.id as catid, c.alias as catalias, c.title as cattitle, count(pc.category_id) AS count_categories,'
			.' a.length, a.width, a.height, a.weight, a.volume,'
			.' a.stock, a.min_quantity, a.stockstatus_a_id, a.stockstatus_n_id, a.availability,'
			.' m.title as manufacturer_title'
			.' FROM #__phocacart_products AS a'
			.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id'
			.' LEFT JOIN #__phocacart_categories AS c ON c.id =  pc.category_id'
			.' LEFT JOIN #__phocacart_manufacturers AS m ON a.manufacturer_id = m.id'
			.  $where
			. ' GROUP BY a.id'
			. ' ORDER BY a.id';
		} else {
			$query = 
			 ' SELECT a.id as id, a.title as title, a.alias as alias,'
			.' c.id as catid, c.alias as catalias, c.title as cattitle, count(pc.category_id) AS count_categories'
			.' FROM #__phocacart_products AS a'
			.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id'
			.' LEFT JOIN #__phocacart_categories AS c ON c.id =  pc.category_id'
			.  $where
			. ' GROUP BY a.id'
			. ' ORDER BY a.id';
		}	
		return $query;
	}
	
	public function getItemsIdString($items) {
		
		$itemsR = '';
		if (!empty($items)) {
			$itemsA = array();
			foreach($items as $k => $v) {
				if (isset($v['id']) && (int)$v['id'] > 0) {
					$itemsA[] = $v['id'];
				}
			}
			$itemsR = implode (',', $itemsA);
		}
		
		if ($itemsR == '') {
			return false;
		}
		return $itemsR;
	}
	
	public function renderList() {
		
		$db 				= JFactory::getDBO();
		$uri 				= JFactory::getURI();
		$action				= $uri->toString();
		$paramsC 			= JComponentHelper::getParams('com_phocacart') ;
		$add_compare_method	= $paramsC->get( 'add_compare_method', 0 );
		$query				= $this->getQueryList($this->items);
		
		$d					= array();
		
		if ($query) {
			//echo nl2br(str_replace('#__', 'jos_', $query));
			$db->setQuery($query);
			$d['compare'] 			= $db->loadObjectList();
			PhocaCartCategoryMultiple::setBestMatchCategory($d['compare'], $this->items, 1);// returned by reference
		}	
		$d['actionbase64']		= base64_encode($action);
		$d['linkcomparison']	= JRoute::_(PhocaCartRoute::getComparisonRoute());
		$d['method']			= $add_compare_method;
			
		$layoutC 			= new JLayoutFile('list_compare', $basePath = JPATH_ROOT .'/components/com_phocacart/layouts');
		echo $layoutC->render($d);
	}
	
	public function getFullItems() {
		
		$db 		= JFactory::getDBO();
		$query		= $this->getQueryList($this->items, 1);
		
		$products	= array();
		if ($query) {
			$db->setQuery($query);
			$products = $db->loadAssocList();
			PhocaCartCategoryMultiple::setBestMatchCategory($products, $this->items);// returned by reference
		}
		return $products;
	
	}
}
?>