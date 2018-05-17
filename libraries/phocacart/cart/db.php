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
defined( '_JEXEC' ) or die( 'Restricted access' );
 
class PhocacartCartDb
{
	private static $cart = array();
	
	private function __construct(){}
	
	public static function clearCartDbVariable($userId, $vendorId = 0, $ticketId = 0, $unitId = 0, $sectionId = 0) {
		unset(self::$cart[$userId][$vendorId][$ticketId][$unitId][$sectionId]);
	}
	
	public static function getCartDb($userId, $vendorId = 0, $ticketId = 0, $unitId = 0, $sectionId = 0) {
		
		if(!isset(self::$cart[$userId][$vendorId][$ticketId][$unitId][$sectionId])){
			
			$db 	= JFactory::getDBO();
			$query = ' SELECT c.cart, c.shipping, c.payment, c.coupon, c.reward, c.loyalty_card_number,'
					.' s.title as shippingtitle, p.title as paymenttitle, p.method as paymentmethod,'
					.' co.title as coupontitle, co.code as couponcode'
					.' FROM #__phocacart_cart_multiple AS c'
					.' LEFT JOIN #__phocacart_shipping_methods AS s ON c.shipping = s.id'
					.' LEFT JOIN #__phocacart_payment_methods AS p ON c.payment = p.id'
					.' LEFT JOIN #__phocacart_coupons AS co ON c.coupon = co.id'
					.' WHERE c.user_id = '.(int)$userId
					.' AND c.vendor_id = '.(int)$vendorId
					.' AND c.ticket_id = '.(int)$ticketId
					.' AND c.unit_id = '.(int)$unitId
					.' AND c.section_id = '.(int)$sectionId
					.' ORDER BY c.cart';
			$db->setQuery($query);
			
			$cartDb = $db->loadAssoc();
		
			
			if (!empty($cartDb) && isset($cartDb['cart']) && $cartDb['cart'] != '') {
				$cartDb['cart'] = unserialize($cartDb['cart']);
				self::$cart[$userId][$vendorId][$ticketId][$unitId][$sectionId] = $cartDb;
			} else {
				
				$pos_payment_force = 0;
				$pos_shipping_force = 0;
				if (PhocacartPos::isPos()) {
					$app					= JFactory::getApplication();
					$paramsC 				= PhocacartUtils::getComponentParameters();
					$pos_payment_force	= $paramsC->get( 'pos_payment_force', 0 );
					$pos_shipping_force	= $paramsC->get( 'pos_shipping_force', 0 );
				}
				
				$cartDb['cart'] 				= array();
				$cartDb['shipping'] 			= $pos_shipping_force;
				$cartDb['payment'] 				= $pos_payment_force;
				$cartDb['coupon'] 				= 0;
				$cartDb['discount'] 			= array();
				$cartDb['paymenttitle'] 		= '';
				$cartDb['paymentmethod']		= '';
				$cartDb['coupontitle'] 			= '';
				$cartDb['couponcode'] 			= '';
				$cartDb['reward']				= '';
				$cartDb['loyalty_card_number']	= '';
				self::$cart[$userId][$vendorId][$ticketId][$unitId][$sectionId] = $cartDb;
			}
			
		}
		return self::$cart[$userId][$vendorId][$ticketId][$unitId][$sectionId];
	}
	
	public static function emptyCartDb($userId, $vendorId = 0, $ticketId = 0, $unitId = 0, $sectionId = 0) {
		self::$cart =  false;
		$db 	= JFactory::getDBO();
		$query = ' DELETE FROM #__phocacart_cart_multiple'
				.' WHERE user_id = '.(int)$userId
				.' AND vendor_id = '.(int)$vendorId
				.' AND ticket_id = '.(int)$ticketId
				.' AND unit_id = '.(int)$unitId
				.' AND section_id = '.(int)$sectionId;
		$db->setQuery($query);
		$db->execute();
		return true;
	}
	
	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
}
?>