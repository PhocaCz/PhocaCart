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
$d['number']	= $this->t['np'];
$d['type']		= $this->t['checkout_icon_status'];

if ($this->a->paymentnotused == 1) {

	// Payment not used

// PAYMENT ADDED
} else if ($this->a->paymentview == 1) {

	$d['status']	= 'finished';
	// Payment is added and goes to confirm
	// ONLY DISPLAY - pamyent method was added and user don't want to edit it


	// Header
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header" id="phcheckoutpaymentview">'.$layoutI->render($d).'<h3>'.$this->t['np'].'. '.JText::_('COM_PHOCACART_PAYMENT_OPTIONS').'</h3></div>';
	echo '</div><div class="ph-cb"></div>';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutAddress">';
	echo '<div class="ph-checkout-box-action">';
	echo '<div id="ph-request-message" style="display:none"></div>';

	echo '<div class="col-sm-12 col-md-12 ph-checkout-payment-row" id="phPaymentMethods" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_PAYMENT_METHODS').'</div>';

	if (isset($this->t['paymentmethod']) && $this->t['paymentmethod']['title'] != '') {

		echo '<div class="col-sm-8 col-md-8 ">';

		if ($this->t['paymentmethod']['image'] != '') {
			echo '<div class="ph-payment-image"><img src="'.JURI::base(true) .'/'. $this->t['paymentmethod']['image'].'" alt="'.htmlspecialchars(strip_tags($this->t['paymentmethod']['title'])).'" /></div>';
		}

		echo '<div class="ph-payment-title">'.$this->t['paymentmethod']['title'].'</div>';

		if ($this->t['display_payment_desc'] && $this->t['paymentmethod']['description'] != '') {
			echo '<div class="ph-checkout-payment-desc">'.JHtml::_('content.prepare', $this->t['paymentmethod']['description']).'</div>';
		}

		echo '</div>';

		echo '<div class="col-sm-4 col-md-4 ">';
		if ($this->a->paymentdisplayeditbutton) {
            echo '<div class="ph-pull-right ph-checkout-payment-edit">';
            echo '<button class="btn btn-success btn-sm ph-btn"><span class="' . PhocacartRenderIcon::getClass('edit') . '"></span> ' . JText::_('COM_PHOCACART_EDIT_PAYMENT') . '</button>';
            echo '</div>';
        }
		echo '</div>';

	}
	echo '<div class="ph-cb"></div>';
	echo '</div><div class="ph-cb"></div>'."\n";// end row action

	echo '</div>'."\n";// end box action

	echo '<input type="hidden" name="paymentedit" value="1" />';
	//echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo JHtml::_('form.token');
	echo '</form>'. "\n";


// PAYMENT EDIT
} else if ($this->a->paymentedit == 1) {

	$d['status']	= 'pending';


	$total	= $this->cart->getTotal();
	$price	= new PhocacartPrice();

	// Paymnet is not added or we edit it but payment is added, we can edit
	// Header
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header" id="phcheckoutpaymentedit">'.$layoutI->render($d).'<h3>'.$this->t['np'].'. '.JText::_('COM_PHOCACART_PAYMENT_OPTIONS').'</h3></div>';
	echo '</div><div class="ph-cb"></div>';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutPayment">';
	echo '<div class="ph-checkout-box-action">';
	echo '<div id="ph-request-message" style="display:none"></div>';
	echo '<div class="col-sm-12 col-md-12 ph-checkout-payment-row" id="phpaymentMethods" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_PAYMENT_METHODS').'</div>';

	echo '<div class="ph-checkout-payment-cost-box">';

	foreach($this->t['paymentmethods'] as $k => $v) {


		$checked = '';
		if (isset($v->selected) && $v->selected == 1 ) {
			$checked = 'checked="checked"';
		}

		$priceI = $price->getPriceItemsPayment($v->cost, $v->cost_additional, $v->calculation_type, $total[0], $v->taxid, $v->taxrate, $v->taxcalculationtype, $v->taxtitle, 0, 1, '');

		echo '<div class="col-sm-6 col-md-6 ">';
		echo '<div class="radio">';
		echo '<label><input type="radio" name="phpaymentopt" id="phpaymentopt'.$v->id.'" value="'.$v->id.'" '.$checked.' >';


		if ($v->image != '') {
			echo '<span class="ph-payment-image"><img src="'.JURI::base(true) .'/'. $v->image.'" alt="'.htmlspecialchars(strip_tags($v->title)).'" /></span>';
		}

		echo '<span class="ph-payment-title">'.$v->title.'</span>';

		echo '</label>';

		echo '</div>';



		if ($this->t['display_payment_desc'] && $v->description != '') {
			echo '<div class="ph-checkout-payment-desc">'.JHtml::_('content.prepare', $v->description).'</div>';
		}

		echo '</div>';

		echo '<div class="col-sm-6 col-md-6"><div class="radio">';

		if ($this->t['zero_payment_price'] == 0 && $priceI['zero'] == 1) {
			// Display blank price field
		} else if ($this->t['zero_payment_price'] == 2 && $priceI['zero'] == 1) {
			// Display free text
			echo '<div class="col-sm-8 col-md-8"></div>';
			echo '<div class="col-sm-4 col-md-4 ph-checkout-shipping-tax">'.JText::_('COM_PHOCACART_FREE').'</div>';
		} else {

			if ($priceI['nettoformat'] == $priceI['bruttoformat']) {

			} else if ($priceI['nettoformat'] != '') {
				echo '<div class="col-sm-8 col-md-8">'.$priceI['nettotxt'].'</div>';
				echo '<div class="col-sm-4 col-md-4 ph-checkout-payment-netto">'.$priceI['nettoformat'].'</div>';
			}

			if ($priceI['taxformat'] != '') {
				echo '<div class="col-sm-8 col-md-8">'.$priceI['taxtxt'].'</div>';
				echo '<div class="col-sm-4 col-md-4 ph-checkout-payment-tax">'.$priceI['taxformat'].'</div>';
			}

			if ($priceI['bruttoformat'] != '') {
				echo '<div class="col-sm-8 col-md-8">'.$priceI['bruttotxt'].'</div>';
				echo '<div class="col-sm-4 col-md-4 ph-checkout-payment-brutto">'.$priceI['bruttoformat'].'</div>';
			}

			if ($priceI['costinfo'] != '') {
				echo '<div class="col-sm-4 col-md-4"></div>';
				echo '<div class="col-sm-8 col-md-8 ph-right ph-checkout-payment-cost-info">'.$priceI['costinfo'].'</div>';

			}
		}

		echo '</div></div>';


		echo '<div class="ph-cb"></div>';

	}

	echo '<div class="ph-cb">&nbsp;</div>';

	// COUPON CODE
	if ($this->t['enable_coupons'] == 1) {
		echo '<div class="col-sm-12 col-md-12 ">';
		echo '<label>'.JText::_('COM_PHOCACART_COUPON_CODE'). ' <small>('.JText::_('COM_PHOCACART_APPLY_COUPON_CODE').')</small><br /><input type="text" name="phcoupon" id="phcoupon" value="'.$this->t['couponcodevalue'].'" ></label>';
		echo '</div><div class="ph-cb"></div>';
	}

	// REWARD POINTS
	if ($this->t['rewards']['apply']) {
		echo '<div class="col-sm-12 col-md-12 ">';
		echo '<label>'.JText::_('COM_PHOCACART_REWARD_POINTS').' '.$this->t['rewards']['text'].'<br /><input type="text" name="phreward" id="phreward" value="'.$this->t['rewards']['usedvalue'].'" ></label>';
		echo '</div><div class="ph-cb"></div>';
	}

	echo '</div>';// end payment cost box
	echo '</div>';// end payment row
	echo '<div class="ph-cb"></div>';

	echo '<div class="ph-pull-right ph-checkout-payment-save">';
	echo '<button class="btn btn-primary btn-sm ph-btn"><span class="'.PhocacartRenderIcon::getClass('save').'"></span> '.JText::_('COM_PHOCACART_SAVE').'</button>';
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

	$d['status']	= 'pending';

	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header-pas">'.$layoutI->render($d).'<h3>'.$this->t['np'].'. '.JText::_('COM_PHOCACART_PAYMENT_OPTIONS').'</h3></div>';
	echo '</div><div class="ph-cb"></div>';
}
?>
