<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$layoutI 		= new FileLayout('icon_checkout_status', null, array('component' => 'com_phocacart'));
$d				= array();
$d['s']			= $this->s;
$d['suffix']	= $this->t['icon_suffix'];
$d['number']	= $this->t['na'];
$d['type']		= $this->t['checkout_icon_status'];

if ($this->a->addressedit == 1) {

	$d['status']	= 'pending';


	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row">';

	// Header
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-box-header" id="phcheckoutaddressedit">'.$layoutI->render($d).'<h3>'.$this->t['na'].'. '.Text::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS').'</h3></div>';
	echo '</div>';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="'.$this->s['c']['form-horizontal.form-validate'].'" role="form" id="phCheckoutAddress">';
	echo '<div id="ph-request-message" style="display:none"></div>';

	// Body
	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-action">';

	echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-checkout-billing-row" id="phBillingAddress" >';
	if ($this->t['dataaddressform']['b'] != '') {
		echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_BILLING_ADDRESS').'</div>';
		echo $this->t['dataaddressform']['b'];
	}
	echo '</div>';// end row

	echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-checkout-shipping-row" id="phShippingAddress" >';
	if ($this->t['dataaddressform']['s'] != '') {
		echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_SHIPPING_ADDRESS').'</div>';
		echo $this->t['dataaddressform']['s'];
	}
	echo '</div>';// end row

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].'">';
	echo '<div class="'.$this->s['c']['pull-right'].' ph-checkout-check-box">';
	if ($this->t['dataaddressform']['s'] != '' && $this->t['$delivery_billing_same_enabled'] != -1) {
		echo '<div class="'.$this->s['c']['controls'].'">';
		echo '<label><input class="'.$this->s['c']['inputbox.checkbox'].'" type="checkbox" id="phCheckoutBillingSameAsShipping" name="phcheckoutbsas" '.$this->t['dataaddressform']['bsch'].' > '.Text::_('COM_PHOCACART_DELIVERY_AND_BILLING_ADDRESSES_ARE_THE_SAME').'</label>';
		echo '</div>';
	}
	echo '</div>';
	echo '</div>';


	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].'">';
	echo '<div class="'.$this->s['c']['pull-right'].' ph-checkout-address-save">';
	echo '<button class="'.$this->s['c']['btn.btn-primary.btn-sm'].'">'.PhocacartRenderIcon::icon($this->s['i']['save'], '', ' ') .Text::_('COM_PHOCACART_SAVE').'</button>';
	//echo '<input type="submit" value="submit" />';
	echo '</div>';
	echo '</div>';


	echo '</div>'."\n";// end ph-checkout-box-action


	echo '<input type="hidden" name="tmpl" value="component" />'. "\n";
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="task" value="checkout.saveaddress" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo HTMLHelper::_('form.token');
	echo '</form>'. "\n";

} else if ($this->a->addressview == 1){

	$d['status']	= 'finished';
	// User completed all items in the form

	// Header
	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row">';

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-box-header"  id="phcheckoutaddressview">'.$layoutI->render($d).'<h3>'.$this->t['na'].'. '.Text::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS').'</h3></div>';
	echo '</div>';
	// end Header

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="'.$this->s['c']['form-horizontal.form-validate'].'" role="form" id="phCheckoutAddress">';
	echo '<div id="ph-request-message" style="display:none"></div>';

	// Body
	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-action">';
	if (isset($this->t['dataaddressoutput']['bsch']) && $this->t['dataaddressoutput']['bsch'] == 1) {

		echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-billing-row"  >';
		echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS').'</div>';
		echo $this->t['dataaddressoutput']['b'];
		echo '</div>';// end row

		//echo '<div class="col-sm-6 col-md-6 ph-checkout-shipping-row" >';
		//echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_SHIPPING_ADDRESS').'</div>';
		//echo JText::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS_IS_THE_SAME');
		//echo '</div>';

	} else {

		echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-checkout-billing-row"  >';
		if ($this->t['dataaddressoutput']['b'] != '') {
			echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_BILLING_ADDRESS').'</div>';
			echo $this->t['dataaddressoutput']['b'];
		}
		echo '</div>';// end row

		echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-checkout-shipping-row" >';
		if ($this->t['dataaddressoutput']['s'] != '') {
			echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_SHIPPING_ADDRESS').'</div>';
			echo $this->t['dataaddressoutput']['s'];
		}
		echo '</div>';
	}


	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].'">';
	echo '<div class="'.$this->s['c']['pull-right'].' ph-checkout-address-edit">';
	echo '<button class="'.$this->s['c']['btn.btn-success.btn-sm'].' ph-btn">';
	//echo '<span class="'.$this->s['i']['edit'].'"></span> ';
	echo PhocacartRenderIcon::icon($this->s['i']['edit'], '', ' ');
	echo Text::_('COM_PHOCACART_EDIT_ADDRESS').'</button>';
	echo '</div>';
	echo '</div>';


	echo '</div>'; // end ph-checkout-box-action




	echo '<input type="hidden" name="addressedit" value="1" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	//echo '<input type="hidden" name="task" value="checkout.editaddress" />'. "\n";
	//echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo HTMLHelper::_('form.token');
	echo '</form>'. "\n";

} else {

	$d['status']	= 'pending';

	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row">';
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-box-header-pas">'.$layoutI->render($d).'<h3>'.$this->t['na'].'. '.Text::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS').'</h3></div>';
	echo '</div>';
}
?>
