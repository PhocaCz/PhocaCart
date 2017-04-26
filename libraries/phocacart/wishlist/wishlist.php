<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocacartWishlist
{
	protected $items     		= array();
	protected $user				= array();

	public function __construct() {
		$session 		= JFactory::getSession();
		$this->user		= JFactory::getUser();
		$app 			= JFactory::getApplication();
		
		
		if((int)$this->user->id > 0) {
			// DATABASE - logged in user - Singleton because of not load data from database every time wishlist instance is loaded
			// 1. Not found in DATABASE - maybe user logged in now, so:
			// 2. We try to find the data in SESSION, if they are still in SESSION - load them to our wishlist class and then
			// 3. Store them to DATABASE as all loged in users have wishlist in database and:
			// 4. Remove them from SESSION as they are stored in DATABASE
			$wLDb = $this->getWishListItemdDb();// user logged in - try to get wishlist from db
			$this->items 		= $wLDb;
		
			if(empty($this->items)) {
				$this->items	= $session->get('wishlist', array(), 'phocaCart');
				if(!empty($this->items)) {
					$this->updateWishListItems();
					$session->set('wishlist', array(), 'phocaCart');
				}
			}
		} else {
			$this->items	= $session->get('wishlist', array(), 'phocaCart');
		
		}
	}
	
	public function getWishListItemdDb() {
		if ($this->user->id > 0) {
			$db = JFactory::getDBO();
		
			$query = 'SELECT a.product_id, a.category_id, a.user_id'
					.' FROM #__phocacart_wishlists AS a'
					.' WHERE a.user_id = '.(int)$this->user->id
					.' ORDER BY a.id';
			$db->setQuery($query);
			$items = $db->loadAssocList();
			$itemsSorted = array();
			if (!empty($items)) {
				
				foreach($items as $k => $v) {
					$itemsSorted[$v['product_id']]['product_id'] 	= $v['product_id'];
					$itemsSorted[$v['product_id']]['category_id'] 	= $v['category_id'];
					$itemsSorted[$v['product_id']]['user_id'] 		= $v['user_id'];
				}

				if (!empty($itemsSorted)) {
					return $itemsSorted;
				}
				return false;
			}
		}
		return false;
	}
	
	public function updateWishListItems() {
		if ($this->user->id > 0) {
			$db 	= JFactory::getDBO();
			//$items	= serialize($this->items);
			$date 	= JFactory::getDate();
			$now	= $date->toSql();
			
		
			if (!empty($this->items)) {
				
				$q = '';
				// Unfortunately we need to run SQL query more times in foreach as 
				// ON DUPLICATE KEY UPDATE can work with one SQL uqery INSERT INTO ... VALUES (a,b,c), (a2,b2,c2)
				// but it does not work with multiple columns product_id x category_id x user_id in combination
				foreach($this->items as $k => $v) {
					if (isset($v['product_id']) && isset($v['category_id']) && (int)$v['product_id'] > 0 && (int)$v['category_id'] > 0) {
						$q = ' INSERT INTO #__phocacart_wishlists (product_id, category_id, user_id, date)';
						$q .= ' VALUES ('.(int)$v['product_id'].', '.(int)$v['category_id'].', '.(int)$this->user->id.',  '.$db->quote($now).')';;
						$q .= ' ON DUPLICATE KEY UPDATE  product_id = VALUES(product_id), category_id = VALUES(category_id), user_id = VALUES(user_id), date = VALUES(date);';
					}
				}
				if ($q != '') {
					$db->setQuery($q);
					$db->execute();
				}
				return true;
			}
			return false;
		} else {
			
			$session 		= JFactory::getSession();
			$session->set('wishlist', $this->items, 'phocaCart');
		}
		return false;
	}
	
	public function addItem($id = 0, $catid = 0) {
		if ($id > 0) {
			
			$app			= JFactory::getApplication();
			$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
			$maxWishListItems	= $paramsC->get( 'max_wishlist_items', 20 );
			
			$count = count($this->items);
		
			if ($count > (int)$maxWishListItems || $count == (int)$maxWishListItems) {
				$message = JText::_('COM_PHOCACART_COUNT_OF_PRODUCTS_IN_WISH_LIST_IS_LIMITED');
				$app->enqueueMessage($message, 'error');
				return false;
			}
			
			if(isset($this->items[$id]) && (int)$this->items[$id] > 0) {
				
				$message = JText::_('COM_PHOCACART_PRODUCT_INCLUDED_IN_WISH_LIST');
				$app->enqueueMessage($message, 'error');
				return false;
			} else {
				
				$this->items[$id]['product_id'] 	= $id;
				$this->items[$id]['category_id'] 	= $catid;
				if ($this->user->id > 0) {
					$this->items[$id]['user_id'] 	= $this->user->id;	
				}
				$this->updateWishListItems();
				return true;
			}
		}
		return false;
	}
	
	public function removeItem($id = 0) {
		if ($id > 0) {
			if(isset($this->items[$id]) && (int)$this->items[$id] > 0) {
				if ($this->user->id > 0) {
					if (isset($this->items[$id]['product_id']) && isset($this->items[$id]['category_id'])) {
						
						$db 	= JFactory::getDBO();
						$query = ' DELETE '
						.' FROM #__phocacart_wishlists'
						.' WHERE product_id = '.(int)$this->items[$id]['product_id']
						.' AND category_id =  '.(int)$this->items[$id]['category_id']
						.' AND user_id =  '.(int)$this->user->id;
						
						unset($this->items[$id]);// Because of ajax
						
						$db->setQuery($query);
						$db->execute();
						return true;
					} else {
						return false;
					}
					
				} else {
					unset($this->items[$id]);
					$session 		= JFactory::getSession();
					$session->set('wishlist', $this->items, 'phocaCart');
					return true;
				}

			} else {
				return false;
			}
			return false;
		}
		return false;
	}
	
	public function emptyWishList() {
		$session 		= JFactory::getSession();
		$session->set('wishlist', array(), 'phocaCart');
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
			//.' a.length, a.width, a.height, a.weight, a.volume,'
			.' a.stock, a.min_quantity, a.min_multiple_quantity, a.stockstatus_a_id, a.stockstatus_n_id, a.availability'
			//.' m.title as manufacturer_title'
			.' FROM #__phocacart_products AS a'
			.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id'
			.' LEFT JOIN #__phocacart_categories AS c ON c.id =  pc.category_id'
			//.' LEFT JOIN #__phocacart_manufacturers AS m ON a.manufacturer_id = m.id'
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
				if (isset($v['product_id']) && (int)$v['product_id'] > 0) {
					$itemsA[] = $v['product_id'];
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
		$app			= JFactory::getApplication();
		$paramsC 		= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$add_wishlist_method	= $paramsC->get( 'add_wishlist_method', 0 );
		$query				= $this->getQueryList($this->items);
		
		$d					= array();
		
		if ($query) {
			//echo nl2br(str_replace('#__', 'jos_', $query));
			$db->setQuery($query);
			$d['wishlist'] 			= $db->loadObjectList();
			
			$tempItems = $this->correctItems();
			PhocacartCategoryMultiple::setBestMatchCategory($d['wishlist'], $tempItems, 1);// returned by reference
			
		}	
		$d['actionbase64']		= base64_encode($action);
		$d['linkwishlist']		= JRoute::_(PhocacartRoute::getWishListRoute());
		$d['method']			= $add_wishlist_method;
			
		$layoutW 			= new JLayoutFile('list_wishlist', null, array('component' => 'com_phocacart'));
		echo $layoutW->render($d);
	}
	
	public function getFullItems() {
		
		$db 		= JFactory::getDBO();
		$query		= $this->getQueryList($this->items, 1);
		
		$products	= array();
		if ($query) {
			$db->setQuery($query);
			$products = $db->loadAssocList();
			
			
			$tempItems = array();
			if (!empty($this->items)) {
				foreach($this->items as $k => $v) {
					$tempItems[$k]['id'] 		= $v['product_id'];
					$tempItems[$k]['catid'] 	= $v['category_id'];
				}
			}
			$tempItems = $this->correctItems();
			PhocacartCategoryMultiple::setBestMatchCategory($products, $tempItems);// returned by reference
		}
		return $products;
	
	}
	
	// Correct $this->items array for finding the right category
	public function correctItems() {
		$tempItems = array();
		if (!empty($this->items)) {
			foreach($this->items as $k => $v) {
				$tempItems[$k]['id'] 		= $v['product_id'];
				$tempItems[$k]['catid'] 	= $v['category_id'];
			}
		}
		return $tempItems;
	}
	
	public function getWishListCountItems() {
		return count($this->items);
	}
}
?>