<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartCategoryMultiple
{
	public static function getCategories($productId, $select = 0) {
	
		$db = JFactory::getDBO();
		
		if ($select == 1) {
			$query = 'SELECT c.category_id';
		} else {
			$query = 'SELECT a.*';
		}
		$query .= ' FROM #__phocacart_categories AS a'
				.' LEFT JOIN #__phocacart_product_categories AS c ON a.id = c.category_id'
			    .' WHERE c.product_id = '.(int)$productId;
		$db->setQuery($query);

		if ($select == 1) {
			$tags = $db->loadColumn();
		} else {
			$tags = $db->loadObjectList();
		}	
	
		return $tags;
	}
	
	public static function getAllCategories() {
	
		$db = JFactory::getDBO();
		
		$query = 'SELECT a.id, a.title, a.alias FROM #__phocacart_categories AS a WHERE a.published = 1 ORDER BY a.id';
		$db->setQuery($query);
		$tags = $db->loadObjectList();	
	
		return $tags;
	}
	
	public static function storeCategories($storeArray, $productId) {
	
	
		if ((int)$productId > 0) {
			
			$db = JFactory::getDBO();
			/*$query = ' DELETE '
					.' FROM #__phocacart_product_categories'
					. ' WHERE product_id = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();*/
			
			// Select stored categories for this ID
			$query = 'SELECT a.category_id'
			.' FROM #__phocacart_product_categories AS a'
			.' WHERE a.product_id = '.(int)$productId.' ORDER BY a.product_id';
			$db->setQuery($query);
			$storedArray = $db->loadColumn();
			
				
			$store 	= array_diff($storeArray,$storedArray);// we only store categories which are not stored yet by this product id
			$delete = array_diff($storedArray, $storeArray);// category is stored in db but we removed it in administration so it is
															// not more selected for this product and we need to remove it
			
			if (!empty($delete)) {
				foreach($delete as $k => $v) {
					$query = ' DELETE '
					.' FROM #__phocacart_product_categories'
					. ' WHERE product_id = '. (int)$productId
					. ' AND category_id = '. (int)$v;
					$db->setQuery($query);
					$db->execute();
				}
			}
			
			if (!empty($store)) {
				
				$values 		= array();
				$valuesString 	= '';
				
				$store		= array_unique($store);
				
				foreach($store as $k => $v) {
					$o = self::getNextOrder((int)$productId, (int)$v);
					$values[] = ' ('.(int)$productId.', '.(int)$v.', '.(int)$o.')';
				}
				
				if (!empty($values)) {
					$valuesString = implode($values, ',');
					$query = ' INSERT INTO #__phocacart_product_categories (product_id, category_id, ordering)'
								.' VALUES '.(string)$valuesString;
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	
	public static function getNextOrder($pId, $cId) {
		$db 	= JFactory::getDBO();
		$where	= 'category_id ='.(int)$cId;
		$query 	= $db->getQuery(true)
			->select('MAX(ordering)')
			->from('#__phocacart_product_categories')
			->where($where);


		$db->setQuery($query);
		$max = (int)$db->loadResult();

		return ($max + 1);
	}
	
	public static function getCategoriesByProducts($productsA) {
		
		$productsS = '';
		$categories = '';
		if (!empty($productsA)) {
			$productsS = implode(',', $productsA);
			$db 	= JFactory::getDBO();
			$query = ' SELECT pc.product_id, c.id, c.alias, c.title FROM #__phocacart_categories AS c'
					.' LEFT JOIN #__phocacart_product_categories AS pc ON c.id = pc.category_id'
					.' WHERE pc.product_id IN ('.$productsS.')';
			$db->setQuery($query);
			$categories = $db->loadAssocList();
		}	
		return $categories;
	}
	
	public static function getAllCategoriesByProduct($productId) {
		$db = JFactory::getDBO();
		// Select stored categories for this ID
		$query = 'SELECT a.category_id'
		.' FROM #__phocacart_product_categories AS a'
		.' WHERE a.product_id = '.(int)$productId.' ORDER BY a.product_id';
		$db->setQuery($query);
		$storedArray = $db->loadColumn();
		return $storedArray;
	}
	
	/*
	*	index.php?option=com_phocacart&task=phocacartitem.removeduplicates
	*
	public static function removeDuplicates() {
		
		$db = JFactory::getDBO();

		$query = ' ALTER IGNORE TABLE '
		.' #__phocacart_product_categories'
		. ' ADD UNIQUE INDEX idx_category (product_id, category_id);';
		$db->setQuery($query);
		$db->execute();
		
		
		return true;
	} */
	
	/*
	* Try to find best category of the produt to build SEF
	* (e.g. if we are in category 5 and product is included in category 5, select this category)
	* We can get it per sql with help of group_concat
	*/
	
	public static function setCurrentCategory($items) {
		
		$app	= JFactory::getApplication();
		$catid	= $app->input->get('catid', 0, 'int');

		if (!empty($items) && (int)$catid > 0) {
			foreach ($items as $k => $v) {
				if ($v->categories != '') {
					$c = explode(',', $v->categories);
					if (!empty($c)) {
						foreach($c as $k2 => $v2) {
							$c2 = explode('|', $v2);
							if (isset($c2[0]) && (int)$c2[0] == (int)$catid) {
								
								$items[$k]->catid 		= $c2[0];
								$items[$k]->catalias	= '';
								if (isset($c2[1])) {
									$items[$k]->catalias 	= $c2[1];
								}
								$items[$k]->cattitle	= '';
								break;
							}
						}
					}
				}
			}
		}
		return $items;
	}
	
	public static function getCategoryByProduct($id, $catid) {
		
		$db 	= JFactory::getDBO();
		$query 	= 
			 ' SELECT c.id AS catid, c.title AS cattitle, c.alias AS catalias'
			.' FROM #__phocacart_categories AS c'
			.' LEFT JOIN #__phocacart_product_categories AS pc ON c.id = pc.category_id'
			.' WHERE pc.product_id = '.(int)$id.' AND c.id = '.(int)$catid
			.' ORDER BY c.id'
			.' LIMIT 1';
		$db->setQuery($query);
		$categories = $db->loadAssoc();
		return $categories;
	}
	
	public static function setBestMatchCategory(&$items, $categories, $object = 0) {


		if (!empty($items)) {
			
			if ($object) {
				foreach ($items as $k => &$v) {
					if (isset($v->count_categories) && (int)$v->count_categories > 1) {
						
						$id 	= (int)$v->id;
						$catid	= (int)$v->catid;
						if (isset($categories[$id]['catid']) && isset($catid) && (int)$categories[$id]['catid'] == $catid){
							continue;
							
						}
						// Try to find better category
						if (isset($categories[$id]['catid']) && isset($id)){
							$newItems = self::getCategoryByProduct($id, $categories[$id]['catid']);
							if (isset($newItems['catid']) && isset($newItems['cattitle']) && isset($newItems['catalias'])) {	
								$v->catid 		= $newItems['catid'];
								$v->cattitle 	= $newItems['cattitle'];
								$v->catalias 	= $newItems['catalias'];
							}
						}
					}
				}
					
			} else {
				foreach ($items as $k => &$v) {
					if (isset($v['count_categories']) && (int)$v['count_categories'] > 1) {
						
						$id 	= (int)$v['id'];
						$catid	= (int)$v['catid'];
						if (isset($categories[$id]['catid']) && isset($catid) && (int)$categories[$id]['catid'] == $catid){
							continue;
							
						}
						// Try to find better category
						if (isset($categories[$id]['catid']) && isset($id)){
							$newItems = self::getCategoryByProduct($id, $categories[$id]['catid']);
							if (isset($newItems['catid']) && isset($newItems['cattitle']) && isset($newItems['catalias'])) {
								$v['catid'] 	= $newItems['catid'];
								$v['cattitle'] 	= $newItems['cattitle'];
								$v['catalias'] 	= $newItems['catalias'];
							}
						}
					}
				}
			}
		}
	
		return $items;
	}
	
	public static function getCurrentCategoryId() {
		
		$app	= JFactory::getApplication();
		$id 	= $app->input->get('id', 0, 'int');
		$catid 	= $app->input->get('catid', 0, 'int');
		$view	= $app->input->get('view', '', 'string');
		$option	= $app->input->get('option', '', 'string');
		
		if ($option == 'com_phocacart' && $view == 'category') {
			return $id;
		} else if ($option == 'com_phocacart' && $view == 'item') {
			return $catid;
		}
		return 0;
	}
}