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

class PhocacartCoupon
{
	protected $coupon;

	public function __construct() {}
	
	public function setCoupon($couponId = 0, $couponCode = '') {
		$db 		= JFactory::getDBO();
		$user 		= JFactory::getUser();
		$guest		= PhocacartUserGuestuser::getGuestUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
		$wheres 		= array();
		
		if ((int)$couponId  > 0) {
			$wheres[]	= 'c.id = '.(int)$couponId;
		} else if ($couponCode != '') {
			$wheres[]	= 'c.code = '.$db->quote((string)$couponCode);
		} else {
			return false;
		}
		
		// ACCESS
		$wheres[] 	= " c.access IN (".$userLevels.")";
		$wheres[] 	= " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";
		$wheres[]	= ' c.published = 1';
 
		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );
		
		if((isset($user->id) && $user->id > 0) || $guest) {
			$query = 'SELECT c.id, c.code, c.title, c.valid_from, c.valid_to, c.discount,'
			.' c.quantity_from, c.available_quantity, c.available_quantity_user, c.total_amount,'
			.' c.calculation_type, c.free_shipping, c.free_payment,'
			.' co.count AS count, cu.count AS countuser,'
			.' GROUP_CONCAT(DISTINCT cp.product_id) AS product,' // line of selected products
			.' GROUP_CONCAT(DISTINCT cc.category_id) AS category' // line of selected categories
			.' FROM #__phocacart_coupons AS c'
			.' LEFT JOIN #__phocacart_coupon_products AS cp ON cp.coupon_id = c.id'
			.' LEFT JOIN #__phocacart_coupon_categories AS cc ON cc.coupon_id = c.id'
			.' LEFT JOIN #__phocacart_coupon_count AS co ON co.coupon_id = c.id' // limit count for all coupons
			.' LEFT JOIN #__phocacart_coupon_count_user AS cu ON cu.coupon_id = c.id AND cu.user_id = '.(int)$user->id // limit c for user
			.' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 6'// type 6 is coupon
			. $where
			.' GROUP BY c.id, c.code, c.title, c.valid_from, c.valid_to, c.discount,'
			.' c.quantity_from, c.available_quantity, c.available_quantity_user, c.total_amount,'
			.' c.calculation_type, c.free_shipping, c.free_payment, co.count, cu.count';
			//.' co.count AS count, cu.count AS countuser';
			
			$query .= ' ORDER BY c.id'
			.' LIMIT 1';
			
			PhocacartUtils::setConcatCharCount();
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
	
	
	/*
	 * Coupon true does not mean it is valid
	 * COUPON TRUE = access, published, available quantity, quantity user (basicCheck)
	 * COUPON VALID = COUPON TRUE + total amount, total quantity, product id, category id checked (advancedCheck)
	 */
	
	
	public function checkCoupon($basicCheck = 0, $id = 0, $catid = 0, $quantity = 0, $amount = 0) {
		
	
		if (!empty($this->coupon)) {
			
			// -----------
			// BASIC CHECK

			// 1. ACCESS, EXISTS, PUBLISHED, CUSTOMER GROUP
			// Checked in SQL
		
			// 2. VALID DATE FROM TO CHECK
			if (isset($this->coupon['valid_from']) && isset($this->coupon['valid_to'])) {
				$valid = PhocacartDate::getActiveDate($this->coupon['valid_from'], $this->coupon['valid_to']);
				if ($valid != 1) {
					return false;
				}
			} else {
				return false;
			}
			
			// 3. AVAILABLE QUANTITY
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
			
			if ($basicCheck) {
				return true;
			}
			

			// 5. VALID TOTAL AMOUNT
			if (isset($this->coupon['total_amount'])) {
				if ($this->coupon['total_amount'] == 0) {
					// OK we don't check the total amount as zero means, no total amount limit 
				} else if ($this->coupon['total_amount'] > 0 && $amount < $this->coupon['total_amount']) {
					return false;
				}
			} else {
				return false;
			}
			
			// 6. VALID QUANTITY
			if (isset($this->coupon['quantity_from'])) {
				if ((int)$this->coupon['quantity_from'] == 0) {
					// OK we don't check the quantity as zero means, no quantity limit 
				} else if((int)$this->coupon['quantity_from'] > 0 &&  (int)$quantity < (int)$this->coupon['quantity_from']) {
					return false;
				}
			} else {
				return false;
			}
			
			// 7. VALID PRODUCT
			if (!empty($this->coupon['product'])) {
				$ids	= explode(',', $this->coupon['product']);
				if (empty($ids)) {
					// OK we don't check the quantity as zero means, no quantity limit 
				} else {
					if (!in_array($id, $ids)) {
						return false;
					}
					
				}
			}
			
			// 8. VALID CATEGORY
			if (!empty($this->coupon['category'])) {
				$catids	= explode(',', $this->coupon['category']);
				
				if (empty($catids)) {
					// OK we don't check the quantity as zero means, no quantity limit 
				} else {
					if (!in_array($catid, $catids)) {
						return false;
					}
				}
			}
			
			// Seems like everything is Ok
			$this->coupon['valid'] = 1;
			return true;
		}
		return false;
	}
	/*
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
	*/
	
	
	
	
	
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
		.' WHERE co.coupon_id = '.(int) $couponId;
		if ($select == 1) {
			$query .= ' GROUP BY co.coupon_id';
		} else {
			$query .= ' GROUP BY co.coupon_id, a.id, a.title';
		}
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