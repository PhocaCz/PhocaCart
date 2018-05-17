<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

class PhocacartCompare
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
		
		$user 		= PhocacartUser::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
		
		$itemsS		= $this->getItemsIdString($items);
		
		if ($itemsS == '') {
			return false;
		}
		
		$wheres[] = 'a.id IN ('.(string)$itemsS.')';	
		$wheres[] = " c.access IN (".$userLevels.")";
		$wheres[] = " a.access IN (".$userLevels.")";
		$wheres[] = " (ga.group_id IN (".$userGroups.") OR ga.group_id IS NULL)";
		$wheres[] = " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";
		$wheres[] = " c.published = 1";
		$wheres[] = " a.published = 1";
		$wheres[] = " c.type IN (0,1)";// compare only works in online shop (0 - all, 1 - online shop, 2 - pos)
		
		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );
		
		if ($full == 1) {
			
			$columns		= 
			'a.id as id, a.title as title, a.alias as alias, a.description, a.price, a.image,'
			.' GROUP_CONCAT(DISTINCT c.id) as catid, GROUP_CONCAT(DISTINCT c.alias) as catalias, GROUP_CONCAT(DISTINCT c.title) as cattitle, COUNT(pc.category_id) AS count_categories,'
			.' a.length, a.width, a.height, a.weight, a.volume, a.unit_amount, a.unit_unit, a.price_original,'
			.' a.stock, a.min_quantity, a.min_multiple_quantity, a.stockstatus_a_id, a.stockstatus_n_id, a.availability,'
			.' m.title as manufacturer_title,'
			.' t.id as taxid, t.tax_rate as taxrate, t.title as taxtitle, t.calculation_type as taxcalculationtype,'
			.' MIN(ppg.price) as group_price, MAX(pptg.points_received) as group_points_received';
			$groupsFull		= 
			'a.id, a.title, a.alias, a.description, a.price, a.image,'
			.' a.length, a.width, a.height, a.weight, a.volume,'
			.' a.stock, a.min_quantity, a.min_multiple_quantity, a.stockstatus_a_id, a.stockstatus_n_id, a.availability,'
			.' m.title,'
			.' ppg.price, pptg.points_received';
			$groupsFast		= 'a.id';
			$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;
			
			$query = 
			 ' SELECT '.$columns
			.' FROM #__phocacart_products AS a'
			.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id'
			.' LEFT JOIN #__phocacart_categories AS c ON c.id =  pc.category_id'
			.' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id'
			.' LEFT JOIN #__phocacart_manufacturers AS m ON a.manufacturer_id = m.id'
			.' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
			.' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
			
			// user is in more groups, select lowest price by best group
			. ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON a.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN ('.$userGroups.') AND type = 3)'
			// user is in more groups, select highest points by best group
			. ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON a.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN ('.$userGroups.') AND type = 3)'
			
			.  $where
			. ' GROUP BY '.$groups
			. ' ORDER BY a.id';
		} else {
			$columns		=
			'a.id as id, a.title as title, a.alias as alias,'
			.' GROUP_CONCAT(DISTINCT c.id) as catid, GROUP_CONCAT(DISTINCT c.alias) as catalias, GROUP_CONCAT(DISTINCT c.title) as cattitle, COUNT(pc.category_id) AS count_categories';
			$groupsFull		= 'a.id, a.title, a.alias';
			$groupsFast		= 'a.id';
			$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

			$query = 
			 ' SELECT '.$columns
			.' FROM #__phocacart_products AS a'
			.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id'
			.' LEFT JOIN #__phocacart_categories AS c ON c.id =  pc.category_id'
			.' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
			.' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
			.  $where
			.' GROUP BY '.$groups
			.' ORDER BY a.id';
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
		$app				= JFactory::getApplication();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		$add_compare_method	= $paramsC->get( 'add_compare_method', 0 );
		$query				= $this->getQueryList($this->items);
		
		$d					= array();
		
		if ($query) {
			//echo nl2br(str_replace('#__', 'jos_', $query));
			$db->setQuery($query);
			$d['compare'] 			= $db->loadObjectList();
			PhocacartCategoryMultiple::setBestMatchCategory($d['compare'], $this->items, 1);// returned by reference
			
		}	
		$d['actionbase64']		= base64_encode($action);
		$d['linkcomparison']	= JRoute::_(PhocacartRoute::getComparisonRoute());
		$d['method']			= $add_compare_method;
			
		$layoutC 			= new JLayoutFile('list_compare', null, array('component' => 'com_phocacart'));
		echo $layoutC->render($d);
	}
	
	public function getFullItems() {
		
		$db 		= JFactory::getDBO();
		$query		= $this->getQueryList($this->items, 1);
		
		$products	= array();
		if ($query) {
			$db->setQuery($query);
			$products = $db->loadAssocList();
			
			PhocacartCategoryMultiple::setBestMatchCategory($products, $this->items);// returned by reference
		}
		return $products;
	
	}
	
	public function getComapareCountItems() {
		return count($this->items);
	}
}
?>