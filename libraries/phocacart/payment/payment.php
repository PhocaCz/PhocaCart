<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

/*
 * Payment Method - is the method stored in Phoca Cart
 * Method - is the method of payment - e.g. Paypal, Cash On Delivery, ...
 *
 * EXAMPLE:
 * Payment Method: Cash to 100eur -> Method: Cash On Delivery
 * Payment Method: Cash over 100eur -> Method: Cash On Delivery 
 * Payment Method: Paypal to 100eur -> Method: Paypal Standard
 * Payment Method: Paypal over 100eur -> Method: Paypal Standard
 * 
 */

class PhocaCartPayment
{
	public function __construct() {
		
	}
	
	public function getPossiblePaymentMethods($amountNetto, $amountBrutto, $country, $region, $shipping, $id = 0) {
		
		$paramsC 				= JComponentHelper::getParams('com_phocacart');
		$payment_amount_rule	= $paramsC->get( 'payment_amount_rule', 0 );
		
		$user 					= JFactory::getUser();
		$userLevels				= implode (',', $user->getAuthorisedViewLevels());
		
		$db = JFactory::getDBO();
		
		// ACCESS
		$accessWhere = " AND p.access IN (".$userLevels.")";
		
		/*$query = ' SELECT p.id, p.title, p.image'
				.' FROM #__phocacart_payment_methods AS p'
				.' WHERE p.published = 1'
				.' ORDER BY p.ordering';
		$db->setQuery($query);*/
		
		$query = ' SELECT p.id, p.tax_id, p.cost, p.title, p.image, p.access, p.description,'
				.' p.active_amount, p.active_country, p.active_region, p.active_shipping,'
				.' p.lowest_amount, p.highest_amount,'
				.' t.title as taxtitle, t.tax_rate as taxrate, t.calculation_type as taxcalctype,'
				.' GROUP_CONCAT(DISTINCT r.region_id) AS region,'
				.' GROUP_CONCAT(DISTINCT c.country_id) AS country,'
				.' GROUP_CONCAT(DISTINCT s.shipping_id) AS shipping'
				.' FROM #__phocacart_payment_methods AS p'
				.' LEFT JOIN #__phocacart_payment_method_regions AS r ON r.payment_id = p.id'
				.' LEFT JOIN #__phocacart_payment_method_countries AS c ON c.payment_id = p.id'
				.' LEFT JOIN #__phocacart_payment_method_shipping AS s ON s.payment_id = p.id'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = p.tax_id'
				.' WHERE p.published = 1'
				. $accessWhere;
		if ((int)$id > 0) {
			$query .= ' AND p.id = '.(int)$id
			       .= ' LIMIT 1';
		} else {
			$query .= ' GROUP BY p.id';
		}
		$db->setQuery($query);

		$payments = $db->loadObjectList();
		
		if (!empty($payments) && !isset($payments[0]->id) || (isset($payments[0]->id) && (int)$payments[0]->id < 1)) {
			return false;
		}
		
		$i = 0;
		if (!empty($payments)) {
			foreach($payments as $k => $v) {
				
			
				$v->active = 0;
				$a = 0;
				$c = 0;
				$r = 0;
				$s = 0;
				
				// Amount Rule
				if($v->active_amount == 1) {
				
					
					if ($payment_amount_rule == 0 || $payment_amount_rule == 1) {
						// No tax, brutto
						if ($amountBrutto > $v->lowest_amount && $amountBrutto < $v->highest_amount) {
							$a = 1;
						}
					
					} else if ($payment_amount_rule == 2) {
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
				}  else {
					$r = 1;
				}
			
				// Shipping Rule
				if($v->active_shipping == 1) {
					if (isset($v->shipping) && $v->shipping != '') {
						$shippings = explode(',', $v->shipping);
						
						if (in_array((int)$shipping, $shippings)) {
							$s = 1;
						}
					}
				} else {
					$s = 1;
				}
			
				// No rule was set for shipping, it will be displayed at all events
				if($v->active_amount == 0 && $v->active_country == 0 && $v->active_region == 0 && $v->active_shipping == 0) {
					$v->active = 1;
				}
				
				// if some of the rules is not valid, all the payment is NOT valid
				if ($a == 0 || $c == 0 || $r == 0 || $s == 0) {
					$v->active = 0;
				} else {
					$v->active = 1;
				}
				
				if ($v->active == 0) {
					if (isset($payments[$i])) {
						unset($payments[$i]);
					}
				}
				$i++;
			}
		
		}
		
		return $payments;
		
	}
	
	public function getPaymentMethod($paymentId) {
		
		//$paramsC 				= JComponentHelper::getParams('com_phocacart');
		//$shipping_amount_rule	= $paramsC->get( 'shipping_amount_rule', 0 );
		
		$db = JFactory::getDBO();
		
		/*$query = ' SELECT p.id, p.title, p.image,'
				.' FROM #__phocacart_payment_methods AS s'
				.' WHERE p.id = '.(int)$paymentId
				.' LIMIT 1';
		$db->setQuery($query);*/
		
		$query = ' SELECT p.id, p.tax_id, p.cost, p.title, p.image, p.method, p.params, p.description, '
				.' t.title as taxtitle, t.tax_rate as taxrate, t.calculation_type as taxcalctype'
				.' FROM #__phocacart_payment_methods AS p'
				.' LEFT JOIN #__phocacart_taxes AS t ON t.id = p.tax_id'
				.' WHERE p.id = '.(int)$paymentId
				.' ORDER BY p.id'
				.' LIMIT 1';
		$db->setQuery($query);

		$payment = $db->loadObject();
		
		if (isset($payment->params)) {
		
			$registry = new JRegistry;
			$registry->loadString($payment->params);
			$payment->params = $registry;
			//$payment->paramsArray = $registry->toArray();
		}
		return $payment;
	}
	
	/*
	 * Important function - when e.g. user changes the address or change the items in cart, the payment method
	 * needs to be removed, because user can get payment advantage when he orders 10 items but after changing
	 * cart to e.g. one item, payment cannot stay the same, the same happens with countries and region
	 */
	
	public static function removePayment() {
		$db 	= JFactory::getDBO();
		$user	= JFactory::getUser();
		
		$query = 'UPDATE #__phocacart_cart SET payment = 0 WHERE user_id = '.(int)$user->id;
		$db->setQuery($query);
		
		$db->execute();
		return true;
	}
	
	/* Checkout - is there even some payment NOT is used reverse */
	public static function isPaymentNotUsed() {
	
		$db =JFactory::getDBO();

		$query = 'SELECT a.id'
				.' FROM #__phocacart_payment_methods AS a'
				.' WHERE a.published = 1'
				.' ORDER BY id LIMIT 1';
		$db->setQuery($query);
		$methods = $db->loadObjectList();
		
		if (empty($methods)) {
			return true;
		}
		return false;
	}
	
	public static function getPaymentMethods($namePlugin = '') {
		
		$db 	= JFactory::getDBO();
		$lang	= JFactory::getLanguage();
		$client	= JApplicationHelper::getClientInfo(0);
		$query = 'SELECT a.extension_id , a.name, a.element, a.folder'
				.' FROM #__extensions AS a'
				.' WHERE a.type = '.$db->quote('plugin')
				.' AND a.enabled = 1'
				.' AND a.folder = ' . $db->quote('pcp');
		
		if ($namePlugin != '') {
			$query .= 'AND a.element = '. $db->quote($name);
		}

		$query .= ' ORDER BY a.ordering';
		$db->setQuery($query);
		$plugins = $db->loadObjectList();
		
		
		if ($namePlugin == '') {
			$i 		= 0;
			$p[0]['text'] 	= '- ' .JText::_('COM_PHOCACART_SELECT_PAYMENT_METHOD').' -';
			$p[0]['value'] 	= '';
		} else {
			$i 		= -1;
		}
		if (!empty($plugins)) {
			foreach($plugins as $k => $v) {
				
				// Load the core and/or local language file(s).
				$folder 	= 'pcp';
				$element	= $v->element;
			$lang->load('plg_'.$folder.'_'.$element, JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, $client->path.'/plugins/'.$folder.'/'.$element, null, false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load('plg_'.$folder.'_'.$element, $client->path.'/plugins/'.$folder.'/'.$element, $lang->getDefault(), false, false);
				
				$i++;
					
				$name = JText::_(strtoupper($v->name) );
				$name = str_replace('Plugin', '', $name);
				$name = str_replace('Phoca Cart Payment -', '', $name);
				
				$p[$i]['text'] = JText::_($name);
				$p[$i]['value'] = $v->element;
			}
		
		}
		
		if ($namePlugin != '' && !empty($p[0])) {
			return $p[0];
		}
		
		return $p;

	}	
	
	public static function proceedToPaymentGateway($payment) {
	
		$proceed = 0;
		if (isset($payment['method'])) {
			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('pcp', htmlspecialchars($payment['method']));
			$dispatcher->trigger('PCPbeforeProceedToPayment', array(&$proceed));
		}
		return $proceed;
	
	}
}
?>