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
use Joomla\CMS\Factory;

class PhocacartCoupon
{
	protected $coupon;
	protected $type = array(0,1);// 0 all, 1 online shop, 2 pos (category type, payment method type, shipping method type)

	public function __construct() {}

	public function setType($type = array(0,1)) {
		$this->type = $type;
	}

	public function setCoupon($couponId = 0, $couponCode = '') {
		$db 		    = Factory::getDBO();
		$user 		    = PhocacartUser::getUser();
		$guest		    = PhocacartUserGuestuser::getGuestUser();
		$pos		    = PhocacartPos::isPos();// In POS coupons can be set for not selected user (when user is not selected)
		$params 	    = PhocacartUtils::getComponentParameters();
		$enable_coupons	= $params->get( 'enable_coupons', 2 );
		$userLevels	    = implode (',', $user->getAuthorisedViewLevels());
		$userGroups     = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
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

		// COUPON Type
        if (!empty($this->type) && is_array($this->type)) {
            $wheres[] = " c.type IN (" . implode(',', $this->type) . ')';
        }
		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );

		// MOVECOUPON
		// 1) user logged in ... OK
		// 2) guest checkout enabled in options and started by user ... OK
		// 3) POS
		// 4) $enable_coupons == 1 which means even not logged in user and not started guest checkout user can add coupon
		//
		// USERS AND COUPONS
		// 1) LOGGED IN USER
		// 2) USER STARTED GUEST CHECKOUT
		// 3) USER NOT LOGGED IN AND NOT STARTED GUEST CHECKOUT YET (PRE-GUEST/PRE-LOGGED-IN)
		if((isset($user->id) && $user->id > 0) || $guest || $pos || $enable_coupons == 1) {

			$columns		=
			'c.id, c.code, c.title, c.valid_from, c.valid_to, c.discount,'
			.' c.quantity_from, c.available_quantity, c.available_quantity_user, c.total_amount,'
			.' c.calculation_type, c.type, c.free_shipping, c.free_payment, c.category_filter, c.product_filter,'
			.' co.count AS count, cu.count AS countuser,'
			.' GROUP_CONCAT(DISTINCT cp.product_id) AS product,' // line of selected products
			.' GROUP_CONCAT(DISTINCT cc.category_id) AS category'; // line of selected categories
			$groupsFull		= 'c.id, c.code, c.title, c.valid_from, c.valid_to, c.discount,'
			.' c.quantity_from, c.available_quantity, c.available_quantity_user, c.total_amount,c.category_filter, c.product_filter,'
			.' c.calculation_type, c.type, c.free_shipping, c.free_payment, co.count, cu.count';
			//.' co.count AS count, cu.count AS countuser';
			$groupsFast		= 'c.id';
			$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;





			$query = 'SELECT '.$columns
			.' FROM #__phocacart_coupons AS c'
			.' LEFT JOIN #__phocacart_coupon_products AS cp ON cp.coupon_id = c.id'
			.' LEFT JOIN #__phocacart_coupon_categories AS cc ON cc.coupon_id = c.id'
			.' LEFT JOIN #__phocacart_coupon_count AS co ON co.coupon_id = c.id' // limit count for all coupons
			.' LEFT JOIN #__phocacart_coupon_count_user AS cu ON cu.coupon_id = c.id AND cu.user_id = '.(int)$user->id // limit c for user
			.' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 6'// type 6 is coupon
			. $where
			.' GROUP BY '.$groups;

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
				PhocacartLog::add(4, 'Message - Coupon not valid (Access, Published, Customer Group)', $couponId, 'Coupon code: '. $couponCode);
				return false;
			}

		} else {
			PhocacartLog::add(4, 'Message - Coupon not valid (User not logged in, No guest checkout, Coupons not enabled)', $couponId, 'Coupon code: '. $couponCode);
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


	public function checkCoupon($basicCheck = 0, $id = 0, $catid = 0, $quantity = 0, $amount = 0, $subtotalAmount = 0) {




		if (!empty($this->coupon)) {

		    $paramsC 								= PhocacartUtils::getComponentParameters();
		   // $discount_priority						= $paramsC->get( 'discount_priority', 1 );
		    $discount_subtotal_amount				= $paramsC->get( 'discount_subtotal_amount', 1 );


			// -----------
			// BASIC CHECK

			// 1. ACCESS, EXISTS, PUBLISHED, CUSTOMER GROUP, TYPE
			// Checked in SQL

			// 2. VALID DATE FROM TO CHECK
			if (isset($this->coupon['valid_from']) && isset($this->coupon['valid_to'])) {
				$valid = PhocacartDate::getActiveDate($this->coupon['valid_from'], $this->coupon['valid_to']);
				if ($valid != 1) {
					PhocacartLog::add(4, 'Message - Coupon not valid (Valid From - Valid To)', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
					return false;
				}
			} else {
				PhocacartLog::add(4, 'Message - Coupon not valid (Valid From - Valid To) - Empty data', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
				return false;
			}

			// 3. AVAILABLE QUANTITY
			if (isset($this->coupon['available_quantity'])) {

				if ((int)$this->coupon['available_quantity'] == 0) {
					// OK we don't check the quantity as zero means, no quantity limit
				} else if((int)$this->coupon['available_quantity'] > 0
				&& (int)$this->coupon['count'] == (int)$this->coupon['available_quantity']
				|| (int)$this->coupon['count'] > (int)$this->coupon['available_quantity']) {
					PhocacartLog::add(4, 'Message - Coupon not valid (Available Quantity)', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
					return false;
				}
				// OK
			} else {
				PhocacartLog::add(4, 'Message - Coupon not valid (Available Quantity) - Empty data', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
				return false;
			}

			// 4. QUANTITY USER
			if (isset($this->coupon['available_quantity_user'])) {
				if ((int)$this->coupon['available_quantity_user'] == 0) {
					// OK we don't check the quantity as zero means, no quantity limit
				} else if((int)$this->coupon['available_quantity_user'] > 0
				&& (int)$this->coupon['countuser'] == (int)$this->coupon['available_quantity_user']
				|| (int)$this->coupon['countuser'] > (int)$this->coupon['available_quantity_user']) {
					PhocacartLog::add(4, 'Message - Coupon not valid (Available Quantity - User)', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
					return false;
				}
				// OK
			} else {
				PhocacartLog::add(4, 'Message - Coupon not valid (Available Quantity - User) - Empty data', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
				return false;
			}

			if ($basicCheck) {
				return true;
			}


			// 5. VALID TOTAL AMOUNT
			if (isset($this->coupon['total_amount'])) {

			    $currentAmount = $amount;
                if ($discount_subtotal_amount == 2) {
                    $currentAmount = $subtotalAmount;
                }

				if ($this->coupon['total_amount'] == 0) {
					// OK we don't check the total amount as zero means, no total amount limit
				} else if ($this->coupon['total_amount'] > 0 && $currentAmount < $this->coupon['total_amount']) {
					PhocacartLog::add(4, 'Message - Coupon not valid (Total Amount)', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
					return false;
				}
			} else {
				PhocacartLog::add(4, 'Message - Coupon not valid (Total Amount) - Empty data', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
				return false;
			}

			// 6. VALID QUANTITY
			if (isset($this->coupon['quantity_from'])) {
				if ((int)$this->coupon['quantity_from'] == 0) {
					// OK we don't check the quantity as zero means, no quantity limit
				} else if((int)$this->coupon['quantity_from'] > 0 &&  (int)$quantity < (int)$this->coupon['quantity_from']) {
					PhocacartLog::add(4, 'Message - Coupon not valid (Quantity From)', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
					return false;
				}
			} else {
				PhocacartLog::add(4, 'Message - Coupon not valid (Quantity From) - Empty data', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
				return false;
			}

			// 7. VALID PRODUCT
			if (!empty($this->coupon['product'])) {
				$ids	= explode(',', $this->coupon['product']);

				if (empty($ids)) {
					// OK we don't check the quantity as zero means, no quantity limit
				} else {
					if ($this->coupon['product_filter'] == 0) {
						// All except the selected

						if (in_array($id, $ids)) {
							PhocacartLog::add(4, 'Message - Coupon not valid (Valid Product)', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
							return false;
						}
					} else {
						// All selected

						if (!in_array($id, $ids)) {
							PhocacartLog::add(4, 'Message - Coupon not valid (Valid Product) - Empty data', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
							return false;
						}
					}
				}
			}

			// 8. VALID CATEGORY
			if (!empty($this->coupon['category'])) {

				$catids	= explode(',', $this->coupon['category']);

				if (empty($catids)) {
					// OK we don't check the quantity as zero means, no quantity limit
				} else {

					if ($this->coupon['category_filter'] == 0) {
						// All except the selected
						if (in_array($catid, $catids)) {
							PhocacartLog::add(4, 'Message - Coupon not valid (Valid Category)', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
							return false;
						}
					} else {
						// All selected
						if (!in_array($catid, $catids)) {
							PhocacartLog::add(4, 'Message - Coupon not valid (Valid Category) - Empty data', $this->coupon['id'], 'Coupon title: '. $this->coupon['title']);
							return false;
						}
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
			$db =Factory::getDBO();
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
					$valuesString = implode(',', $values);

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
			$db =Factory::getDBO();
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
					$valuesString = implode(',', $values);

					$query = ' INSERT INTO #__phocacart_coupon_categories (coupon_id, category_id)'
								.' VALUES '.(string)$valuesString;
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}

	public static function getCouponProductsById($couponId, $select = 0) {

		$db =Factory::getDBO();

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

		$db =Factory::getDBO();

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

		$db =Factory::getDBO();
		$query = 'SELECT c.title'
		.' FROM #__phocacart_coupons AS c'
		.' WHERE c.id = '.(int) $couponId
		.' ORDER BY c.id'
		.' LIMIT 1';
		$db->setQuery($query);

		$coupon = $db->loadObject();
		return $coupon;
	}

	public static function getCouponInfoById($couponId) {

		$db =Factory::getDBO();
		$query = 'SELECT c.id, c.title, c.code'
		.' FROM #__phocacart_coupons AS c'
		.' WHERE c.id = '.(int) $couponId
		.' ORDER BY c.id'
		.' LIMIT 1';
		$db->setQuery($query);

		$coupon = $db->loadObject();
		return $coupon;
	}


	public static function storeCouponCount($couponId) {

		$idExists = 0;
		if ((int)$couponId > 0) {
			$db =Factory::getDBO();
			$query = ' SELECT id FROM #__phocacart_coupon_count WHERE coupon_id = '. (int)$couponId .' ORDER BY id LIMIT 1';
			$db->setQuery($query);
			$idExists = $db->loadResult();

			if ((int)$idExists > 0) {

				$query = 'UPDATE #__phocacart_coupon_count SET count = count + 1 WHERE id = '.(int)$idExists;
				$db->setQuery($query);
				$db->execute();
			} else {

				$valuesString 	= '('.(int)$couponId.', 1)';
				$query = ' INSERT INTO #__phocacart_coupon_count (coupon_id, count) VALUES '.(string)$valuesString;
				$db->setQuery($query);
				$db->execute();
			}
			return true;
		}

		return false;
	}

	public static function storeCouponCountUser($couponId, $userId) {

		$idExists = 0;
		if ((int)$couponId > 0 && (int)$userId > 0) {
			$db =Factory::getDBO();
			$query = ' SELECT coupon_id, user_id FROM #__phocacart_coupon_count_user WHERE coupon_id = '. (int)$couponId .' AND user_id = '.(int)$userId.' ORDER BY coupon_id LIMIT 1';
			$db->setQuery($query);
			$idExists = $db->loadAssoc();

			if (isset($idExists['coupon_id']) && isset($idExists['user_id']) && (int)$idExists['coupon_id'] > 0 && (int)$idExists['user_id'] > 0) {

				$query = 'UPDATE #__phocacart_coupon_count_user SET count = count + 1 WHERE coupon_id = '.(int)$idExists['coupon_id'] . ' AND user_id = '.(int)$idExists['user_id'];
				$db->setQuery($query);
				$db->execute();
			} else {

				$valuesString 	= '('.(int)$couponId.', '.(int)$userId.', 1)';
				$query = ' INSERT INTO #__phocacart_coupon_count_user (coupon_id, user_id, count) VALUES '.(string)$valuesString;
				$db->setQuery($query);
				$db->execute();
			}
			return true;
		}

		return false;
	}


	public static function generateCouponCode() {

	    $pC                   = PhocacartUtils::getComponentParameters();
        $gift_code_length     = $pC->get('gift_code_length', 8);
        $gift_code_characters = $pC->get('gift_code_characters', '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');

        $db =Factory::getDBO();
        // Limit attempts for generating coupon code - to protect before infinite loop
        for ($i = 0; $i < 7; $i++) {

            $o = "";
            for ($j = 0; $j < (int)$gift_code_length; $j++) {
                $o .= $gift_code_characters[mt_rand(0, strlen($gift_code_characters)-1)];
            }

            $query = ' SELECT code FROM #__phocacart_coupons WHERE code = '.$db->quote((string)$o).' ORDER BY id LIMIT 1';
			$db->setQuery($query);
			$code = $db->loadResult();

			// Generated code does not exist in database
			if(empty($code)) {
			    return $o;
            }
			echo $o;

        }

        // After 5 attempts no success, return timestamp + random
        $date = new DateTime();
        $random = PhocacartUtils::getRandomString(mt_rand(6, 10));
        return $date->getTimestamp() . strtoupper($random);





    }

    public static function getGiftsByOrderId($orderId) {

		$db =Factory::getDBO();
		$query = 'SELECT c.id, c.title, c.code, c.discount, c.valid_from, c.valid_to, c.type, c.published,'
            .' c.gift_order_id, c.gift_product_id, c.gift_order_product_id, c.coupon_type, c.gift_class_name,'
            .' c.gift_title, c.gift_description, c.gift_image, c.gift_recipient_name, c.gift_recipient_email, c.gift_sender_name, c.gift_sender_message, c.gift_type'
		    .' FROM #__phocacart_coupons AS c'
		    .' WHERE c.gift_order_id = '.(int)$orderId
		    .' ORDER BY c.id';
		$db->setQuery($query);

		$gifts = $db->loadAssocList();

		return $gifts;
	}

	public static function getGiftByCouponId($couponId) {

		$db =Factory::getDBO();
		$query = 'SELECT c.id, c.title, c.code, c.discount, c.valid_from, c.valid_to, c.type, c.published,'
            .' c.gift_order_id, c.gift_product_id, c.gift_order_product_id, c.coupon_type, c.gift_class_name,'
            .' c.gift_title, c.gift_description, c.gift_image, c.gift_recipient_name, c.gift_recipient_email, c.gift_sender_name, c.gift_sender_message, c.gift_type'
		    .' FROM #__phocacart_coupons AS c'
		    .' WHERE c.id = '.(int)$couponId
		    .' ORDER BY c.id';
		$db->setQuery($query);

		$gift = $db->loadAssoc();

		return $gift;
	}

	public static function activateAllGiftsByOrderId($orderId) {
	    $db 	= Factory::getDBO();

		$query = 'UPDATE #__phocacart_coupons SET'
		.' published = 1'
		.' WHERE gift_order_id = '.(int)$orderId;
		$db->setQuery($query);
		$db->execute();
		return true;
    }
}
