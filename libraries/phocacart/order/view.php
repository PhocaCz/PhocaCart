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

class PhocacartOrderView
{
	public function __construct() {}
	
	public function getItemBaS($orderId, $returnArray = 0) {

		/*$db				= JFactory::getDBO();
		$config['dbo'] 	= $db;
		JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
		$table	= JTable::getInstance('PhocacartOrderUsers', 'Table', $config);
		$tableS	= JTable::getInstance('PhocacartOrderUsers', 'Table', $config);*/
		
		
		// Billing and Shipping
		$userItems = $this->getItemUser($orderId);
		
		// Billing
		/*if(isset($orderId) && (int)$orderId > 0) {
			$return = $table->load(array('order_id' => (int)$orderId, 'type' => 0));
			if ($return === false && $table->getError()) {
				throw new Exception($table->getErrorMsg());
				return false;
			}
		}
		
		// Shipping
		if(isset($orderId) && (int)$orderId > 0) {
			$returnS = $tableS->load(array('order_id' => (int)$orderId, 'type' => 1));
			if ($returnS === false && $tableS->getError()) {
				throw new Exception($tableS->getErrorMsg());
				return false;
			}
		}*/
		
		// Convert to the JObject before adding other data.
		$properties = array();
		if (isset($userItems[0]['type']) && $userItems[0]['type'] == 0) {
			$properties['b'] = $userItems[0];
		}
		if (isset($userItems[1]['type']) && $userItems[1]['type'] == 1) {
			$properties['s'] = $userItems[1];
		}
		
		
		/*$properties['b'] = $table->getProperties(1);
		$properties['s'] = $tableS->getProperties(1);
		*/
		/*if ($returnArray == 1) {
			
			$region = $this->getRegion($properties['b']['country'], $properties['b']['region'] );
			$properties['b']['countrytitle'] = $region['countrytitle'];
			$properties['b']['regiontitle'] = $region['regiontitle'];
			$region = $this->getRegion($properties['s']['country'], $properties['s']['region'] );
			$properties['s']['countrytitle'] = $region['countrytitle'];
			$properties['s']['regiontitle'] = $region['regiontitle'];
			return $properties;
		}*/
		
		if ($returnArray == 1) {
			return $properties;
		}
		
		//$itemS 	= JArrayHelper::toObject($propertiesS, 'JObject');
		//$item 	= JArrayHelper::toObject($properties, 'JObject');
		$item		= new JObject();//stdClass();
		
		if(!empty($properties['b']) && is_object($item)) {
			foreach($properties['b'] as $k => $v) {
				$newName = $k . '_phb';
				$item->$newName = $v;
			}
		}
		
		//Add shipping data to billing and do both data package
		if(!empty($properties['s']) && is_object($item)) {
			foreach($properties['s'] as $k => $v) {
				$newName = $k . '_phs';
				$item->$newName = $v;
			}
		}
		
		return $item;
	}
	
	/*public function getRegion($countryId, $regionId) {
		
		$db = JFactory::getDBO();
		$query = 'SELECT u.id, c.title AS countrytitle, r.title AS regiontitle'
				.' FROM #__phocacart_order_users AS u'
				.' LEFT JOIN #__phocacart_countries AS c ON c.id = u.country'
				.' LEFT JOIN #__phocacart_regions AS r ON r.id = u.region'
			    .' WHERE u.country = '.(int)$countryId. ' AND u.region = '.(int)$regionId;
		$db->setQuery($query);
		$region = $db->loadAssoc();
		return $region;
	}*/
	
	public function getItemUser($orderId) {
		
		$db = JFactory::getDBO();
		$query = 'SELECT u.*,'
				.' c.title AS countrytitle, r.title AS regiontitle'
				.' FROM #__phocacart_order_users AS u'
				.' LEFT JOIN #__phocacart_countries AS c ON c.id = u.country'
				.' LEFT JOIN #__phocacart_regions AS r ON r.id = u.region'
			    .' WHERE u.order_id = '.(int)$orderId. ' AND (u.type = 1 OR u.type = 0)'
				.' ORDER BY u.id'
				.' LIMIT 0,2';
		$db->setQuery($query);
		$userList = $db->loadAssocList();
		return $userList;
	}

	
	public function getItemCommon($orderId) {
		
		$db = JFactory::getDBO();
		$query = 'SELECT o.*,'
				.' u.id AS user_id, u.name AS user_name, u.username AS user_username, p.title AS paymenttitle,'
				.' s.title AS shippingtitle, s.tracking_link as shippingtrackinglink, s.tracking_description as shippingtrackingdescription,'
				.' c.title AS coupontitle, cu.title AS currencytitle, d.title AS discounttitle'
				.' FROM #__phocacart_orders AS o'
				.' LEFT JOIN #__users AS u ON u.id = o.user_id'
				.' LEFT JOIN #__phocacart_payment_methods AS p ON p.id = o.payment_id'
				.' LEFT JOIN #__phocacart_shipping_methods AS s ON s.id = o.shipping_id'
				.' LEFT JOIN #__phocacart_coupons AS c ON c.id = o.coupon_id'
				.' LEFT JOIN #__phocacart_discounts AS d ON d.id = o.discount_id'
				.' LEFT JOIN #__phocacart_currencies AS cu ON cu.id = o.currency_id'
			    .' WHERE o.id = '.(int)$orderId
				.' ORDER BY o.id';
		$db->setQuery($query);
		$order = $db->loadObject();
		return $order;
	}
	
	public function getItemProducts($orderId) {
		
		$db = JFactory::getDBO();
		$query = 'SELECT p.*, pr.download_token, pd.published as download_published'
				.' FROM #__phocacart_orders AS o'
				.' LEFT JOIN #__phocacart_order_products AS p ON o.id = p.order_id'
				.' LEFT JOIN #__phocacart_products AS pr ON pr.id = p.product_id'
				.' LEFT JOIN #__phocacart_order_downloads AS pd ON pd.order_product_id = p.id'
			    .' WHERE o.id = '.(int)$orderId
				.' ORDER BY p.id';
		$db->setQuery($query);
		$items = $db->loadObjectList();
		
		// BE AWARE
		// Product ID ... is an ID of product
		// OrderProduct ID ... is an ID of ordered product 
		// There is one product ID but more ordered variants of one product
		// PRODUCT 1 (Product ID = 1) can have different Attributes when ordered
		// PRODUCT 1 with attribute 1 (OrderProduct ID = 1)
		// PRODUCT 2 with attribute 2 (OrderProduct ID = 2)
		// If you order one product but you will select it with different attributes, you are ordering in fact more products
		// derivated frome the one
		
		if (!empty($items)) {
			foreach ($items as $k => $v) {
				$attributes = $this->getItemAttributes($orderId, $v->id);
				
				if (!empty($attributes) && !empty($attributes[0]->id)) {
					$v->attributes = new stdClass(); 
					$v->attributes = $attributes;
				}
			}
		}
		return $items;
	}
	
	
	public function getItemAttributes($orderId, $orderProductId) {
		
		$db = JFactory::getDBO();
		// BE AWARE
		// productid is ID of Product Ordered not of product
		// productquantity is QUANTITY of Product Ordered not of product
		// 
		// Product Ordered ID is different to product because one product can have more Product Ordered IDs
		// Product 1 with attribute A ... is Product Ordered 1
		// Porduct 1 with attribute B ... is Product Ordered 2
		// Product 2 with attribute A ... is Product Ordered 3
		// (Product 1 is divided to two ordered products)
		$query = 'SELECT p.id AS productid, p.quantity as productquantity,'
				.' a.id, a.attribute_id, a.attribute_title, a.option_id, a.option_title'
				.' FROM #__phocacart_order_products AS p'
				.' LEFT JOIN #__phocacart_order_attributes AS a ON p.id = a.order_product_id'
			    .' WHERE p.id = '.(int)$orderProductId . ' AND p.order_id = '.(int)$orderId
				.' ORDER BY p.id';
		$db->setQuery($query);
		
		$items = $db->loadObjectList();
		return $items;
	}
	
	public function getItemProductDiscounts($orderId, $onlyPublished = 0) {
		
		$db = JFactory::getDBO();
		$q = 'SELECT d.*'
			.' FROM #__phocacart_orders AS o'
			.' LEFT JOIN #__phocacart_order_product_discounts AS d ON o.id = d.order_id'
			.' WHERE o.id = '.(int)$orderId;
		if ($onlyPublished == 1) {
			$q.= ' AND d.published = 1';
		}
		$q.= ' ORDER BY d.id, d.type';
		
		$db->setQuery($q);
		$items = $db->loadObjectList();
		$itemsByKey = array();
		if (!empty($items)) {
			foreach($items as $k => $v) {
				$itemsByKey[$v->product_id_key][$k] = $v;
			}
		}
		return $itemsByKey;
		//return $items;
	}
	
	public function getItemTotal($orderId, $onlyPublished = 0) {
		
		$db = JFactory::getDBO();
		$q = ' SELECT t.*'
			.' FROM #__phocacart_orders AS o'
			.' LEFT JOIN #__phocacart_order_total AS t ON o.id = t.order_id'
			.' WHERE o.id = '.(int)$orderId;
		if ($onlyPublished == 1) {
			$q.= ' AND t.published = 1';
		}		
		$q.= ' ORDER BY t.ordering';
		$db->setQuery($q);
		$items = $db->loadObjectList();
		return $items;
	}
	
	
	// Tracking
	public static function getTrackingLink($common) {
		$trackingLink = '';
		
		if (isset($common->tracking_link_custom) && $common->tracking_link_custom != '') {
			$trackingLink = '<a href="'.$common->tracking_link_custom.'">'.$common->tracking_link_custom.'</a>';
		} else if (isset($common->shippingtrackinglink) && $common->shippingtrackinglink != '' && isset($common->tracking_number) && $common->tracking_number != '') {
			$trackingLink = '<a href="'.$common->shippingtrackinglink . $common->tracking_number.'">'.$common->shippingtrackinglink . $common->tracking_number.'</a>';
		}
		return $trackingLink;
	}
	
	public static function getTrackingDescription($common) {
		$trackingDescription = '';
		if (isset($common->tracking_description_custom) && $common->tracking_description_custom != '') {
			$trackingDescription = $common->tracking_description_custom;
		} else if (isset($common->shippingtrackingdescription) && $common->shippingtrackingdescription != '') {
			$trackingDescription = $common->shippingtrackingdescription;
		}
		return $trackingDescription;
		
	}
	
	public static function getShippingTitle($common) {
		$shippingTitle = '';
		if (isset($common->shippingtitle) && $common->shippingtitle != '') {
			$shippingTitle = $common->shippingtitle;
		}
		return $shippingTitle;
	}
	
	public static function getDateShipped($common) {
		$dateShipped = '';
		
		if (isset($common->tracking_date_shipped) && $common->tracking_date_shipped != '' && $common->tracking_date_shipped != '0000-00-00 00:00:00') {	
			$date 	= PhocacartUtils::date($common->tracking_date_shipped);
			$dateShipped = $date;
		}
		return $dateShipped;
	}
}

?>