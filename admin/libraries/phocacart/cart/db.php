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
use Joomla\CMS\Factory;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocacartCartDb
{
	private static $cart = array();

	private function __construct(){}

	public static function clearCartDbVariable($userId, $vendorId = 0, $ticketId = 0, $unitId = 0, $sectionId = 0) {
		unset(self::$cart[$userId][$vendorId][$ticketId][$unitId][$sectionId]);
	}

	public static function getCartDb($userId, $vendorId = 0, $ticketId = 0, $unitId = 0, $sectionId = 0) {
		if(!isset(self::$cart[$userId][$vendorId][$ticketId][$unitId][$sectionId])) {
			$db 	= Factory::getDBO();

			/*if (I18nHelper::useI18n()) {
				$columns = 'coalesce(i18n_s.title, s.title) as shippingtitle, coalesce(i18n_p.title, p.title) as paymenttitle, coalesce(i18n_co.title, co.title) as coupontitle';
			} else {
				$columns = 's.title as shippingtitle, p.title as paymenttitle, co.title as coupontitle';
			}*/

			$columns = I18nHelper::sqlCoalesce(['title'], 's', 'shipping');
			$columns .= I18nHelper::sqlCoalesce(['title'], 'p', 'payment', '', ',');
			$columns .= I18nHelper::sqlCoalesce(['title'], 'co', 'coupon', '', ',');

			$query = ' SELECT c.cart, c.shipping, c.params_shipping, c.payment, c.params_payment, c.coupon, c.reward, c.loyalty_card_number,'
					.' s.method as shippingmethod, s.image as shippingimage, p.method as paymentmethod, p.image as paymentimage,'
					.' co.code as couponcode, ' . $columns
					.' FROM #__phocacart_cart_multiple AS c'
					.' LEFT JOIN #__phocacart_shipping_methods AS s ON c.shipping = s.id'
					.' LEFT JOIN #__phocacart_payment_methods AS p ON c.payment = p.id'
					.' LEFT JOIN #__phocacart_coupons AS co ON c.coupon = co.id'
					. I18nHelper::sqlJoin('#__phocacart_shipping_methods_i18n', 's')
					. I18nHelper::sqlJoin('#__phocacart_payment_methods_i18n', 'p')
					. I18nHelper::sqlJoin('#__phocacart_coupons_i18n', 'co')
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
					$paramsC 				= PhocacartUtils::getComponentParameters();
					$pos_payment_force	= $paramsC->get( 'pos_payment_force', 0 );
					$pos_shipping_force	= $paramsC->get( 'pos_shipping_force', 0 );
					if ((int)$pos_payment_force > 0) {
						$pos_payment_force = PhocacartPayment::isPaymentMethodActive($pos_payment_force) === true ? (int)$pos_payment_force : 0;
					}
					if ((int)$pos_shipping_force > 0) {
						$pos_shipping_force = PhocacartShipping::isShippingMethodActive($pos_shipping_force) === true ? (int)$pos_shipping_force : 0;
					}
				}

				$cartDb['cart'] 				= array();
				$cartDb['shipping'] 			= $pos_shipping_force;
				$cartDb['payment'] 				= $pos_payment_force;
				$cartDb['coupon'] 				= 0;
				$cartDb['discount'] 			= array();
                $cartDb['shippingtitle'] 		= '';
                $cartDb['shippingmethod']		= '';
                $cartDb['shippingimage']		= '';
				$cartDb['paymenttitle'] 		= '';
				$cartDb['paymentmethod']		= '';
				$cartDb['paymentimage']		    = '';
				$cartDb['coupontitle'] 			= '';
				$cartDb['couponcode'] 			= '';
				$cartDb['reward']				= '';
				$cartDb['loyalty_card_number']	= '';
				$cartDb['params_shipping']		= array();
				$cartDb['params_payment']		= array();
				self::$cart[$userId][$vendorId][$ticketId][$unitId][$sectionId] = $cartDb;
			}

		}
		return self::$cart[$userId][$vendorId][$ticketId][$unitId][$sectionId];
	}

	public static function emptyCartDb($userId, $vendorId = 0, $ticketId = 0, $unitId = 0, $sectionId = 0) {
		self::$cart =  false;
		$db 	= Factory::getDBO();
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
