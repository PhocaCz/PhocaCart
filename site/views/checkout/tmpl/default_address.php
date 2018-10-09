<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$layoutI 		= new JLayoutFile('icon_checkout_status', null, array('component' => 'com_phocacart'));
$d				= array();
$d['suffix']	= $this->t['icon_suffix'];
$d['number']	= $this->t['na'];
$d['type']		= $this->t['checkout_icon_status'];

if ($this->a->addressedit == 1) {
	
	$d['status']	= 'pending';
	
	// Header
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header" id="phcheckoutaddressedit">'.$layoutI->render($d).'<h3>'.$this->t['na'].'. '.JText::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS').'</h3></div>';
	echo '</div><div class="ph-cb"></div>';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutAddress">';
	echo '<div class="ph-checkout-box-action">';
	echo '<div id="ph-request-message" style="display:none"></div>';
	echo '<div class="col-sm-6 col-md-6 ph-checkout-billing-row" id="phBillingAddress" >';
	if ($this->t['dataaddressform']['b'] != '') {	
		echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_BILLING_ADDRESS').'</div>';
		echo $this->t['dataaddressform']['b'];
	}
	echo '</div>';// end row
	
	echo '<div class="col-sm-6 col-md-6 ph-checkout-shipping-row" id="phShippingAddress" >';
	if ($this->t['dataaddressform']['s'] != '') {
		echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_SHIPPING_ADDRESS').'</div>';
		echo $this->t['dataaddressform']['s'];
	}
	echo '</div><div class="ph-cb"></div>';// end row
	
	echo '<div class="ph-cb"></div>';
	
	echo '<div class="ph-pull-right ph-checkout-check-box">';
	
	if ($this->t['dataaddressform']['s'] != '') {
		echo '<div class="checkbox">';
		echo '<label><input type="checkbox" id="phCheckoutBillingSameAsShipping" name="phcheckoutbsas" '.$this->t['dataaddressform']['bsch'].' > '.JText::_('COM_PHOCACART_DELIVERY_AND_BILLING_ADDRESSES_ARE_THE_SAME').'</label>';
		echo '</div>';
	}
	echo '</div>';
	
	echo '<div class="ph-cb"></div>';
	
	echo '<div class="ph-pull-right ph-checkout-address-save">';
	echo '<button class="btn btn-primary btn-sm ph-btn"><span class="'.PhocacartRenderIcon::getClass('save').'"></span> '.JText::_('COM_PHOCACART_SAVE').'</button>';
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
	
	$d['status']	= 'finished';
	// User completed all items in the form
	// Header
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header"  id="phcheckoutaddressview">'.$layoutI->render($d).'<h3>'.$this->t['na'].'. '.JText::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS').'</h3></div>';
	echo '</div><div class="ph-cb"></div>';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutAddress">';
	echo '<div class="ph-checkout-box-action">';
	echo '<div id="ph-request-message" style="display:none"></div>';
	
	
	
	if (isset($this->t['dataaddressoutput']['bsch']) && $this->t['dataaddressoutput']['bsch'] == 1) {
		
		echo '<div class="col-sm-12 col-md-12 ph-checkout-billing-row"  >';
		echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS').'</div>';
		echo $this->t['dataaddressoutput']['b'];
		echo '</div>';// end row
	
		//echo '<div class="col-sm-6 col-md-6 ph-checkout-shipping-row" >';
		//echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_SHIPPING_ADDRESS').'</div>';
		//echo JText::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS_IS_THE_SAME');
		//echo '</div>';
		
	} else {
		
		echo '<div class="col-sm-6 col-md-6 ph-checkout-billing-row"  >';
		if ($this->t['dataaddressoutput']['b'] != '') {
			echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_BILLING_ADDRESS').'</div>';
			echo $this->t['dataaddressoutput']['b'];
		}
		echo '</div>';// end row
		
		echo '<div class="col-sm-6 col-md-6 ph-checkout-shipping-row" >';
		if ($this->t['dataaddressoutput']['s'] != '') {
			echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_SHIPPING_ADDRESS').'</div>';
			echo $this->t['dataaddressoutput']['s'];
		}
		echo '</div>';
	}
	echo '<div class="ph-cb"></div>';// end row
	
	
	echo '<div class="ph-cb"></div>';
	
	echo '<div class="ph-pull-right ph-checkout-address-edit">';
	echo '<button class="btn btn-success btn-sm ph-btn"><span class="'.PhocacartRenderIcon::getClass('edit').'"></span> '.JText::_('COM_PHOCACART_EDIT_ADDRESS').'</button>';
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
	
	$d['status']	= 'pending';
	
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header-pas">'.$layoutI->render($d).'<h3>'.$this->t['na'].'. '.JText::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS').'</h3></div>';
	echo '</div><div class="ph-cb"></div>';
}
?>