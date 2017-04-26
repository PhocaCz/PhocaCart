<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocacartDiscountProduct
{

	private static $product = array();
	
	private function __construct(){}
	
	/*
	 * ID ... id of product
	 */
	
	public static function getProductDiscountsById($id = 0, $returnArray = 0) {
	
		if( is_null( $id ) ) {
			throw new Exception('Function Error: No id added', 500);
			return false;
		}
		
		$id = (int)$id;
		
		if( !array_key_exists( $id, self::$product ) ) {

			$db 			= JFactory::getDBO();
			$user 			= JFactory::getUser();
			$userLevels		= implode (',', $user->getAuthorisedViewLevels());
			$where 			= array();
			$where[]		= "a.product_id = ".(int)$id;
			$where[] 		= "a.access IN (".$userLevels.")";
			$where 			= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
			
			$query = 'SELECT a.id, a.title, a.alias, a.discount, a.access, a.calculation_type, a.quantity_from, a.valid_from, a.valid_to'
					.' FROM #__phocacart_product_discounts AS a'
					. $where
					.' ORDER BY a.id';
			$db->setQuery($query);
			if ($returnArray) {
				$discounts = $db->loadAssocList();
			} else {
				$discounts = $db->loadObjectList();
			}
			self::$product[$id] = $discounts;
		}
		return self::$product[$id];
	}

	
	/*
	 * $groupQuantitry - Group: Product A Option 1 and Product A Option 2 is ONE PRODUCT
	 * $productQuantity - Product: Product A Option 1 and Product A Option 2 are TWO PRODUCTS
	 *
	 * When we apply discount to one product based on quantity, we need to differentiate
	 * if the quantity is based on one product variation - each product variation is single product
	 * of if the quantity is based on whole product (group) product count is sum of count of all product variations
	 */
	
	public static function getProductDiscount($id = 0, $groupQuantity = 0, $productQuantity = 0) {
		
		$app									= JFactory::getApplication();
		$paramsC 								= $app->isAdmin() ? JComponentHelper::getParams('com_phocacart') : $app->getParams();
		$discount_product_variations_quantity	= $paramsC->get( 'discount_product_variations_quantity', 1 );
		
		if ($discount_product_variations_quantity == 0) {
			$quantity = $productQuantity;
		} else {
			$quantity = $groupQuantity;
		}
		
		$discounts 	= self::getProductDiscountsById($id, 1);
		
		
		
		if (!empty($discounts)) {
			$maxQuantityKey = 0;// get the discount key with the maximal quantity
			$maxQuantity	= 0;
			foreach($discounts as $k => $v) {
				
				// 1. ACCESS CHECK 
				// Checked in SQL
				
				// 2. VALID DATE FROM TO CHECK
				if (isset($v['valid_from']) && isset($v['valid_to'])) {
					$valid = PhocacartDate::getActiveDate($v['valid_from'], $v['valid_to']);
					if ($valid != 1) {
						
						unset($discounts[$k]);
						continue;
					}
				} else {
				
					unset($discounts[$k]);
					continue;
				}
				
				// 3. VALID QUANTITY
				if (isset($v['quantity_from'])) {

					if ((int)$v['quantity_from'] == 0) {
						// OK we don't check the quantity as zero means, no quantity limit 
					} else if((int)$v['quantity_from'] > 0 &&  (int)$quantity < (int)$v['quantity_from']) {
					
						unset($discounts[$k]);
						continue;
					}
				} else {
					
					unset($discounts[$k]);
					continue;
				}
				
				// 4. SELECT THE HIGHEST QUANTITY
				// When more product discounts fullfill the rules, select only one
				// Select the one with heighest quantity, e.g.:
				// minimum quantity = 10 -> discount 5%
				// minimum quantity = 20 -> discount 10%
				// minimum quantity = 30 -> discount 20%
				// If customer buys 50 items, we need to select 20% so both 5% and 10% should be unset
				// But if we have quantity_from == 0, this rule does not have quantity rule, it is first used.
				if ((int)$v['quantity_from'] == 0) {
					$maxQuantity 	= (int)$v['quantity_from'];
					$maxQuantityKey	= $k;
				} else if (isset($v['quantity_from']) && (int)$v['quantity_from'] > $maxQuantity) {
					$maxQuantity 	= (int)$v['quantity_from'];
					$maxQuantityKey	= $k;
				}

			}

			
			if (isset($discounts[$maxQuantityKey])) {
				
				return $discounts[$maxQuantityKey];
			} else {
				return false;
			}
		} else {
			return false;
		}

	}
	
	

	
	
	
	
	/*
	 * Administration
	 */
	
	public static function getDiscountsById($productId, $returnArray = 0) {
	
		$db = JFactory::getDBO();
		
		$query = 'SELECT a.id, a.title, a.alias, a.discount, a.access, a.discount, a.calculation_type, a.quantity_from, a.valid_from, a.valid_to'
				.' FROM #__phocacart_product_discounts AS a'
			    .' WHERE a.product_id = '.(int) $productId
				.' ORDER BY a.id';
		$db->setQuery($query);
		if ($returnArray) {
			$discounts = $db->loadAssocList();
		} else {
			$discounts = $db->loadObjectList();
		}
		return $discounts;
	}
	
	/* $new = 1 When we copy a product, we create new one and we need to create new items for this product
	*/
	
	public static function storeDiscountsById($productId, $discsArray, $new = 0) {
	
		if ((int)$productId > 0) {
			$db =JFactory::getDBO();
			
			/*$query = ' DELETE '
					.' FROM #__phocacart_product_discounts'
					. ' WHERE product_id = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();*/
			
			$notDeleteDiscs = array();
			
			if (!empty($discsArray)) {
				$values 	= array();
				foreach($discsArray as $k => $v) {
					
					// Don't store empty discounts
					/*if ($v['title'] == '') {
						continue;
					}*/
					
					if(empty($v['alias'])) {
						$v['alias'] = $v['title'];
					}
					$v['alias'] = PhocacartUtils::getAliasName($v['alias']);
					
				
					
					// correct simple xml
					if (empty($v['title'])) 			{$v['title'] 			= '';}
					if (empty($v['alias'])) 			{$v['alias'] 			= '';}
					if (empty($v['access'])) 			{$v['access'] 			= '';}
					if (empty($v['discount'])) 			{$v['discount'] 		= '';}
					if (empty($v['calculation_type'])) 	{$v['calculation_type'] = '';}
					if (empty($v['quantity_from'])) 	{$v['quantity_from'] 	= '';}
					if (empty($v['quantity_to'])) 		{$v['quantity_to'] 		= '';}
					if (empty($v['valid_from'])) 		{$v['valid_from'] 		= '';}
					if (empty($v['valid_to'])) 			{$v['valid_to'] 		= '';}
					
					if ($v['discount'] == '') {
						continue;
					}
					
					$idExists = 0;
					
					if ($new == 0) {
						if (isset($v['id']) && $v['id'] > 0) {
							
							// Does the row exist
							$query = ' SELECT id '
							.' FROM #__phocacart_product_discounts'
							.' WHERE id = '. (int)$v['id']
							.' ORDER BY id';
							$db->setQuery($query);
							$idExists = $db->loadResult();
							
						}
					}
					
					if ((int)$idExists > 0) {
									
						$query = 'UPDATE #__phocacart_product_discounts SET'
						.' product_id = '.(int)$productId.','
						.' title = '.$db->quote($v['title']).','
						.' alias = '.$db->quote($v['alias']).','
						.' access = '.(int)$v['access'].','
						.' discount = '.$db->quote($v['discount']).','
						.' calculation_type = '.$db->quote($v['calculation_type']).','
						.' quantity_from = '.(int)$v['quantity_from'].','
						.' quantity_to = '.(int)$v['quantity_to'].','
						.' valid_from = '.$db->quote($v['valid_from']).','
						.' valid_to = '.$db->quote($v['valid_to']).','
						.' ordering = '.(int)$k
						.' WHERE id = '.(int)$idExists;
						$db->setQuery($query);
						$db->execute();
						
						$newIdD 				= $idExists;
						
					} else {
					
						$values 	= '('.(int)$productId.', '.$db->quote($v['title']).', '.$db->quote($v['alias']).', '.(int)$v['access'].', '.$db->quote($v['discount']).', '.(int)$v['calculation_type'].', '.(int)$v['quantity_from'].', '.(int)$v['quantity_to'].', '.$db->quote($v['valid_from']).', '.$db->quote($v['valid_to']).', '.(int)$k.')';
					

						$query = ' INSERT INTO #__phocacart_product_discounts (product_id, title, alias, access, discount, calculation_type, quantity_from, quantity_to, valid_from, valid_to, ordering)'
								.' VALUES '.$values;
						$db->setQuery($query);
						$db->execute();
						
						$newIdD = $db->insertid();
					}
					
					$notDeleteDiscs[]	= $newIdD;
				}
			}
			
			// Remove all discounts except the active
			if (!empty($notDeleteDiscs)) {
				$notDeleteDiscsString = implode($notDeleteDiscs, ',');
				$query = ' DELETE '
						.' FROM #__phocacart_product_discounts'
						.' WHERE product_id = '. (int)$productId
						.' AND id NOT IN ('.$notDeleteDiscsString.')';
				
			} else {
				$query = ' DELETE '
						.' FROM #__phocacart_product_discounts'
						.' WHERE product_id = '. (int)$productId;
			}
			$db->setQuery($query);
			$db->execute();
		}
	}
	
	/*
	public static function storeDiscountsById($productId, $discsArray) {
	
		if ((int)$productId > 0) {
			$db =JFactory::getDBO();
			
			$query = ' DELETE '
					.' FROM #__phocacart_product_discounts'
					. ' WHERE product_id = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();
			
			if (!empty($discsArray)) {
				$values 	= array();
				foreach($discsArray as $k => $v) {
					
					// Don't store empty discounts
					/*if ($v['title'] == '') {
						continue;
					}*//*
					
					if(empty($v['alias'])) {
						$v['alias'] = $v['title'];
					}
					$v['alias'] = PhocacartUtils::getAliasName($v['alias']);
					
				
					
					// correct simple xml
					if (empty($v['title'])) 			{$v['title'] 			= '';}
					if (empty($v['alias'])) 			{$v['alias'] 			= '';}
					if (empty($v['access'])) 			{$v['access'] 			= '';}
					if (empty($v['discount'])) 			{$v['discount'] 		= '';}
					if (empty($v['calculation_type'])) 	{$v['calculation_type'] = '';}
					if (empty($v['quantity_from'])) 	{$v['quantity_from'] 	= '';}
					if (empty($v['quantity_to'])) 		{$v['quantity_to'] 		= '';}
					if (empty($v['valid_from'])) 		{$v['valid_from'] 		= '';}
					if (empty($v['valid_to'])) 			{$v['valid_to'] 		= '';}
					
					if ($v['discount'] == '') {
						continue;
					}
					
					$values[] 	= '('.(int)$productId.', '.$db->quote($v['title']).', '.$db->quote($v['alias']).', '.(int)$v['access'].', '.$db->quote($v['discount']).', '.(int)$v['calculation_type'].', '.(int)$v['quantity_from'].', '.(int)$v['quantity_to'].', '.$db->quote($v['valid_from']).', '.$db->quote($v['valid_to']).', '.(int)$k.')';
				}
				
				if (!empty($values)) {
					$valuesString = implode($values, ',');
					$query = ' INSERT INTO #__phocacart_product_discounts (product_id, title, alias, access, discount, calculation_type, quantity_from, quantity_to, valid_from, valid_to, ordering)'
							.' VALUES '.(string)$valuesString;
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	*/
	
	
	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
}