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


use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;

class PhocacartOrderView
{
	public function __construct() {}

	public function getItemBaS($orderId, $returnArray = 0) {

		/*$db				= Factory::getDBO();
		$config['dbo'] 	= $db;
		Table::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
		$table	= Table::getInstance('PhocacartOrderUsers', 'Table', $config);
		$tableS	= JTable::getInstance('PhocacartOrderUsers', 'Table', $config);*/


		// Billing and Shipping
		$userItems = $this->getItemUser($orderId);

		// Billing
		/*if(isset($orderId) && (int)$orderId > 0) {
			$return = $table->load(array('order_id' => (int)$orderId, 'type' => 0));
			if ($return === false && $table->getError()) {
				throw new Exception($table->getError());
				return false;
			}
		}

		// Shipping
		if(isset($orderId) && (int)$orderId > 0) {
			$returnS = $tableS->load(array('order_id' => (int)$orderId, 'type' => 1));
			if ($returnS === false && $tableS->getError()) {
				throw new Exception($tableS->getError());
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

		//$itemS 	= JArrayHelper::toObject($propertiesS, 'stdClass');
		//$item 	= JArrayHelper::toObject($properties, 'stdClass');
		$item		= new stdClass();

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

		$db = Factory::getDBO();
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

		$db = Factory::getDBO();
		$query = 'SELECT u.*,'
				.' c.title AS countrytitle, r.title AS regiontitle, c.code2 AS countrycode, c.code3 AS countrycode3'
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

		$db = Factory::getDBO();
		$query = 'SELECT o.*,'
				.' u.id AS user_id, o.vendor_id AS vendor_id, u.name AS user_name, u.username AS user_username,'
				.' p.title AS paymenttitle, p.description_info AS paymentdescriptioninfo,'
				.' s.title AS shippingtitle, s.description_info AS shippingdescriptioninfo, s.tracking_link as shippingtrackinglink, s.tracking_description as shippingtrackingdescription,'
				.' c.title AS coupontitle, cu.title AS currencytitle, cu.price_currency_symbol AS currency_symbol, d.title AS discounttitle, os.orders_view_display as ordersviewdisplay,'
				.' uv.username as vendor_username, uv.name as vendor_name,'
				.' sc.title as section_name, un.title as unit_name'
				.' FROM #__phocacart_orders AS o'
				.' LEFT JOIN #__users AS u ON u.id = o.user_id'
				.' LEFT JOIN #__users AS uv ON uv.id = o.vendor_id'
				.' LEFT JOIN #__phocacart_order_statuses AS os ON os.id = o.status_id'
				.' LEFT JOIN #__phocacart_payment_methods AS p ON p.id = o.payment_id'
				.' LEFT JOIN #__phocacart_shipping_methods AS s ON s.id = o.shipping_id'
				.' LEFT JOIN #__phocacart_coupons AS c ON c.id = o.coupon_id'
				.' LEFT JOIN #__phocacart_discounts AS d ON d.id = o.discount_id'
				.' LEFT JOIN #__phocacart_currencies AS cu ON cu.id = o.currency_id'
				.' LEFT JOIN #__phocacart_sections AS sc ON sc.id = o.section_id'
				.' LEFT JOIN #__phocacart_units AS un ON un.id = o.unit_id'
			    .' WHERE o.id = '.(int)$orderId
				.' ORDER BY o.id';
		$db->setQuery($query);
		$order = $db->loadObject();

		return $order;
	}

	public function getItemProducts($orderId) {

		$db = Factory::getDBO();
		//$query = 'SELECT DISTINCT p.*, pd.download_token, pd.download_file, pd.download_folder, pd.published as download_published, pd.type as download_type'
		//		.' FROM #__phocacart_orders AS o'
		$query = 'SELECT DISTINCT p.*'
				.' FROM #__phocacart_orders AS o'
				.' LEFT JOIN #__phocacart_order_products AS p ON o.id = p.order_id'
				.' LEFT JOIN #__phocacart_products AS pr ON pr.id = p.product_id'
		//		.' LEFT JOIN #__phocacart_order_downloads AS pd ON pd.order_product_id = p.id'
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


				if (!empty($attributes)) {

					$v->attributes = array();
					foreach($attributes as $k2 => $v2) {
						if (isset($v2->id) && $v2->id > 0) {
							$v->attributes[$k2] = $v2;
						}
					}
				}


				$downloads = $this->getItemDownloads($orderId, $v->id);

				if (!empty($downloads)) {
					$v->downloads = array();
					foreach($downloads as $k2 => $v2) {
						if (isset($v2->id) && $v2->id > 0) {
							$v->downloads[$k2] = $v2;
						}
					}
				}

			}
		}

		return $items;
	}

	public function getItemDownloads($orderId, $orderProductId) {


		$db = Factory::getDBO();
		// BE AWARE
		// productid is ID of Product Ordered not of product
		// productquantity is QUANTITY of Product Ordered not of product
		// select all files except attributes as these are selected in attributes
		$query = 'SELECT DISTINCT p.id AS productid,'
				.' pd.id, pd.download_token, pd.download_file, pd.download_folder, pd.published, pd.type'
				.' FROM #__phocacart_order_products AS p'
				.' LEFT JOIN #__phocacart_order_downloads AS pd ON p.id = pd.order_product_id AND (pd.type = 0 OR pd.type = 1 or pd.type = 2)'
			    .' WHERE p.id = '.(int)$orderProductId . ' AND p.order_id = '.(int)$orderId
				.' ORDER BY p.id';


		$db->setQuery($query);

		$items = $db->loadObjectList();

		return $items;
	}


	public function getItemAttributes($orderId, $orderProductId) {


		$db = Factory::getDBO();
		// BE AWARE
		// productid is ID of Product Ordered not of product
		// productquantity is QUANTITY of Product Ordered not of product
		//
		// Product Ordered ID is different to product because one product can have more Product Ordered IDs
		// Product 1 with attribute A ... is Product Ordered 1
		// Porduct 1 with attribute B ... is Product Ordered 2
		// Product 2 with attribute A ... is Product Ordered 3
		// (Product 1 is divided to two ordered products)
		$query = 'SELECT DISTINCT p.id AS productid, p.quantity as productquantity,'
				.' a.id, a.attribute_id, a.attribute_title, a.option_id, a.option_title, a.option_value, a.type, od.download_folder, od.download_file, od.download_token, od.published AS download_published'
				.' FROM #__phocacart_order_products AS p'
				.' LEFT JOIN #__phocacart_order_attributes AS a ON p.id = a.order_product_id'
				.' LEFT JOIN #__phocacart_order_downloads AS od ON p.id = od.order_product_id AND a.option_id = od.option_id'
			    .' WHERE p.id = '.(int)$orderProductId . ' AND p.order_id = '.(int)$orderId
				.' ORDER BY p.id';


		$db->setQuery($query);

		$items = $db->loadObjectList();

		return $items;
	}

	public function getItemProductDiscounts($orderId, $onlyPublished = 0) {

		$db = Factory::getDBO();
		$q = 'SELECT d.*'
			.' FROM #__phocacart_orders AS o'
			.' JOIN #__phocacart_order_product_discounts AS d ON o.id = d.order_id'
			.' WHERE o.id = '.(int)$orderId;
		if ($onlyPublished == 1) {
			$q.= ' AND d.published = 1';
		}
		$q.= ' ORDER BY d.id';

		$db->setQuery($q);
		$items = $db->loadObjectList();
		$itemsByKey = array();


		if (!empty($items)) {

			$oPD = array();
			$iS = 4;// specific ordering - start from 3 because 0 - 2 is taken for reward points, product discounts, cart discounts, coupon

			foreach($items as $k => $v) {

				// SPECIFIC CASE - BACKWARD COMPATIBILITY
				// Ordering 5 (reward points) -> 2 (product discount) -> 3 (cart discount) -> 4 (coupon)
				if ($v->type == 5) {
					$kS = 0;
				} else if ($v->type == 2) {
					$kS = 1;
				} else if ($v->type == 3) {
					$kS = 2;
				} else if ($v->type == 4) {
					$kS = 3;
				} else {
					$kS = $iS;
				}

				$itemsByKey[$v->product_id_key][$kS] = $v;
				$iS++;
			}

			if (!empty($itemsByKey)) {
				foreach($itemsByKey as $k => $v) {
					ksort($itemsByKey[$k]);
				}
			}
		}
		return $itemsByKey;
		//return $items;
	}

	public function getItemTotal($orderId, $onlyPublished = 0, $type = '') {

		$db = Factory::getDBO();
		$q = ' SELECT t.*'
			.' FROM #__phocacart_orders AS o'
			.' LEFT JOIN #__phocacart_order_total AS t ON o.id = t.order_id'
			.' WHERE o.id = '.(int)$orderId;
		if ($onlyPublished == 1) {
			$q.= ' AND t.published = 1';
		}
		if ($type != '') {
			$q.= ' AND t.type = '.$db->quote($type);
		}
		$q.= ' ORDER BY t.ordering';
		$db->setQuery($q);
		$items = $db->loadObjectList();
		return $items;
	}

	public function getItemTaxRecapitulation($orderId, $type = '') {

		$db = Factory::getDBO();
		$q = ' SELECT t.*, o.currency_id AS currency_id, o.currency_exchange_rate AS currency_exchange_rate'
			.' FROM #__phocacart_orders AS o'
			.' LEFT JOIN #__phocacart_order_tax_recapitulation AS t ON o.id = t.order_id'
			.' WHERE o.id = '.(int)$orderId;

		if ($type != '') {
			$q.= ' AND t.type = '.$db->quote($type);
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

	public static function getTrackingNumber($common) {
		$trackingNumber = '';

		if (isset($common->tracking_number) && $common->tracking_number != '') {
			$trackingNumber = $common->tracking_number;
		}
		return $trackingNumber;
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

	public static function getPaymentTitle($common) {
		$paymentTitle = '';
		if (isset($common->paymenttitle) && $common->paymenttitle != '') {
			$paymentTitle = $common->paymenttitle;
		}
		return $paymentTitle;
	}

	public static function getShippingDescriptionInfo($common) {
		$shippingDI = '';
		if (isset($common->shippingdescriptioninfo) && $common->shippingdescriptioninfo != '') {
			$shippingDI = $common->shippingdescriptioninfo;
		}
		return $shippingDI;
	}

	public static function getPaymentDescriptionInfo($common) {
		$paymentDI = '';
		if (isset($common->paymentdescriptioninfo) && $common->paymentdescriptioninfo != '') {
			$paymentDI = $common->paymentdescriptioninfo;
		}
		return $paymentDI;
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
