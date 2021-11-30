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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;


/* TYPES
 * 1	...	user				... #__phocacart_user_groups
 * 2	... category			...	#__phocacart_category_groups
 * 3	...	product				...	#__phocacart_product_groups
 * 4	... productdiscount		...	#__phocacart_product_discount_groups
 * 5	... discount			...	#__phocacart_discount_groups
 * 6	... coupon				... #__phocacart_coupon_groups
 * 7	... shipping			... #__phocacart_shipping_method_groups
 * 8	... payment				... #__phocacart_payment_method_groups
 * 9	... formfield			...	#__phocacart_form_fields_groups
 *
 * #__phocacart_item_groups replace all these tables
 */

 /* Example:
 * To get some group price for customer:
 * - user must be assigned to the group: 1 user - user id
 * - product must be assigned to the group: 3 product - product id
 * - category must be assigned to the group: 2 category - category id
 */

class PhocacartGroup
{

	private static $group			= array();

	public static function getAllGroupsSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id', $attributes = 'class="form-select" size="4" multiple="multiple"' ) {

		$db = Factory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_groups AS a'
				. ' ORDER BY '. $order;
		$db->setQuery($query);

		$groups = $db->loadObjectList();

		foreach($groups as $k => $v) {
			$groups[$k]->text = Text::_($v->text);
		}


		$groupsO = HTMLHelper::_('select.genericlist', $groups, $name, $attributes . ' '. $javascript, 'value', 'text', $activeArray, $id);

		return $groupsO;
	}

	public static function getAllGroups($order = 'id'){
		$db = Factory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_groups AS a'
				. ' ORDER BY '. $order;
		$db->setQuery($query);

		$groups = $db->loadObjectList();

		foreach($groups as $k => $v) {
			$groups[$k]->text = Text::_($v->text);
		}
		return $groups;
	}

	/*
	 * id ... user id
	 * type ... see header comment in this class
	 *
	 */


	public static function getGroupsById($id, $type , $returnArray = 0, $productId = 0) {

		$key = base64_encode(serialize((int)$id . ':' . (int)$type . (int)$returnArray . ':' . (int)$productId ));

		if( !array_key_exists( (string)$key, self::$group )) {
			if ((int)$id > 0) {
				$db = Factory::getDBO();

				$query = 'SELECT a.id, a.title, a.alias, a.type, a.published, a.display_price, a.display_addtocart, a.display_attributes'
						.' FROM #__phocacart_groups AS a'
						.' LEFT JOIN #__phocacart_item_groups AS g ON g.group_id = a.id'
						.' WHERE g.item_id = '.(int) $id
						.' AND g.type = '.(int)$type;
				if ($productId > 0) {
					$query .= ' AND g.product_id = '.(int)$productId;
				}
				$query .= ' ORDER BY a.id';

				$db->setQuery($query);

				if ($returnArray == 1) {
					$items = $db->loadColumn();
					if (empty($items)) {
						$items = array(0 => 1);// Default is default for all
					}
				} else if ($returnArray == 2) {
					$items = $db->loadAssocList();
					if (empty($items)) {
						$items[0]['id'] 	        	= 1;
						$items[0]['title'] 	        	= 'COM_PHOCACART_DEFAULT';
						$items[0]['alias']      		= 'com-phocacart-default';
						$items[0]['type'] 	       	 	= 1;
                        $items[0]['display_price'] 		= 1;
						$items[0]['display_addtocart'] 	= 1;
						$items[0]['display_attributes'] = 1;
                        $items[0]['published'] 	    	= 1;
					}
				} else {
					$items = $db->loadObjectList();
					if (empty($items)) {
						$items[0]->id 		        	= 1;
						$items[0]->title 	        	= 'COM_PHOCACART_DEFAULT';
						$items[0]->alias 	        	= 'com-phocacart-default';
						$items[0]->type 	        	= 1;
                        $items[0]->display_price 		= 1;
						$items[0]->display_addtocart 	= 1;
						$items[0]->display_attributes 	= 1;
                        $items[0]->published 	    	= 1;
					}
				}

				self::$group[$key] = $items;

			} else {
				self::$group[$key] = array(0 => 1);
			}
		}


		if (!empty(self::$group[$key])) {
			return self::$group[$key];
		} else {
			return array(0 => 1);
		}
	}


	public static function getDefaultGroup($select = 0) {

		$db = Factory::getDBO();

		if ($select == 1) {
			$query = 'SELECT a.id';
		} else {
			$query = 'SELECT a.id, a.title, a.alias, a.type';
		}
		$query .= ' FROM #__phocacart_groups AS a'
			    .' WHERE a.id = 1 AND a.type = 1'
				.' ORDER BY a.id';
		$db->setQuery($query);
		if ($select == 1) {
			$attributes = $db->loadColumn();
		} /*else if ($returnArray == 2) {
			$attributes = $db->loadAssocList();
		}*/ else {
			$attributes = $db->loadObjectList();
		}

		return $attributes;
	}

	/*
	 * Product ID is only used by product discounts - to successfully clean the table we need info about
	 * in which product the product discount is used
	 */

	public static function storeGroupsById($id, $type, $groups, $productId = 0) {


		if ((int)$id > 0) {
			$db =Factory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_item_groups'
					. ' WHERE item_id = '. (int)$id;
			if ($productId > 0) {
				$query .= ' AND product_id = '.(int)$productId;
			}
			$query .= ' AND type = '.(int)$type;

			$db->setQuery($query);
			$db->execute();

			if (!empty($groups)) {

				$values 		= array();
				$activeGroups	= array();
				foreach($groups as $k => $v) {
					$values[] 		= ' ('.(int)$id.', '.(int)$v.', '.(int)$productId.', '.(int)$type.')';
					$activeGroups[] = (int)$v;
				}

				if (!empty($values)) {
					$valuesString = implode(',', $values);

					$query = ' INSERT INTO #__phocacart_item_groups (item_id, group_id, product_id, type)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();

				}

				// Product groups have two tables assinged
				// phocacart_product_price_groups
				// phocacart_product_point_groups
				if (!empty($activeGroups) && $type == 3) {
					$activeGroupsS = implode(',', $activeGroups);

					$q1 = 'DELETE FROM #__phocacart_product_price_groups'
						. ' WHERE product_id = '.(int)$id
						. ' AND group_id NOT IN ( '.$activeGroupsS.' )';
					$db->setQuery( $q1 );
					$db->execute();

					$q1 = 'DELETE FROM #__phocacart_product_point_groups'
						. ' WHERE product_id = '.(int)$id
						. ' AND group_id NOT IN ( '.$activeGroupsS.' )';
					$db->setQuery( $q1 );
					$db->execute();
				}
			}
		}
	}

	public static function getGroupRules() {

		$db = Factory::getDBO();

		$query = 'SELECT a.id, a.type, a.minimum_sum'
				.' FROM #__phocacart_groups AS a'
			    //.' WHERE a.published = 1'
				//.' WHERE a.type <> 1'
			    .' ORDER BY a.id';
		$db->setQuery($query);
		$groups = $db->loadAssocList();

		return $groups;
	}


	public static function changeUserGroupByRule($userId) {

		$app						= Factory::getApplication();
		$paramsC 					= PhocacartUtils::getComponentParameters();
		$user_group_change_rule		= $paramsC->get('user_group_change_rule', 0);

		if ($user_group_change_rule == 0) {
			// User Group Change is not enabled
			return true;
		}

		if ($userId > 0) {

			$total	= PhocacartUser::getUserOrderSum($userId);

			$groups = self::getGroupRules();

			$rulesActive = 0;
			if (!empty($groups)) {
				foreach($groups as $k => $v) {
					if ($v['minimum_sum'] > 0) {
						$rulesActive = 1;
					}
				}
			}

			if ($rulesActive == 0) {
				// Seems like user group change rules are all empty
				return true;
			}

			$groupsNew 			= array();
			$groupsNewDefault	= array();
			$t = 0;
			if (!empty($groups)) {
				foreach($groups as $k => $v) {


					if ($user_group_change_rule	== 2) {
						if ($v['type'] == 1) {
							$groupsNewDefault[$k] = $v['id']; // Default is always included
						} else if ($v['minimum_sum'] < $total || $v['minimum_sum'] == $total) {
							$groupsNew[$k] = $v['id'];
						}
					} else {
					// SELECT ONLY ONE GROUP including default
						if ($v['type'] == 1) {
							$groupsNewDefault[$k] = $v['id']; // Default is always included
						} else if ($v['minimum_sum'] < $total || $v['minimum_sum'] == $total) {

							if ($total > $t) {
								$groupsNew[0] = $v['id'];
								$t = $total;
							}
						}
					}
				}
			}


			$groupsAll = array_merge($groupsNew, $groupsNewDefault);

			if (!empty($groupsAll)) {
				self::storeGroupsById((int)$userId, 1, $groupsAll);
			}
			return true;
		}
		return false;
	}


	// Group prices and points

	public static function getProductPriceGroupsById($productId) {

		$db = Factory::getDBO();

		$query = 'SELECT a.id, a.product_id, a.group_id, a.price'
				.' FROM #__phocacart_product_price_groups AS a'
			    .' WHERE a.product_id = '.(int) $productId
			    .' ORDER BY a.id';
		$db->setQuery($query);

		$items = $db->loadAssocList();

		$itemsNew = array();
		if (!empty($items)) {
			foreach ($items as $k => $v) {
				$newK = $v['group_id'];
				$itemsNew[$newK] = $v;
			}
		}
		return $itemsNew;
	}

	public static function getProductPointGroupsById($productId) {

		$db = Factory::getDBO();

		$query = 'SELECT a.id, a.product_id, a.group_id, a.points_received'
				.' FROM #__phocacart_product_point_groups AS a'
			    .' WHERE a.product_id = '.(int) $productId
			    .' ORDER BY a.id';
		$db->setQuery($query);

		$items = $db->loadAssocList();

		$itemsNew = array();
		if (!empty($items)) {
			foreach ($items as $k => $v) {
				$newK = $v['group_id'];
				$itemsNew[$newK] = $v;
			}
		}
		return $itemsNew;
	}


	public static function storeProductPriceGroupsById($data, $productId) {


		if (!empty($data)) {

			$app	= Factory::getApplication();
			$db 	= Factory::getDBO();

			$notDeleteItems = array();

			foreach($data as $k => $v) {
				if (!isset($v['price']) || (isset($v['price']) && $v['price'] == '')) {
					// Price is not 0, price is empty
					// We can set price to zero so we need to differentiate between zero and not set
					continue;
				}

				//$row = JTable::getInstance('PhocacartProductPriceGroup', 'Table', array());
				$idExists = 0;
				if(isset($v['product_id']) && $v['product_id'] != '' && isset($v['group_id']) && $v['group_id'] != '') {

					$query = ' SELECT id'
					.' FROM #__phocacart_product_price_groups'
					.' WHERE product_id = '. (int)$v['product_id']
					.' AND group_id = '. (int)$v['group_id']
					.' ORDER BY id';
					$db->setQuery($query);
					$idExists = $db->loadResult();

				}

				if ((int)$idExists > 0) {

					$query = 'UPDATE #__phocacart_product_price_groups SET'
					.' product_id = '.(int)$v['product_id'].','
					.' group_id = '. (int)$v['group_id'].','
					.' price = '.$db->quote($v['price'])
					.' WHERE id = '.(int)$idExists;
					$db->setQuery($query);
					$db->execute();
					$newIdD = $idExists;

				} else {

					if (isset($v['id']) && (int)$v['id'] > 0) {
						// IMPORT
						$values 	= '('.(int)$v['id'].', '.(int)$v['product_id'].', '.(int)$v['group_id'].', '.$db->quote($v['price']).')';
						$query = ' INSERT INTO #__phocacart_product_price_groups (id, product_id, group_id, price) VALUES '.$values;
						$db->setQuery($query);

						$db->execute();
						$newIdD = (int)$v['id'];

					} else {

						// NEW ITEM
						$values 	= '('.(int)$v['product_id'].', '.(int)$v['group_id'].', '.$db->quote($v['price']).')';
						$query = ' INSERT INTO #__phocacart_product_price_groups (product_id, group_id, price) VALUES '.$values;
						$db->setQuery($query);
						$db->execute();
						$newIdD = $db->insertid();
					}
				}


				if (isset($newIdD) && (int)$newIdD > 0) {
					$notDeleteItems[] = (int)$newIdD;
				}

			}

			if (!empty($notDeleteItems)) {
				$notDeleteItemsString = implode(',', $notDeleteItems);
				$query = ' DELETE '
						.' FROM #__phocacart_product_price_groups'
						.' WHERE product_id = '. (int)$productId
						.' AND id NOT IN ('.$notDeleteItemsString.')';

			} else {
				$query = ' DELETE '
						.' FROM #__phocacart_product_price_groups'
						.' WHERE product_id = '. (int)$productId;

			}

			$db->setQuery($query);
			$db->execute();

		}

		return true;
	}

	public static function storeProductPointGroupsById($data, $productId) {

		if (!empty($data)) {

			$app	= Factory::getApplication();
			$db 	= Factory::getDBO();

			$notDeleteItems = array();

			foreach($data as $k => $v) {
				if (!isset($v['points_received']) || (isset($v['points_received']) && $v['points_received'] == '')) {
					// points_received is not 0, points_received is empty
					// We can set points_received to zero so we need to differentiate between zero and not set
					continue;
				}

				//$row = JTable::getInstance('PhocacartProductPointGroup', 'Table', array());
				$idExists = 0;
				if(isset($v['product_id']) && $v['product_id'] != '' && isset($v['group_id']) && $v['group_id'] != '') {

					$query = ' SELECT id'
					.' FROM #__phocacart_product_point_groups'
					.' WHERE product_id = '. (int)$v['product_id']
					.' AND group_id = '. (int)$v['group_id']
					.' ORDER BY id';
					$db->setQuery($query);
					$idExists = $db->loadResult();

				}

				if ((int)$idExists > 0) {

					$query = 'UPDATE #__phocacart_product_point_groups SET'
					.' product_id = '.(int)$v['product_id'].','
					.' group_id = '. (int)$v['group_id'].','
					.' points_received = '.$db->quote($v['points_received'])
					.' WHERE id = '.(int)$idExists;
					$db->setQuery($query);
					$db->execute();
					$newIdD = $idExists;

				} else {

					if (isset($v['id']) && (int)$v['id'] > 0) {
						// IMPORT
						$values 	= '('.(int)$v['id'].', '.(int)$v['product_id'].', '.(int)$v['group_id'].', '.$db->quote($v['points_received']).')';
						$query = ' INSERT INTO #__phocacart_product_point_groups (id, product_id, group_id, points_received) VALUES '.$values;
						$db->setQuery($query);

						$db->execute();
						$newIdD = (int)$v['id'];

					} else {
						// NEW ITEM
						$values 	= '('.(int)$v['product_id'].', '.(int)$v['group_id'].', '.$db->quote($v['points_received']).')';
						$query = ' INSERT INTO #__phocacart_product_point_groups (product_id, group_id, points_received) VALUES '.$values;
						$db->setQuery($query);
						$db->execute();
						$newIdD = $db->insertid();
					}
				}


				if (isset($newIdD) && (int)$newIdD > 0) {
					$notDeleteItems[] = (int)$newIdD;
				}

			}

			if (!empty($notDeleteItems)) {
				$notDeleteItemsString = implode(',', $notDeleteItems);
				$query = ' DELETE '
						.' FROM #__phocacart_product_point_groups'
						.' WHERE product_id = '. (int)$productId
						.' AND id NOT IN ('.$notDeleteItemsString.')';

			} else {
				$query = ' DELETE '
						.' FROM #__phocacart_product_point_groups'
						.' WHERE product_id = '. (int)$productId;
			}

			$db->setQuery($query);
			$db->execute();

		}

		return true;
	}


	/* We store price for default group - because we need to compare group prices in one sql query
	 * When customer is assigned to more groups select the lowest price - lowest group for this customer
	 * Example: group A = 500, group B = 400 - customer is assigned to both groups so he gets the lowest price for group B
	 * When we store the product, we need to update the default price in phocacart_product_price_groups table
	 * because it cannot be manually set, it is set automatically by price of product
	 * it is universal function but as default: group = 1 which is default group
	 */

	public static function updateGroupProductPriceById($productId, $price, $groupId = 1) {

		$db 	= Factory::getDBO();

		$query = 'UPDATE #__phocacart_product_price_groups SET'
		.' price = '.$db->quote($price)
		.' WHERE product_id = '.(int)$productId
		.' AND group_id = '.(int)$groupId;
		$db->setQuery($query);
		$db->execute();
	}

	public static function updateGroupProductRewardPointsById($productId, $pointsReceived, $groupId = 1) {

		$db 	= Factory::getDBO();

		$query = 'UPDATE #__phocacart_product_point_groups SET'
		.' points_received = '.(int)$pointsReceived
		.' WHERE product_id = '.(int)$productId
		.' AND group_id = '.(int)$groupId;

		$db->setQuery($query);
		$db->execute();
	}

	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
}
?>
