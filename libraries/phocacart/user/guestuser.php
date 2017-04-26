<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocacartUserGuestuser
{
	public static function getGuestUser() {
		$p 				= JComponentHelper::getParams('com_phocacart') ;
		$guestCheckout	= $p->get( 'guest_checkout', 0 );
		$session 		= JFactory::getSession();
		$user			= JFactory::getUser();
		if ((int)$user->id > 0 || $guestCheckout == 0) {
			self::cancelGuestUser();
		} else {
			return $session->get('guest', false, 'phocaCart');
		}
	}
	
	public static function setGuestUser($guest = false) {
		$p 				= JComponentHelper::getParams('com_phocacart') ;
		$guestCheckout	= $p->get( 'guest_checkout', 0 );
		$session 		= JFactory::getSession();
		if ($guest && $guestCheckout == 1) {
			$session->set('guest', true, 'phocaCart');
		} else {
			self::cancelGuestUser();
		}
		return true;
	}
	
	public static function storeAddress($data) {
		$session = JFactory::getSession();
		if (!empty($data)) {
			$session->set('guestaddress', $data, 'phocaCart');
			return true;
		}
		return false;
	}
	
	public static function getAddress() {
		$session = JFactory::getSession();
		$address = $session->get('guestaddress', false, 'phocaCart');
		return $address;
	}
	
	public static function storeShipping($shippingId) {
		$session = JFactory::getSession();
		if ((int)$shippingId > 0) {
			$session->set('guestshipping', $shippingId, 'phocaCart');
			return true;
		}
		return false;
	}
	
	public static function getShipping() {
		$session = JFactory::getSession();
		$shipping = $session->get('guestshipping', false, 'phocaCart');
		return $shipping;
	}
	
	public static function storePayment($paymentId) {
		$session = JFactory::getSession();
	
		if ((int)$paymentId > 0) {
			$session->set('guestpayment', $paymentId, 'phocaCart');
			return true;
		}
		return false;
	}
	
	public static function getPayment() {
		$session = JFactory::getSession();
		$payment = $session->get('guestpayment', false, 'phocaCart');
		return $payment;
	}
	public static function storeCoupon($couponId) {
		$session = JFactory::getSession();
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
		$session = JFactory::getSession();
		$couponId = $session->get('guestcoupon', false, 'phocaCart');
		return $couponId;
	}
	
	public static function getUserAddressGuest() {
		$session = JFactory::getSession();
		$address = $session->get('guestaddress', false, 'phocaCart');
		$data = array();
		$data = PhocacartUser::convertAddressTwo($address, 0);
		return $data;
	}

	public static function cancelGuestUser() {
		$session 		= JFactory::getSession();
		$session->set('guest', false, 'phocaCart');
		$session->set('guestaddress', false, 'phocaCart');
		$session->set('guestshipping', false, 'phocaCart');
		$session->set('guestpayment', false, 'phocaCart');
		$session->set('guestcoupon', false, 'phocaCart');
	}
}	
?>