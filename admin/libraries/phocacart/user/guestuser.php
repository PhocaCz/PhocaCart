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

class PhocacartUserGuestuser
{
	public static function getGuestUser() {
		$app			= Factory::getApplication();
		$p 				= PhocacartUtils::getComponentParameters();
		$guestCheckout	= $p->get( 'guest_checkout', 0 );
		$session 		= Factory::getSession();
		$user			= PhocacartUser::getUser();

		if ($app->getName() == 'administrator') {
			return false;
		}

		if ((int)$user->id > 0 || $guestCheckout == 0) {
			self::cancelGuestUser();
			return false;
		} else {
			return $session->get('guest', false, 'phocaCart');
		}
	}

	public static function setGuestUser($guest = false) {
		$p 				= PhocacartUtils::getComponentParameters();
		$guestCheckout	= $p->get( 'guest_checkout', 0 );
		$session 		= Factory::getSession();
		if ($guest && $guestCheckout == 1) {
			$session->set('guest', true, 'phocaCart');
		} else {
			self::cancelGuestUser();
		}
		return true;
	}

	public static function storeAddress($data) {
		$session = Factory::getSession();
		if (!empty($data)) {
			$session->set('guestaddress', $data, 'phocaCart');
			return true;
		}
		return false;
	}

	public static function getAddress() {
		$session = Factory::getSession();
		$address = $session->get('guestaddress', false, 'phocaCart');
		return $address;
	}

	public static function storeShipping($shippingId) {
		$session = Factory::getSession();
		if ((int)$shippingId > 0) {
			$session->set('guestshipping', $shippingId, 'phocaCart');
			return true;
		}
		return false;
	}

	public static function storeShippingParams($shippingParams = array()) {
		$session = Factory::getSession();
		if (!empty($shippingParams)) {
			$session->set('guestshippingparams', $shippingParams, 'phocaCart');
			//$shipping = $session->get('guestshipping', false, 'phocaCart');
			return true;
		}
		return false;
	}

	public static function getShipping() {
		$session = Factory::getSession();
		$shipping = $session->get('guestshipping', false, 'phocaCart');
		return $shipping;
	}

	public static function getShippingParams() {
		$session = Factory::getSession();
		$shipping = $session->get('guestshippingparams', false, 'phocaCart');

		return $shipping;
	}

	public static function storePayment($paymentId) {
		$session = Factory::getSession();

		if ((int)$paymentId > 0) {
			$session->set('guestpayment', $paymentId, 'phocaCart');
			return true;
		}
		return false;
	}

	public static function storePaymentParams($paymentParams = array()) {
		$session = Factory::getSession();

		if (!empty($paymentParams)) {
			$session->set('guestpaymentparams', $paymentParams, 'phocaCart');
			return true;
		}
		return false;
	}

	public static function getPayment() {
		$session = Factory::getSession();
		$payment = $session->get('guestpayment', false, 'phocaCart');
		return $payment;
	}

	public static function getPaymentParams() {
		$session = Factory::getSession();
		$payment = $session->get('guestpaymentparams', false, 'phocaCart');
		return $payment;
	}

	public static function storeLoyaltyCardNumber($number) {
		$session = Factory::getSession();

		if ($number != '') {
			$session->set('guestloyaltycardnumber', $number, 'phocaCart');
			return true;
		}
		return false;
	}

	public static function getLoyaltyCardNumber() {
		$session = Factory::getSession();
		$loyaltyCardNumber = $session->get('guestloyaltycardnumber', false, 'phocaCart');
		return $loyaltyCardNumber;
	}
	public static function storeCoupon($couponId) {
		$session = Factory::getSession();
		if ((int)$couponId > 0) {
			$session->set('guestcoupon', $couponId, 'phocaCart');
			return true;
		} else {
			$session->set('guestcoupon', false, 'phocaCart');
			return true;
		}
		return false;
	}

	public static function getCoupon() {
		$session = Factory::getSession();
		$couponId = $session->get('guestcoupon', false, 'phocaCart');
		return $couponId;
	}

	public static function getUserAddressGuest() {
		$session = Factory::getSession();
		$address = $session->get('guestaddress', false, 'phocaCart');
		$data = array();
		$data = PhocacartUser::convertAddressTwo($address, 0);
		return $data;
	}

	public static function cancelGuestUser() {


		$session 		= Factory::getSession();
		$session->set('guest', false, 'phocaCart');
		$session->set('guestaddress', false, 'phocaCart');
		$session->set('guestshipping', false, 'phocaCart');
		$session->set('guestpayment', false, 'phocaCart');
		$session->set('guestshippingparams', false, 'phocaCart');
		$session->set('guestpaymentparams', false, 'phocaCart');
		//$session->set('guestcoupon', false, 'phocaCart');// COUPONMOVE - it is possible to use coupon even user is still not logged in or user still didn't enable guest checkout
		$session->set('guestloyaltycardnumber', false, 'phocaCart');
	}
}
?>
