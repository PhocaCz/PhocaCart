<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartCoupon
{
	protected $coupon;

	public function __construct() {}
	
	public function setCoupon($couponId = 0, $couponCode = '') {
		$db 	= JFactory::getDBO();
		$user 	= JFactory::getUser();
		$guest	= PhocaCartGuestUser::getGuestUser();
		$where 	= array();
		
		if ((int)$couponId  > 0) {
			$where[]	= 'c.id = '.(int)$couponId;
		} else if ($couponCode != '') {
			$where[]	= 'c.code = '.$db->quote((string)$couponCode);
		} else {
			return false;
		}
		
		$where[]	= 'c.published = 1';
 
		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );
		
		if((isset($user->id) && $user->id > 0) || $guest) {
			$query = 'SELECT c.id, c.code, c.title, c.valid_from, c.valid_to, c.discount,'
			.' c.available_quantity, c.available_quantity_user, c.total_amount,'
			.' calculation_type, free_shipping,'
			.' co.count AS count, cu.count AS countuser,'
			.' GROUP_CONCAT(DISTINCT cp.product_id) AS product,' // line of selected products
			.' GROUP_CONCAT(DISTINCT cc.category_id) AS category' // line of selected categories
			.' FROM #__phocacart_coupons AS c'
			.' LEFT JOIN #__phocacart_coupon_products AS cp ON cp.coupon_id = c.id'
			.' LEFT JOIN #__phocacart_coupon_categories AS cc ON cc.coupon_id = c.id'
			.' LEFT JOIN #__phocacart_coupon_count AS co ON co.coupon_id = c.id' // limit count for all coupons
			.' LEFT JOIN #__phocacart_coupon_count_user AS cu ON cu.coupon_id = c.id AND cu.user_id = '.(int)$user->id // limit c for user
			. $where
			//.' GROUP BY c.id'
			.' ORDER BY c.id'
			.' LIMIT 1';
			
			$db->setQuery($query);
			$coupon = $db->loadAssoc(); 
		
			if (!empty($coupon)) {
				$this->coupon = $coupon;
				return true;
			} else {
				$this->coupon = false;
				return false;
			}
			
		} else {
			return false;
		}
	}
	
	public function getCoupon() {
		return $this->coupon;
	}
	
	
	public function checkCouponBasic() {
		
		
		if (!empty($this->coupon)) {
			// 1. EXISTS, PUBLISHED - solved in SQL query
		
			// 2. DATE
			if (isset($this->coupon['valid_from']) && isset($this->coupon['valid_to'])) {
				$valid = PhocaCartDate::getActiveDate($this->coupon['valid_from'], $this->coupon['valid_to']);
				if ($valid != 1) {
					return false;
				}
				// OK
			} else {
				return false;
			}
		
			// 3. QUANTITY
			if (isset($this->coupon['available_quantity'])) {
				
				if ((int)$this->coupon['available_quantity'] == 0) {
					// OK we don't check the quantity as zero means, no quantity limit 
				} else if((int)$this->coupon['available_quantity'] > 0
				&& (int)$this->coupon['count'] == (int)$this->coupon['available_quantity']
				|| (int)$this->coupon['count'] > (int)$this->coupon['available_quantity']) {
					return false;
				}
				// OK
			} else {
				return false;
			}
			
			// 4. QUANTITY USER
			if (isset($this->coupon['available_quantity_user'])) {
				if ((int)$this->coupon['available_quantity_user'] == 0) {
					// OK we don't check the quantity as zero means, no quantity limit 
				} else if((int)$this->coupon['available_quantity_user'] > 0
				&& (int)$this->coupon['countuser'] == (int)$this->coupon['available_quantity_user']
				|| (int)$this->coupon['countuser'] > (int)$this->coupon['available_quantity_user']) {
					return false;
				}
				// OK
			} else {
				return false;
			}
			
			// Seems like everything is Ok
			return true;
		}
		return false;
	}
	
	public function checkCouponAdvanced($id, $catid) {
		
		
		if (!empty($this->coupon)) {
			
			$ids	= explode(',', $this->coupon['product']);
			$catids	= explode(',', $this->coupon['category']);
			
			// Products
			if(!empty($ids)) {
				if (in_array($id, $ids)) {
					return true;
				}
			}
			
			// Categories
			if(!empty($catids)) {
				if (in_array($catid, $catids)) {
					return true;
				}
			}
			
			// No condition regarding ids or catids was set, coupon is valid for every item
			if (empty($ids) && empty($catids)) {
				return true;
			}
			
			if (isset($ids[0]) && $ids[0] == '' && isset($catids[0]) && $catids[0] == '') {
				return true;
			}

			return false;
		}
		return false;
	}
	
	public function checkCouponTotal($amount, $totalAmount) {
		if (!empty($this->coupon)) {
			if ($amount > $totalAmount) {
				return true;
			}
			return false;
		}
		return false;
	}
	
	
	
	
	
	
	/*
	 * Static part
	 */
	public static function storeCouponProductsById($itemString, $couponId) {
		if ((int)$couponId > 0) {
			$db =JFactory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_coupon_products'
					. ' WHERE coupon_id = '. (int)$couponId;
			$db->setQuery($query);
			$db->execute();
			
			if (isset($itemString) && $itemString != '') {
				
				$couponArray 	= explode(",", $itemString);
				$values 		= array();
				$valuesString 	= '';
				
				foreach($couponArray as $k => $v) {
					$values[] = ' ('.(int)$couponId.', '.(int)$v.')';
				}
				
				if (!empty($values)) {
					$valuesString = implode($values, ',');
				
					$query = ' INSERT INTO #__phocacart_coupon_products (coupon_id, product_id)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	
	
	public static function storeCouponCatsById($catArray, $couponId) {
	
		if ((int)$couponId > 0) {
			$db =JFactory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_coupon_categories'
					. ' WHERE coupon_id = '. (int)$couponId;
			$db->setQuery($query);
			$db->execute();
			
			if (isset($catArray) && !empty($catArray)) {
				
				$valuesString 	= '';
				
				foreach($catArray as $k => $v) {
					$values[] = ' ('.(int)$couponId.', '.(int)$v.')';
				}
				
				if (!empty($values)) {
					$valuesString = implode($values, ',');
				
					$query = ' INSERT INTO #__phocacart_coupon_categories (coupon_id, category_id)'
								.' VALUES '.(string)$valuesString;
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	
	public static function getCouponProductsById($couponId, $select = 0) {
	
		$db =JFactory::getDBO();
		
		if ($select == 1) {
			$query = 'SELECT co.coupon_id';
		} else {
			$query = 'SELECT a.id as id, a.title as title, group_concat(c.title SEPARATOR \' \') AS categories_title';
		}
		$query .= ' FROM #__phocacart_products AS a'
		.' LEFT JOIN #__phocacart_coupon_products AS co ON a.id = co.product_id'
		//.' LEFT JOIN #__phocacart_categories AS c ON a.catid = c.id'
		.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
		.' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
		.' WHERE co.coupon_id = '.(int) $couponId
		.' GROUP BY a.id';
		$db->setQuery($query);
		
		if ($select == 1) {
			$coupon = $db->loadColumn();
		} else {
			$coupon = $db->loadObjectList();
		}
		return $coupon;
	}
	
	public static function getCouponCatsById($couponId, $select = 0) {
	
		$db =JFactory::getDBO();
 
		if ($select == 1) {
			$query = 'SELECT co.coupon_id';
		} else {
			$query = 'SELECT c.id as value, c.title as text';
		}
		$query .= ' FROM #__phocacart_categories AS c'
		.' LEFT JOIN #__phocacart_coupon_categories AS co ON c.id = co.category_id'
		.' WHERE co.coupon_id = '.(int) $couponId;
		
		$db->setQuery($query);
		
		
		if ($select == 1) {
			$coupon = $db->loadColumn();
		} else {
			$coupon = $db->loadObjectList();
		}
		return $coupon;
	}
	
	public static function getCouponTitleById($couponId) {
	
		$db =JFactory::getDBO();
		$query = 'SELECT c.title'
		.' FROM #__phocacart_coupons AS c'
		.' WHERE c.id = '.(int) $couponId
		.' ORDER BY c.id'
		.' LIMIT 1';	
		$db->setQuery($query);
		
		$coupon = $db->loadObject();
		return $coupon;
	}
}