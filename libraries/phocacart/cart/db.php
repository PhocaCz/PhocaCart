<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @form Phoca form
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
 
class PhocacartCartDb
{
	private static $cart = false;
	
	private function __construct(){}
	
	public static function getCartDb($userId) {
		
		if(self::$cart === false){
		
			$db 	= JFactory::getDBO();
			$query = ' SELECT c.cart, c.shipping, c.payment, c.coupon, c.reward,'
					.' s.title as shippingtitle, p.title as paymenttitle, p.method as paymentmethod,'
					.' co.title as coupontitle, co.code as couponcode'
					.' FROM #__phocacart_cart AS c'
					.' LEFT JOIN #__phocacart_shipping_methods AS s ON c.shipping = s.id'
					.' LEFT JOIN #__phocacart_payment_methods AS p ON c.payment = p.id'
					.' LEFT JOIN #__phocacart_coupons AS co ON c.coupon = co.id'
					.' WHERE c.user_id = '.(int)$userId
					.' ORDER BY c.cart';
			$db->setQuery($query);
			$cartDb = $db->loadAssoc();
			
			
			if (!empty($cartDb) && isset($cartDb['cart']) && $cartDb['cart'] != '') {
				$cartDb['cart'] = unserialize($cartDb['cart']);
				self::$cart = $cartDb;
			} else {
				$cartDb['cart'] 		= array();
				$cartDb['shipping'] 	= 0;
				$cartDb['payment'] 		= 0;
				$cartDb['coupon'] 		= 0;
				$cartDb['discount'] 	= array();
				$cartDb['paymenttitle'] = '';
				$cartDb['paymentmethod']= '';
				$cartDb['coupontitle'] 	= '';
				$cartDb['couponcode'] 	= '';
				$cartDb['reward']		= '';
				self::$cart = $cartDb;
			}
			
		}
		return self::$cart;
	}
	
	public static function emptyCartDb($userId) {
		self::$cart =  false;
		$db 	= JFactory::getDBO();
		$query = ' DELETE FROM #__phocacart_cart'
				.' WHERE user_id = '.(int)$userId;
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