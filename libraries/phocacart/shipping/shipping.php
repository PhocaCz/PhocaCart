<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartShipping
{

	public function __construct() {
		
	}
	
	public function getPossibleShippingMethods($amountNetto, $amountBrutto, $country, $region, $weight, $id = 0) {
		
		$paramsC 				= JComponentHelper::getParams('com_phocacart');
		$shipping_amount_rule	= $paramsC->get( 'shipping_amount_rule', 0 );
		
		$user 					= JFactory::getUser();
		$userLevels				= implode (',', $user->getAuthorisedViewLevels());
		
		$db = JFactory::getDBO();
		
		// ACCESS
		$accessWhere = " AND s.access IN (".$userLevels.")";
		
		$query = ' SELECT s.id, s.tax_id, s.cost, s.title, s.image, s.access,'
				.' s.active_amount, s.active_country, s.active_region, s.active_weight,'
				.' s.lowest_amount, s.highest_amount, s.lowest_weight, s.highest_weight,'
				.' t.title as taxtitle, t.tax_rate as taxrate, t.calculation_type as taxcalctype,'
				.' GROUP_CONCAT(DISTINCT r.region_id) AS region,'
				.' GROUP_CONCAT(DISTINCT c.country_id) AS country'
				.' FROM #__phocacart_shipping_methods AS s'
				.' LEFT JOIN #__phocacart_shipping_method_regions AS r ON r.shipping_id = s.id'
				.' LEFT JOIN #__phocacart_shipping_method_countries AS c ON c.shipping_id = s.id'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = s.tax_id'
				.' WHERE s.published = 1'
				. $accessWhere;
		if ((int)$id > 0) {
			$query .= ' AND s.id = '.(int)$id
			       .= ' LIMIT 1';
		} else {
			$query .= ' GROUP BY s.id';
		}
		$db->setQuery($query);

		$shippings = $db->loadObjectList();
		
		
		if (!empty($shippings) && !isset($shippings[0]->id) || (isset($shippings[0]->id) && (int)$shippings[0]->id < 1)) {
			return false;
		}
		
		$i = 0;
		if (!empty($shippings)) {
			foreach($shippings as $k => $v) {
				
				
				$v->active = 0;
				$a = 0;
				$c = 0;
				$r = 0;
				$w = 0;
				// Amount Rule
				if($v->active_amount == 1) {
				
					
					if ($shipping_amount_rule == 0 || $shipping_amount_rule == 1) {
						// No tax, brutto
						if ($amountBrutto > $v->lowest_amount && $amountBrutto < $v->highest_amount) {
							$a = 1;
						}
					
					} else if ($shipping_amount_rule == 2) {
						// Netto
						if ($amountNetto > $v->lowest_amount && $amountNetto < $v->highest_amount) {
							$a = 1;
						}
					
					}
				
				} else {
					$a = 1;
				}
				
				// Country Rule
				if($v->active_country == 1) {
					if (isset($v->country) && $v->country != '') {
						$countries = explode(',', $v->country);
						if (in_array((int)$country, $countries)) {
							$c = 1;
						}
					}
				
				} else {
					$c = 1;
				}
				
				// Region Rule
				if($v->active_region == 1) {
					if (isset($v->region) && $v->region != '') {
						$regions = explode(',', $v->region);
						if (in_array((int)$region, $regions)) {
							$r = 1;
						}
					}
				} else {
					$r = 1;
				}
				
				// Weight Rule
				if($v->active_weight == 1) {
					if (($weight > $v->lowest_weight || $weight == $v->lowest_weight)
						&& ($weight < $v->highest_weight || $weight == $v->highest_weight)) {
						$w = 1;
					}
				
				} else {
					$w = 1;
				}
			
				// No rule was set for shipping, it will be displayed at all events
				if($v->active_amount == 0 && $v->active_country == 0 && $v->active_region == 0 && $v->active_weight == 0) {
					$v->active = 1;
				}
				
				// if some of the rules is not valid, all the payment is NOT valid
				if ($a == 0 || $c == 0 || $r == 0 || $w == 0) {
					$v->active = 0;
				} else {
					$v->active = 1;
				}
				
				if ($v->active == 0) {
					if (isset($shippings[$i])) {
						unset($shippings[$i]);
					}
				}
				$i++;
			}
		
		}
		
		return $shippings;
		
	}
	
	public function getShippingMethod($shippingId) {
		
		//$paramsC 				= JComponentHelper::getParams('com_phocacart');
		//$shipping_amount_rule	= $paramsC->get( 'shipping_amount_rule', 0 );
		
		$db = JFactory::getDBO();
		
		$query = ' SELECT s.id, s.tax_id, s.cost, s.title, s.image,'
				.' t.title as taxtitle, t.tax_rate as taxrate, t.calculation_type as taxcalctype'
				.' FROM #__phocacart_shipping_methods AS s'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = s.tax_id'
				.' WHERE s.id = '.(int)$shippingId
				.' LIMIT 1';
		$db->setQuery($query);

		$shipping = $db->loadObject();
		return $shipping;
	}
	
	/* Used as payment rule */
	public static function getShippingMethods($paymentId, $select = 0, $table = 'payment') {
	
		if ($table == 'payment') {
			$t = '#__phocacart_payment_method_shipping';
			$c = 'payment_id';
		}
	
		$db =JFactory::getDBO();
		
		if ($select == 1) {
			$query = 'SELECT p.shipping_id';
		} else {
			$query = 'SELECT a.*';
		}
		$query .= ' FROM #__phocacart_shipping_methods AS a'
				.' LEFT JOIN '.$t.' AS p ON a.id = p.shipping_id'
			    .' WHERE p.'.$c.' = '.(int) $paymentId;
		$db->setQuery($query);
		if ($select == 1) {
			$items = $db->loadColumn();
		} else {
			$items = $db->loadObjectList();
		}	
	
		return $items;
	}
	
	/* Used as payment rule */
	public static function getAllShippingMethodsSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id' ) {
	
		$db =JFactory::getDBO();

		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_shipping_methods AS a'
				.' ORDER BY a.'. $order;
		$db->setQuery($query);
		$methods = $db->loadObjectList();
		
		
		$methodsO = JHTML::_('select.genericlist', $methods, $name, 'class="inputbox" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);
		return $methodsO;
	}
	
	/* used as payment rule*/
	public static function storeShippingMethods($shippingsArray, $id, $table = 'payment') {
	
		if ($table == 'payment') {
			$t = '#__phocacart_payment_method_shipping';
			$c = 'payment_id';
		}
	
		if ((int)$id > 0) {
			$db =JFactory::getDBO();
			$query = ' DELETE '
					.' FROM '.$t
					. ' WHERE '.$c.' = '. (int)$id;
			$db->setQuery($query);
			$db->execute();
			
			if (!empty($shippingsArray)) {
				
				$values 		= array();
				$valuesString 	= '';
				
				foreach($shippingsArray as $k => $v) {
					$values[] = ' ('.(int)$id.', '.(int)$v[0].')';
				}
				
				if (!empty($values)) {
					$valuesString = implode($values, ',');
				
					$query = ' INSERT INTO '.$t.' ('.$c.', shipping_id)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	
	/*
	 * Important function - when e.g. user changes the address or change the items in cart, the shipping method
	 * needs to be removed, because user can get shipping advantage when he orders 10 items but after changing
	 * cart to e.g. one item, shipping cannot stay the same, the same happens with countries and region
	 */
	
	public static function removeShipping() {
		$db 	= JFactory::getDBO();
		$user	= JFactory::getUser();
		
		$query = 'UPDATE #__phocacart_cart SET shipping = 0 WHERE user_id = '.(int)$user->id;
		$db->setQuery($query);
		
		$db->execute();
		return true;
	}
	
	/* Checkout - is there even some shipping NOT is used reverse */
	public static function isShippingNotUsed() {
	
		$db =JFactory::getDBO();

		$query = 'SELECT a.id'
				.' FROM #__phocacart_shipping_methods AS a'
				.' WHERE a.published = 1'
				.' ORDER BY id LIMIT 1';
		$db->setQuery($query);
		$methods = $db->loadObjectList();
		
		if (empty($methods)) {
			return true;
		}
		return false;
	}
}
?>