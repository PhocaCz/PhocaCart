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
use Phoca\PhocaCart\Constants\GroupType;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocacartDiscountCart
{
	private static $cart = [];

	/*
	 * ID ... id of cart
	 */
	public static function getCartDiscountsById($id = 0, $returnArray = 0)
    {
        if (is_null($id)) {
            throw new Exception('Function Error: No id passed', 500);
        }

        $id = (int)$id;

        $db   = Factory::getDBO();
        $user = PhocacartUser::getUser();

        $where   = [];
        $where[] = 'a.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')';
        $where[] = ' (ga.group_id IN (' . implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1)) . ') OR ga.group_id IS NULL)';
        $where[] = 'a.published = 1';

        $columns = 'a.id, a.discount, a.access, a.discount, a.total_amount, a.free_shipping, a.free_payment, a.calculation_type, a.quantity_from, a.quantity_to, a.valid_from, a.valid_to, a.category_filter, a.product_filter';

        if (PhocacartUtilsSettings::isFullGroupBy()) {
            $groupBy = $columns;
            if (I18nHelper::useI18n()) {
                $groupBy .= ' ';
            } else {
                $groupBy .= ', a.title, a.alias';
            }
        } else {
            $groupBy = 'a.id';
        }

       /* if (I18nHelper::useI18n()) {
            $columns .= ', coalesce(i18n.title, a.title) as title, coalesce(i18n.alias, a.alias) as alias';
        } else {
            $columns .= ', a.title, a.alias';
        }*/
        $columns .= I18nHelper::sqlCoalesce(['title', 'alias'], 'a', '', '', ',');

        $columns .= ', GROUP_CONCAT(DISTINCT dp.product_id) AS product' // line of selected products
            . ', GROUP_CONCAT(DISTINCT dc.category_id) AS category'; // line of selected categories

        $query = 'SELECT ' . $columns
            . ' FROM #__phocacart_discounts AS a'
            . I18nHelper::sqlJoin('#__phocacart_discounts_i18n')
            . ' LEFT JOIN #__phocacart_discount_products AS dp ON dp.discount_id = a.id'
            . ' LEFT JOIN #__phocacart_discount_categories AS dc ON dc.discount_id = a.id'
            . ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = ' . GroupType::Discount
            . ' WHERE ' . implode(' AND ', $where)
            . ' GROUP BY ' . $groupBy
            . ' ORDER BY a.id';

        PhocacartUtils::setConcatCharCount();
        $db->setQuery($query);
        if ($returnArray) {
            $discounts = $db->loadAssocList();
        } else {
            $discounts = $db->loadObjectList();
        }
        self::$cart[$id] = $discounts;

        return self::$cart[$id];
    }

	/*
	 * id ... id of current checked product
	 * quantity ... total quantity of all products
	 * amount	... total amount based on all products (affected by sales, discounts, etc.)
	 * subtotalAmount ... total amount based on all products (not affected by sales, discounts, etc.)
	 */

	public static function getCartDiscount($id = 0, $catid = 0, $quantity = 0, $amount = 0, $subtotalAmount = 0) {

		//$app									= Factory::getApplication();
		$paramsC 								= PhocacartUtils::getComponentParameters();
		$discount_priority						= $paramsC->get( 'discount_priority', 1 );
		$discount_subtotal_amount				= $paramsC->get( 'discount_subtotal_amount', 1 );


		// Cart discount applies to all cart, so we don't need to load it for each product
		// 1 mean id of cart, not id of product
		$discounts 	= self::getCartDiscountsById(1, 1);


		if (!empty($discounts)) {
			$bestKey 		= 0;// get the discount key which best meet the rules
			$maxDiscount	= 0;
			foreach($discounts as $k => $v) {

				// 1. ACCESS, PUBLISH CHECK, GROUP CHECK
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

				// 3. VALID TOTAL AMOUNT
				if (isset($v['total_amount'])) {

				    $currentAmount = $amount;
				    if ($discount_subtotal_amount == 2) {
				        $currentAmount = $subtotalAmount;
                    }


					if ($v['total_amount'] == 0) {
						// OK we don't check the total amount as zero means, no total amount limit
					} else if ($v['total_amount'] > 0 && $currentAmount < $v['total_amount']) {
						unset($discounts[$k]);
						continue;
					}
				} else {
					unset($discounts[$k]);
					continue;
				}

				// 4. VALID QUANTITY
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

				// 5. VALID PRODUCT
				if (!empty($v['product'])) {
					$ids	= explode(',', $v['product']);
					if (empty($ids)) {
						// OK we don't check the quantity as zero means, no product limit
					} else {
						if ($v['product_filter'] == 0) {
							// All except the selected
							if (in_array($id, $ids)) {
								unset($discounts[$k]);
								continue;
							}
						} else {
							// All selected
							if (!in_array($id, $ids)) {
								unset($discounts[$k]);
								continue;
							}
						}
					}
				}

				// 6. VALID CATEGORY
				if (!empty($v['category'])) {
					$catids	= explode(',', $v['category']);

					if (empty($catids)) {
						// OK we don't check the quantity as zero means, no category limit
					} else {
						if ($v['category_filter'] == 0) {
							// All except the selected
							if (in_array($catid, $catids)) {
								unset($discounts[$k]);
								continue;
							}
						} else {
							// All selected
							if (!in_array($catid, $catids)) {
								unset($discounts[$k]);
								continue;
							}
						}
					}
				}


				//$ids	= explode(',', $this->coupon['product']);
				/*$catids	= explode(',', $this->coupon['category']);

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
				}*/


				// 4. SELECT THE HIGHEST QUANTITY
				// When more product discounts fullfill the rules, select only one
				// Select the one with heighest quantity, e.g.:
				// minimum quantity = 10 -> discount 5%
				// minimum quantity = 20 -> discount 10%
				// minimum quantity = 30 -> discount 20%
				// If customer buys 50 items, we need to select 20% so both 5% and 10% should be unset
				// But if we have quantity_from == 0, this rule does not have quantity rule, it is first used.
				//4.1 if more discountes meet rule select the one with maxDiscount
				//4.2.if quantity is 0 for all select the largest discount (BUT be aware because of possible conflict)

				if ($discount_priority	== 2) {
					if ((int)$v['quantity_from'] == 0) {
						$maxDiscount 	= (int)$v['quantity_from'];
						$bestKey		= $k;
					} else if (isset($v['quantity_from']) && (int)$v['quantity_from'] > $maxDiscount) {
						$maxDiscount 	= (int)$v['quantity_from'];
						$bestKey		= $k;
					}
				} else {
					if ((int)$v['discount'] == 0) {
						$maxDiscount 	= (int)$v['discount'];
						$bestKey		= $k;
					} else if (isset($v['discount']) && (int)$v['discount'] > $maxDiscount) {
						$maxDiscount 	= (int)$v['discount'];
						$bestKey		= $k;
					}
				}

			}


			// POSSIBLE CONFLICT discount vs. quantity - solved by parameter
			// POSSIBLE CONFLICT percentage vs. fixed amount

			if (isset($discounts[$bestKey])) {

				return $discounts[$bestKey];
			} else {
				return false;
			}
		} else {
			return false;
		}

	}


	/*
	 * Static part - administration
	 */

	public static function storeDiscountProductsById($itemString, $discountId) {
		if ((int)$discountId > 0) {
			$db =Factory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_discount_products'
					. ' WHERE discount_id = '. (int)$discountId;
			$db->setQuery($query);
			$db->execute();

			if (isset($itemString) && $itemString != '') {

				$couponArray 	= explode(",", $itemString);
				$values 		= array();
				$valuesString 	= '';

				foreach($couponArray as $k => $v) {
					$values[] = ' ('.(int)$discountId.', '.(int)$v.')';
				}

				if (!empty($values)) {
					$valuesString = implode(',', $values);

					$query = ' INSERT INTO #__phocacart_discount_products (discount_id, product_id)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}


	public static function storeDiscountCatsById($catArray, $discountId) {

		if ((int)$discountId > 0) {
			$db =Factory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_discount_categories'
					. ' WHERE discount_id = '. (int)$discountId;
			$db->setQuery($query);
			$db->execute();

			if (isset($catArray) && !empty($catArray)) {

				$valuesString 	= '';

				foreach($catArray as $k => $v) {
					$values[] = ' ('.(int)$discountId.', '.(int)$v.')';
				}

				if (!empty($values)) {
					$valuesString = implode(',', $values);

					$query = ' INSERT INTO #__phocacart_discount_categories (discount_id, category_id)'
								.' VALUES '.(string)$valuesString;
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}

	public static function getDiscountProductsById($discounId, $select = 0) {

		$db =Factory::getDBO();

		if ($select == 1) {
			$query = 'SELECT di.discount_id';
		} else {
			$query = 'SELECT a.id as id, a.title as title, group_concat(c.title SEPARATOR \' \') AS categories_title';
		}
		$query .= ' FROM #__phocacart_products AS a'
		.' LEFT JOIN #__phocacart_discount_products AS di ON a.id = di.product_id'
		//.' LEFT JOIN #__phocacart_categories AS c ON a.catid = c.id'
		.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id = a.id'
		.' LEFT JOIN #__phocacart_categories AS c ON c.id = pc.category_id'
		.' WHERE di.discount_id = '.(int) $discounId;
		if ($select == 1) {
			$query .= ' GROUP BY di.discount_id';
		} else {
			$query .= ' GROUP BY di.discount_id, a.id, a.title';
		}
		$db->setQuery($query);

		if ($select == 1) {
			$coupon = $db->loadColumn();
		} else {
			$coupon = $db->loadObjectList();
		}
		return $coupon;
	}

	public static function getDiscountCatsById($discounId, $select = 0) {

		$db =Factory::getDBO();

		if ($select == 1) {
			$query = 'SELECT di.discount_id';
		} else {
			$query = 'SELECT c.id as value, c.title as text';
		}
		$query .= ' FROM #__phocacart_categories AS c'
		.' LEFT JOIN #__phocacart_discount_categories AS di ON c.id = di.category_id'
		.' WHERE di.discount_id = '.(int) $discounId;

		$db->setQuery($query);


		if ($select == 1) {
			$coupon = $db->loadColumn();
		} else {
			$coupon = $db->loadObjectList();
		}
		return $coupon;
	}



	/*
	 * Specific case: global cart discount will be displayed in category, item or items view
	 * but under specific rules like: only percentage, no total amount, no minimum quantity rule !!!
	 * Display the cart discount price in category, items or product view
	 */
	public static function getCartDiscountPriceForProduct($productId, $categoryId, &$priceItems) {


		$paramsC 								= PhocacartUtils::getComponentParameters();
		$display_discount_cart_product_views	= $paramsC->get( 'display_discount_cart_product_views', 0 );

		if ($display_discount_cart_product_views == 0) {
			return false;
		}

		// DISABLED FOR
		// when calculation is fixed amount (we cannot divide the discount into products which are not in checkout cart)
		// when rule TOTAL AMOUNT is active (displaying products is not checkout to check the total amount)
		// when rule MINIMUM QUANTITY is active (displaying products is not checkout to check the minimum quantity)

		$discount = self::getCartDiscount($productId, $categoryId, 0, 0);

		if (isset($discount['discount']) && isset($discount['calculation_type'])) {

			$priceItems['bruttotxt'] 	= $discount['title'];
			$priceItems['nettotxt'] 	= $discount['title'];

			$quantity					= 1;//Quantity for displaying the price in items,category and product view is always 1
			$total						= array();// not used in product view

			if ($discount['calculation_type'] == 0) {
				// FIXED AMOUNT
				// Fixed amount cannot be divided into product in views (category, items, item)
				// this is the opposite to checkout where we can divide fixed amount to all products added to cart
				$priceItems = array();
				return false;

			} else {
				// PERCENTAGE
				PhocacartCalculation::calculateDiscountPercentage($discount['discount'], $quantity, $priceItems, $total);
			}

			PhocacartCalculation::correctItemsIfNull($priceItems);
			PhocacartCalculation::formatItems($priceItems);
			return true;
		}

		$priceItems = array();
		return false;
	}


	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
}
