<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

$classByTaxPlugin = '';
$results = Dispatcher::dispatch(new Event\Tax\StartCheckoutView('com_phocacart.checkout', $this->data));
if (!empty($results)) {
	foreach ($results as $k => $v) {
		if ($v != false && isset($v['checkout_class']) && $v['checkout_class'] != '') {
			$classByTaxPlugin = ' '. $v['checkout_class'];
		}
	}
}

echo '<div id="ph-pc-checkout-box" class="pc-view pc-checkout-view'.$this->p->get( 'pageclass_sfx' ).$this->t['tax_calculation_class'].$classByTaxPlugin.'">';

echo PhocacartRenderFront::renderHeader(array(Text::_('COM_PHOCACART_CHECKOUT')));

if ( isset($this->t['checkout_desc']) && $this->t['checkout_desc'] != '') {
	// Checkout description
	echo '<div class="ph-desc">'. $this->t['checkout_desc']. '</div>';
}



echo $this->loadTemplate('cart');
echo $this->t['event']->onCheckoutAfterCart;

// Coupon form before login
if ($this->t['enable_coupons'] > 0 && $this->t['display_apply_coupon_form'] == 2) {
	if ($this->t['enable_coupons'] == 1) {
		// Display for all
		echo $this->loadTemplate('coupon');
	} else if ($this->t['enable_coupons'] == 2 && ($this->a->login == 1 || $this->a->login == 2)){
		// Display for logged in user or guest checkout started
		echo $this->loadTemplate('coupon');
	}
}

// Reward points form before login
if ($this->t['enable_rewards'] > 0 && $this->t['display_apply_reward_points_form'] == 2) {
	echo $this->loadTemplate('rewardpoints');
}

echo $this->loadTemplate('login');
echo $this->t['event']->onCheckoutAfterLogin;

// Coupon form after login or
// Coupon form after login but user needs to be logged in or guest checkout is on
if ($this->t['enable_coupons'] > 0 && $this->t['display_apply_coupon_form'] == 3) {
	if ($this->t['enable_coupons'] == 1) {
		// Display for all
		echo $this->loadTemplate('coupon');
	} else if ($this->t['enable_coupons'] == 2 && ($this->a->login == 1 || $this->a->login == 2)){
		// Display for logged in user or guest checkout started
		echo $this->loadTemplate('coupon');
	}
}

// Reward points form after login
if ($this->t['enable_rewards'] > 0 && $this->t['display_apply_reward_points_form'] == 3) {
	echo $this->loadTemplate('rewardpoints');
}

echo $this->loadTemplate('address');
echo $this->t['event']->onCheckoutAfterAddress;
echo $this->loadTemplate('shipping');
echo $this->t['event']->onCheckoutAfterShipping;
echo $this->loadTemplate('payment');
echo $this->t['event']->onCheckoutAfterPayment;
echo $this->loadTemplate('confirm');
echo $this->t['event']->onCheckoutAfterConfirm;



echo '</div>';// end ph-pc-checkout-box

echo '<div id="phContainer"></div>';

echo '<div>&nbsp;</div>';
echo PhocacartUtilsInfo::getInfo();
?>
