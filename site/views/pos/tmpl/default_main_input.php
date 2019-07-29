<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

// USER
echo '<form class="form-inline" action="'.$this->t['action'].'" method="post">';
echo '<input type="hidden" name="format" value="raw">';
echo '<input type="hidden" name="page" value="main.content.customers">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<input type="hidden" name="ticketid" value="'.(int)$this->t['ticket']->id.'" />';
echo '<input type="hidden" name="unitid" value="'.(int)$this->t['unit']->id.'" />';
echo '<input type="hidden" name="sectionid" value="'.(int)$this->t['section']->id.'" />';
//echo '<input type="hidden" name="limitstart" value="0" />';//We use more pages, reset for new date, new customer, new products
//echo '<input type="hidden" name="start" value="0" />';
echo JHtml::_('form.token');

if ($this->t['userexists']) {
	echo '<button class="'.$this->s['c']['btn.btn-primary'].' loadMainContent"><span class="'.$this->s['i']['user'].' icon-white"></span>  &nbsp;'.$this->t['user']->name.'</button>';
} else if ($this->t['anonymoususerexists']) {
	echo '<button class="'.$this->s['c']['btn.btn-primary'].' loadMainContent"><span class="'.$this->s['i']['user'].' icon-white"></span>  &nbsp;'.$this->t['loyalty_card_number'].'</button>';
} else {
	echo '<button class="'.$this->s['c']['btn.btn-primary'].' loadMainContent"><span class="'.$this->s['i']['user'].' icon-white"></span> &nbsp;'.JText::_('COM_PHOCACART_SELECT_CUSTOMER').'</button>';
}

echo '</form>';


// SHIPPING Method
$title = JText::_('COM_PHOCACART_SELECT_SHIPPING_METHOD');
if ($this->t['shippingmethodexists']) {
	$shipping = $this->cart->getShippingMethod();
	if (isset($shipping['title']) && $shipping['title'] != '') {
		$title = $shipping['title'];
	}
}

if ($this->t['pos_shipping_force'] == 0) {

	echo '<form class="form-inline" action="'.$this->t['action'].'" method="post">';
	echo '<input type="hidden" name="format" value="raw">';
	echo '<input type="hidden" name="page" value="main.content.shippingmethods">';
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />';
	echo '<input type="hidden" name="ticketid" value="'.(int)$this->t['ticket']->id.'" />';
	echo '<input type="hidden" name="unitid" value="'.(int)$this->t['unit']->id.'" />';
	echo '<input type="hidden" name="sectionid" value="'.(int)$this->t['section']->id.'" />';
	echo JHtml::_('form.token');
	echo '<button class="'.$this->s['c']['btn.btn-primary'].' loadMainContent"><span class="'.$this->s['i']['shipping-method'].' icon-white"></span> &nbsp;'.$title.'</button>';
	echo '</form>';
} else {
	echo '<div><button class="'.$this->s['c']['btn.btn-primary'].'"><span class="'.$this->s['i']['shipping-method'].' icon-white"></span> &nbsp;'.$title.'</button></div>';
}


// PAYMENT Method
$title = JText::_('COM_PHOCACART_SELECT_PAYMENT_METHOD');
if ($this->t['paymentmethodexists']) {
	$payment = $this->cart->getPaymentMethod();
	if (isset($payment['title']) && $payment['title'] != '') {
		$title = $payment['title'];
	}
}

if ($this->t['pos_payment_force'] == 0) {
	echo '<form class="form-inline" action="'.$this->t['action'].'" method="post">';
	echo '<input type="hidden" name="format" value="raw">';
	echo '<input type="hidden" name="page" value="main.content.paymentmethods">';
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />';
	echo '<input type="hidden" name="ticketid" value="'.(int)$this->t['ticket']->id.'" />';
	echo '<input type="hidden" name="unitid" value="'.(int)$this->t['unit']->id.'" />';
	echo '<input type="hidden" name="sectionid" value="'.(int)$this->t['section']->id.'" />';
	echo JHtml::_('form.token');
	echo '<button class="'.$this->s['c']['btn.btn-primary'].' loadMainContent"><span class="'.$this->s['i']['payment-method'].' icon-white"></span> &nbsp;'.$title.'</button>';
	echo '</form>';
} else {
	echo '<div><button class="'.$this->s['c']['btn.btn-primary'].'"><span class="'.$this->s['i']['payment-method'].' icon-white"></span> &nbsp;'.$title.'</button></div>';
}


// PAYMENT
echo '<form class="form-inline" action="'.$this->t['action'].'" method="post">';
echo '<input type="hidden" name="format" value="raw">';
echo '<input type="hidden" name="page" value="main.content.payment">';
echo '<input type="hidden" name="tmpl" value="component" />';
echo '<input type="hidden" name="option" value="com_phocacart" />';
echo '<input type="hidden" name="ticketid" value="'.(int)$this->t['ticket']->id.'" />';
echo '<input type="hidden" name="unitid" value="'.(int)$this->t['unit']->id.'" />';
echo '<input type="hidden" name="sectionid" value="'.(int)$this->t['section']->id.'" />';
echo JHtml::_('form.token');

//if (!$this->t['paymentexists']) {
echo '<button class="'.$this->s['c']['btn.btn-success.btn-lg'].' loadMainContent">'.JText::_('COM_PHOCACART_PAYMENT').'</button>';
//} else {
//	echo '<button class="btn btn-success btn-lg loadMainContent">'.$this->t['user']->name.'</button>';
//}
echo '</form>';
?>
