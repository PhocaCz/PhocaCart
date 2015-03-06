<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */ 
defined('_JEXEC') or die();
if ($this->a->paymentnotused == 1) {
	
	// Payment not used
	
// PAYMENT ADDED
} else if ($this->a->paymentview == 1) {
	
	// Payment is added and goes to confirm
	// ONLY DISPLAY - pamyent method was added and user don't want to edit it
	

	// Header
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header" id="phcheckoutpaymentview"><div class="pull-right"><span class="glyphicon glyphicon-ok-circle ph-checkout-icon-ok"></span></div><h3>'.$this->t['np'].'. '.JText::_('COM_PHOCACART_PAYMENT_OPTIONS').'</h3></div>';
	echo '</div>';
	
	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutAddress">';
	echo '<div class="ph-checkout-box-action">';
	echo '<div id="ph-request-message" style="display:none"></div>';
	
	echo '<div class="col-sm-12 col-md-12 ph-checkout-payment-row" id="phPaymentMethods" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_PAYMENT_METHODS').'</div>';
	
	if (isset($this->t['paymentmethod']) && $this->t['paymentmethod']['title'] != '') {
	
		echo '<div class="col-sm-8 col-md-8 ">'.$this->t['paymentmethod']['title'].'</div>';
		
		echo '<div class="col-sm-4 col-md-4 ">';
		echo '<div class="pull-right ph-checkout-payment-edit">';
		echo '<button class="btn btn-success btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-edit"></span> '.JText::_('COM_PHOCACART_EDIT_PAYMENT').'</button>';
		echo '</div>';
		echo '</div>';
	
	}
	echo '<div class="ph-cb"></div>';
	echo '</div>'."\n";// end row action
	
	echo '</div>'."\n";// end box action

	echo '<input type="hidden" name="paymentedit" value="1" />';
	//echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo JHtml::_('form.token');
	echo '</form>'. "\n";
	

// PAYMENT EDIT
} else if ($this->a->paymentedit == 1) {

	$price	= new PhocaCartPrice();
	
	// Paymnet is not added or we edit it but payment is added, we can edit
	// Header
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header" id="phcheckoutpaymentedit"><div class="pull-right"><span class="glyphicon glyphicon-remove-circle ph-checkout-icon-not-ok"></span></div><h3>'.$this->t['np'].'. '.JText::_('COM_PHOCACART_PAYMENT_OPTIONS').'</h3></div>';
	echo '</div>';
	
	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutPayment">';
	echo '<div class="ph-checkout-box-action">';
	echo '<div id="ph-request-message" style="display:none"></div>';
	echo '<div class="col-sm-12 col-md-12 ph-checkout-payment-row" id="phpaymentMethods" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_PAYMENT_METHODS').'</div>';
	
	echo '<div class="ph-checkout-payment-cost-box">';
	
	foreach($this->t['paymentmethods'] as $k => $v) {
		
		$checked = '';
		if (isset($this->t['cartitems']['payment']) && (int)$this->t['cartitems']['payment'] == (int)$v->id) {
			$checked = 'checked="checked"';
		}
		
		$priceI = $price->getPriceItems($v->cost, $v->taxrate, $v->taxcalctype, $v->taxtitle, 1);

		echo '<div class="col-sm-6 col-md-6 ">';
		echo '<div class="radio">';
		echo '<label><input type="radio" name="phpaymentopt" id="phpaymentopt'.$v->id.'" value="'.$v->id.'" '.$checked.' >'.$v->title.'</label>';
		echo '</div>';
		echo '</div>';
		
		echo '<div class="col-sm-6 col-md-6"><div class="radio">';
			
		if (isset($priceI['nettoformat']) && isset($priceI['bruttoformat']) && $priceI['nettoformat'] == $priceI['bruttoformat']) {
		
		} else if (isset($priceI['nettoformat']) && $priceI['nettoformat'] != '' && isset($priceI['nettotxt']) && $priceI['nettotxt'] != '') {
			//echo '<div class="col-sm-6 col-md-6"></div>';
			echo '<div class="col-sm-8 col-md-8">'.$priceI['nettotxt'].'</div>';
			echo '<div class="col-sm-4 col-md-4 ph-checkout-payment-netto">'.$priceI['nettoformat'].'</div>';
		}
		
		if (isset($priceI['taxformat']) && $priceI['taxformat'] != '' && isset($priceI['taxtxt']) && $priceI['taxtxt'] != '') {
			//echo '<div class="col-sm-6 col-md-6"></div>';
			echo '<div class="col-sm-8 col-md-8">'.$priceI['taxtxt'].'</div>';
			echo '<div class="col-sm-4 col-md-4 ph-checkout-payment-tax">'.$priceI['taxformat'].'</div>';
		}
		
		if (isset($priceI['bruttoformat']) && $priceI['bruttoformat'] != '' && isset($priceI['bruttotxt']) && $priceI['bruttotxt'] != '') {
			//echo '<div class="col-sm-6 col-md-6"></div>';
			echo '<div class="col-sm-8 col-md-8">'.$priceI['bruttotxt'].'</div>';
			echo '<div class="col-sm-4 col-md-4 ph-checkout-payment-brutto">'.$priceI['bruttoformat'].'</div>';
		}
		echo '</div></div>';
		
		
		echo '<div class="ph-cb"></div>';
			
	}
	
	echo '<div class="ph-cb">&nbsp;</div>';
	
	echo '<div class="col-sm-12 col-md-12 ">';
	//echo '<div class="radio">';
	echo '<label>'.JText::_('COM_PHOCACART_COUPON_CODE').' <input type="text" name="phcoupon" id="phcoupon" value="" ></label>';
	//echo '</div>';
	echo '</div>';
	
	echo '</div>';// end payment cost box
	echo '</div>';// end payment row	
	echo '<div class="ph-cb"></div>';
	
	echo '<div class="pull-right ph-checkout-shipping-save">';
	echo '<button class="btn btn-primary btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-floppy-disk"></span> '.JText::_('COM_PHOCACART_SAVE').'</button>';
	echo '</div>';
		
	echo '<div class="ph-cb"></div>';	
	echo '</div>'."\n";// end box action
	
	echo '<input type="hidden" name="task" value="checkout.savepayment" />'. "\n";
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo JHtml::_('form.token');
	echo '</form>'. "\n";

// PAYMENT NOT ADDED OR SHIPPING IS EDITED OR ADDRESS IS EDITED
} else {
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header-pas"><div class="pull-right"><span class="glyphicon glyphicon-remove-circle ph-checkout-icon-not-ok"></span></div><h3>'.$this->t['np'].'. '.JText::_('COM_PHOCACART_PAYMENT_OPTIONS').'</h3></div>';
	echo '</div>';
}
?>