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

class PhocacartShipping
{

	public function __construct() {
		
	}
	
	public function getPossibleShippingMethods($amountNetto, $amountBrutto, $quantity, $country, $region, $weight, $maxLength, $maxWidth, $maxHeight, $id = 0, $selected = 0) {
		
		$app			= JFactory::getApplication();
		$paramsC 		= PhocacartUtils::getComponentParameters();
		$shipping_amount_rule	= $paramsC->get( 'shipping_amount_rule', 0 );
		
		$user 			= JFactory::getUser();
		$userLevels		= implode (',', $user->getAuthorisedViewLevels());
		$userGroups 	= implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
		
		$db 			= JFactory::getDBO();
		
		$wheres	  		= array();
		// ACCESS
		$wheres[] = " s.published = 1";
		$wheres[] = " s.access IN (".$userLevels.")";
		$wheres[] = " (ga.group_id IN (".$userGroups.") OR ga.group_id IS NULL)";
		
		if ((int)$id > 0) {
			$wheres[] =  's.id = '.(int)$id;
			$limit = ' LIMIT 1';
			//$group = '';
		} else {
			$limit = '';
			
		}
		
		$group = ' GROUP BY s.id, s.tax_id, s.cost, s.calculation_type, s.title, s.description, s.image, s.access,'
				.' s.active_amount, s.active_quantity, s.active_zone, s.active_country, s.active_region,'
				.' s.active_weight, s.active_size,'
				.' s.lowest_amount, s.highest_amount, s.minimal_quantity, s.maximal_quantity, s.lowest_weight,'
				.' s.highest_weight, s.default, s.maximal_length, s.maximal_width, s.maximal_height,'
				.' t.id, t.title, t.tax_rate, t.calculation_type';
		
		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );
		
		$query = ' SELECT s.id, s.tax_id, s.cost, s.calculation_type, s.title, s.description, s.image, s.access,'
				.' s.active_amount, s.active_quantity, s.active_zone, s.active_country, s.active_region,'
				.' s.active_weight, s.active_size,'
				.' s.lowest_amount, s.highest_amount, s.minimal_quantity, s.maximal_quantity, s.lowest_weight,'
				.' s.highest_weight, s.default, s.maximal_length, s.maximal_width, s.maximal_height,'
				.' t.id as taxid, t.title as taxtitle, t.tax_rate as taxrate, t.calculation_type as taxcalculationtype,'
				.' GROUP_CONCAT(DISTINCT r.region_id) AS region,'
				.' GROUP_CONCAT(DISTINCT c.country_id) AS country,'
				.' GROUP_CONCAT(DISTINCT z.zone_id) AS zone'
				.' FROM #__phocacart_shipping_methods AS s'
				.' LEFT JOIN #__phocacart_shipping_method_regions AS r ON r.shipping_id = s.id'
				.' LEFT JOIN #__phocacart_shipping_method_countries AS c ON c.shipping_id = s.id'
				.' LEFT JOIN #__phocacart_shipping_method_zones AS z ON z.shipping_id = s.id'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = s.tax_id'
				.' LEFT JOIN #__phocacart_item_groups AS ga ON s.id = ga.item_id AND ga.type = 7'// type 8 is payment
				. $where
				. $group
				. $limit;
		
		PhocacartUtils::setConcatCharCount();
		$db->setQuery($query);
		
		$shippings = $db->loadObjectList();
		
		
		if (!empty($shippings) && !isset($shippings[0]->id) || (isset($shippings[0]->id) && (int)$shippings[0]->id < 1)) {
			return false;
		}


		$i = 0;
		if (!empty($shippings)) {
			foreach($shippings as $k => $v) {
				
				
				$v->active = 0;
				$v->selected = 0;
				$a = 0;
				$q = 0;
				$z = 0;
				$c = 0;
				$r = 0;
				$w = 0;
				$s = 0;
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
				
				// Quantity Rule
				if($v->active_quantity == 1) {
					if ($quantity > $v->minimal_quantity && $quantity < $v->maximal_quantity) {
						$q = 1;
					}
				} else {
					$q = 1;
				}
				
				// Zone Rule
				if($v->active_zone == 1) {
					if (isset($v->zone) && $v->zone != '')  {
						$zones = explode(',', $v->zone);
						if (PhocacartZone::isCountryOrRegionIncluded($zones, (int)$country, (int)$region)) {
							$z = 1;
						}
					}
				
				} else {
					$z = 1;
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
				
				// Size Rule
				if($v->active_size == 1) {
					$sP = 0;
					if ($maxLength < $v->maximal_length || $maxLength == $v->maximal_length) {
						$sP++;
					}
					if ($maxWidth < $v->maximal_width || $maxWidth == $v->maximal_width) {
						$sP++;
					}
					if ($maxHeight < $v->maximal_height || $maxHeight == $v->maximal_height) {
						$sP++;
					}
					if ($sP == 3) {
						$s = 1;
					}
				} else {
					$s = 1;
				}
				
				
				// No rule was set for shipping, it will be displayed at all events
				if($v->active_amount == 0 && $v->active_quantity == 0 && $v->active_country == 0 && $v->active_region == 0 && $v->active_weight == 0) {
					$v->active = 1;
				}
				
				// if some of the rules is not valid, all the payment is NOT valid
				if ($a == 0 || $q == 0 || $z == 0 || $c == 0 || $r == 0 || $w == 0 || $s == 0) {
					$v->active = 0;
				} else {
					$v->active = 1;
				}
				
				if ($v->active == 0) {
					if (isset($shippings[$i])) {
						unset($shippings[$i]);
					}
				}
				
				// Try to set default for frontend form
				// If user selected some shipping, such will be set as default
				// If not they the default will be set
				if ((int)$selected > 0) {
					if ((int)$v->id == (int)$selected) {
						$v->selected = 1;
					}
				} else {
					$v->selected = $v->default;
				}
				
				
				$i++;
			}
		
		}
		
		return $shippings;
		
	}
	
	public function getShippingMethod($shippingId) {
		
		//$app			= JFactory::getApplication();
		//$paramsC 		= PhocacartUtils::getComponentParameters();
		//$shipping_amount_rule	= $paramsC->get( 'shipping_amount_rule', 0 );
		
		$db = JFactory::getDBO();
		
		$query = ' SELECT s.id, s.tax_id, s.cost, s.calculation_type, s.title, s.description, s.image,'
				.' t.id as taxid, t.title as taxtitle, t.tax_rate as taxrate, t.calculation_type as taxcalculationtype'
				.' FROM #__phocacart_shipping_methods AS s'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = s.tax_id'
				.' WHERE s.id = '.(int)$shippingId
				.' ORDER BY s.id'
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
	
	/*
	 * Get all PCS Plugins
	 */
	public static function getShippingPluginMethods($namePlugin = '') {
		
		$db 	= JFactory::getDBO();
		$lang	= JFactory::getLanguage();
		$client	= JApplicationHelper::getClientInfo(0);
		$query = 'SELECT a.extension_id , a.name, a.element, a.folder'
				.' FROM #__extensions AS a'
				.' WHERE a.type = '.$db->quote('plugin')
				.' AND a.enabled = 1'
				.' AND a.folder = ' . $db->quote('pcs');
		
		if ($namePlugin != '') {
			$query .= 'AND a.element = '. $db->quote($name);
		}

		$query .= ' ORDER BY a.ordering';
		$db->setQuery($query);
		$plugins = $db->loadObjectList();
		
		
		if ($namePlugin == '') {
			$i 		= 0;
			$p[0]['text'] 	= '- ' .JText::_('COM_PHOCACART_SELECT_SHIPPING_METHOD').' -';
			$p[0]['value'] 	= '';
		} else {
			$i 		= -1;
		}
		if (!empty($plugins)) {
			foreach($plugins as $k => $v) {
				
				// Load the core and/or local language file(s).
				$folder 	= 'pcs';
				$element	= $v->element;
			$lang->load('plg_'.$folder.'_'.$element, JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, $client->path.'/plugins/'.$folder.'/'.$element, null, false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, $client->path.'/plugins/'.$folder.'/'.$element, $lang->getDefault(), false, false);
				
				$i++;
					
				$name = JText::_(strtoupper($v->name) );
				$name = str_replace('Plugin', '', $name);
				$name = str_replace('Phoca Cart Shipping -', '', $name);
				
				$p[$i]['text'] = JText::_($name);
				$p[$i]['value'] = $v->element;
			}
		
		}
		
		if ($namePlugin != '' && !empty($p[0])) {
			return $p[0];
		}
		
		return $p;

	}
}
?>