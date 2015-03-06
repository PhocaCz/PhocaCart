<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if ($this->a->addressedit == 1) {
		
	// Header
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header" id="phcheckoutaddressedit"><div class="pull-right"><span class="glyphicon glyphicon-remove-circle ph-checkout-icon-not-ok"></span></div><h3>'.$this->t['na'].'. '.JText::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS').'</h3></div>';
	echo '</div>';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutAddress">';
	echo '<div class="ph-checkout-box-action">';
	echo '<div id="ph-request-message" style="display:none"></div>';
	echo '<div class="col-sm-6 col-md-6 ph-checkout-billing-row" id="phBillingAddress" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_BILLING_ADDRESS').'</div>';
	echo $this->t['dataaddressform']['b'];
	echo '</div>';// end row
	
	echo '<div class="col-sm-6 col-md-6 ph-checkout-shipping-row" id="phShippingAddress" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_SHIPPING_ADDRESS').'</div>';
	echo $this->t['dataaddressform']['s'];
	echo '</div>';// end row
	
	echo '<div class="ph-cb"></div>';
	
	echo '<div class="pull-right ph-checkout-check-box">';
	
	echo '<div class="checkbox">';
	echo '<label><input type="checkbox" id="phCheckoutBillingSameAsShipping" name="phcheckoutbsas" '.$this->t['dataaddressform']['bsch'].' > '.JText::_('COM_PHOCACART_DELIVERY_AND_BILLING_ADDRESSES_ARE_THE_SAME').'</label>';
	echo '</div>';

	echo '</div>';
	
	echo '<div class="ph-cb"></div>';
	
	echo '<div class="pull-right ph-checkout-address-save">';
	echo '<button class="btn btn-primary btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-floppy-disk"></span> '.JText::_('COM_PHOCACART_SAVE').'</button>';
	//echo '<input type="submit" value="submit" />';
	echo '</div>';
	
	echo '<div class="ph-cb"></div>';
	echo '</div>'."\n";// end box action
	
	
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="task" value="checkout.saveaddress" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo JHtml::_('form.token');
	echo '</form>'. "\n";

} else if ($this->a->addressview == 1){
	// User completed all items in the form
	// Header
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header"  id="phcheckoutaddressview"><div class="pull-right"><span class="glyphicon glyphicon-ok-circle ph-checkout-icon-ok"></span></div><h3>'.$this->t['na'].'. '.JText::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS').'</h3></div>';
	echo '</div>';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutAddress">';
	echo '<div class="ph-checkout-box-action">';
	echo '<div id="ph-request-message" style="display:none"></div>';
	
	echo '<div class="col-sm-6 col-md-6 ph-checkout-billing-row"  >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_BILLING_ADDRESS').'</div>';
	echo $this->t['dataaddressoutput']['b'];
	echo '</div>';// end row
	
	echo '<div class="col-sm-6 col-md-6 ph-checkout-shipping-row" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_SHIPPING_ADDRESS').'</div>';
	echo $this->t['dataaddressoutput']['s'];
	echo '</div>';// end row
	
	
	echo '<div class="ph-cb"></div>';
	
	echo '<div class="pull-right ph-checkout-address-edit">';
	echo '<button class="btn btn-success btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-edit"></span> '.JText::_('COM_PHOCACART_EDIT_ADDRESS').'</button>';
	echo '</div>';
	
	echo '<div class="ph-cb"></div>';
	echo '</div>'."\n";// end box action
	
	
	
	echo '<input type="hidden" name="addressedit" value="1" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	//echo '<input type="hidden" name="task" value="checkout.editaddress" />'. "\n";
	//echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo JHtml::_('form.token');
	echo '</form>'. "\n";
		
} else {
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header-pas"><div class="pull-right"><span class="glyphicon glyphicon-remove-circle ph-checkout-icon-not-ok"></span></div><h3>'.$this->t['na'].'. '.JText::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS').'</h3></div>';
	echo '</div>';
}
?>