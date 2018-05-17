<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
echo '<div id="ph-pc-checkout-box" class="pc-checkout-view'.$this->p->get( 'pageclass_sfx' ).'">';

echo PhocacartRenderFront::renderHeader(array(JText::_('COM_PHOCACART_CHECKOUT')));

if ( isset($this->t['checkout_desc']) && $this->t['checkout_desc'] != '') {
	// Checkout description
	echo '<div class="ph-desc">'. $this->t['checkout_desc']. '</div>';
}


echo $this->loadTemplate('cart');
echo $this->t['event']->onCheckoutAfterCart;
echo $this->loadTemplate('login');
echo $this->t['event']->onCheckoutAfterLogin;
echo $this->loadTemplate('address');
echo $this->t['event']->onCheckoutAfterAddress;
echo $this->loadTemplate('shipping');
echo $this->t['event']->onCheckoutAfterShipping;
echo $this->loadTemplate('payment');
echo $this->t['event']->onCheckoutAfterPayment;
echo $this->loadTemplate('confirm');
echo $this->t['event']->onCheckoutAfterConfirm;



echo '</div>';// end ph-pc-checkout-box
echo '<div>&nbsp;</div>';
echo PhocacartUtilsInfo::getInfo();
?>