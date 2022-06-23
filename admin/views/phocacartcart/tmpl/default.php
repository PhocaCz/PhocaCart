<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Cart
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

// All is solved in the rendercart and cart (include the asked userid)
if (isset($this->item->user_id) && (int)$this->item->user_id > 0) {

	$link	= Route::_( 'index.php?option='.$this->t['o'].'&view=phocacartcart&tmpl=component&&id='.(int)$this->item->user_id);
	$cart	= new PhocacartCartRendercart();

	$cart->setType(array());// all types
	$cart->setFullItems();

	$this->t['shippingid'] 	= $cart->getShippingId();


	if (isset($this->t['shippingid']) && (int)$this->t['shippingid'] > 0 && $this->t['shippingedit'] == 0) {
		$cart->addShippingCosts($this->t['shippingid']);
		$this->t['shippingmethodexists'] = true;
	}
	$this->t['paymentid'] 	= $cart->getPaymentId();
	if (isset($this->t['paymentid']) && (int)$this->t['paymentid'] > 0 && $this->t['paymentedit'] == 0) {
		$cart->addPaymentCosts($this->t['paymentid']);// validity of payment will be checked
		$this->t['paymentmethodexists'] = true;
	}

	$cart->roundTotalAmount();

	$cart->getItems();
	$total                         = $cart->getTotal();


	echo '<div class="ph-cart-info-box">';
	echo $cart->render();
	echo '</div>';

	echo '<div class="ph-last-cart-activity">'.Text::_('COM_PHOCACART_LAST_CART_ACTIVITY').': '.$this->item->date.'</div>';
	$userName = $this->item->user_name;
	if (isset($this->item->user_username)) {
		$userName .= ' <small>('.$this->item->user_username.')</small>';
	}
	echo '<div class="ph-cart-info-user">'.Text::_('COM_PHOCACART_USER').': '.$userName.'</div>';

	echo '<form action="'.$link.'" method="post">';
	echo '<input type="hidden" name="userid" value="'.(int)$this->item->user_id.'">';
	echo '<input type="hidden" name="vendorid" value="'.(int)$this->item->vendor_id.'">';
	echo '<input type="hidden" name="ticketid" value="'.(int)$this->item->ticket_id.'">';
	echo '<input type="hidden" name="unitid" value="'.(int)$this->item->unit_id.'">';
	echo '<input type="hidden" name="sectionid" value="'.(int)$this->item->section_id.'">';
	echo '<input type="hidden" name="task" value="phocacartcart.emptycart">';
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />';
	echo '<button class="btn btn-primary btn-sm ph-btn"><span class="icon-delete"></span> '.Text::_('COM_PHOCACART_EMPTY_USER_CART').'</button>';
	echo '</div>';
	echo HTMLHelper::_('form.token');
	echo '</form>';
} else {
	echo '<div class="ph-cart-info-user">'.Text::_('COM_PHOCACART_NO_ACTIVE_CART_FOR_THIS_USER').'</div>';
}


?>
