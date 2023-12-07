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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;

$layoutI 		= new FileLayout('icon_checkout_status', null, array('component' => 'com_phocacart'));
$d				= array();
$d['s']			= $this->s;
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

	echo '<div class="ph-checkout-box-payment ph-checkout-box-status-'.$d['status'].'">';

	// Header
	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" >';

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-box-header" id="phcheckoutpaymentview">'.$layoutI->render($d).'<h3>'.$this->t['np'].'. '.Text::_('COM_PHOCACART_PAYMENT_OPTIONS').'</h3></div>';
	echo '</div>';


	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="'.$this->s['c']['form-horizontal.form-validate'].'" role="form" id="phCheckoutAddress">';
	echo '<div id="ph-request-message" style="display:none"></div>';

	// Body
	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-action">';

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-payment-row" id="phPaymentMethods" >';
	echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_PAYMENT_METHODS').'</div>';
	echo '</div>';

	if (isset($this->t['paymentmethod']) && isset($this->t['paymentmethod']['title']) && $this->t['paymentmethod']['title'] != '') {

		//echo '<div class="'.$this->s['c']['row'].'">';
		echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].'">';

		if (isset($this->t['paymentmethod']['image']) && $this->t['paymentmethod']['image'] != '') {
			echo '<div class="ph-payment-image"><img src="'.Uri::base(true) .'/'. $this->t['paymentmethod']['image'].'" alt="'.htmlspecialchars(strip_tags($this->t['paymentmethod']['title'])).'" /></div>';
		}

		echo '<div class="ph-payment-title">'.$this->t['paymentmethod']['title'].'</div>';

		if ($this->t['display_payment_desc'] && $this->t['paymentmethod']['description'] != '') {
			echo '<div class="ph-checkout-payment-desc">'.HTMLHelper::_('content.prepare', $this->t['paymentmethod']['description']).'</div>';
		}

		echo '</div>';

		echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].'">';
		if ($this->a->paymentdisplayeditbutton) {
            echo '<div class="'.$this->s['c']['pull-right'].' ph-checkout-payment-edit">';
            echo '<button class="'.$this->s['c']['btn.btn-success.btn-sm'].' ph-btn">';
			//echo '<span class="' . $this->s['i']['edit'] . '"></span> ';
			echo PhocacartRenderIcon::icon($this->s['i']['edit'], '', ' ');
			echo Text::_('COM_PHOCACART_EDIT_PAYMENT') . '</button>';
            echo '</div>';
        }
		echo '</div>';
		//echo '</div>'; // end checkout_payment_row_display

	}
	//echo '<div class="ph-cb"></div>';
	echo '</div>';// end Body
	//echo '<div class="ph-cb"></div>'."\n";// end row action

	//echo '</div>'."\n";// end box action

	echo '<input type="hidden" name="paymentedit" value="1" />';
	//echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo HTMLHelper::_('form.token');
	echo '</form>'. "\n";
    //echo '</div>';// end checkout box row

	echo '</div>';// end box payment

// PAYMENT EDIT
} else if ($this->a->paymentedit == 1) {

	$d['status']	= 'pending';
	$total			= $this->cart->getTotal();
	$price			= new PhocacartPrice();

	echo '<div class="ph-checkout-box-payment ph-checkout-box-status-'.$d['status'].'">';

	// Paymnet is not added or we edit it but payment is added, we can edit
	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" >';

	// Header
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-box-header" id="phcheckoutpaymentedit">'.$layoutI->render($d).'<h3>'.$this->t['np'].'. '.Text::_('COM_PHOCACART_PAYMENT_OPTIONS').'</h3></div>';
	echo '</div>';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="'.$this->s['c']['form-horizontal.form-validate'].'" role="form" id="phCheckoutPayment">';
	echo '<div id="ph-request-message" style="display:none"></div>';


	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-action">';

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-payment-row" id="phPaymentMethods" >';
	echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_PAYMENT_METHODS').'</div>';
	echo '</div>';

	//echo '<div class="'.$this->s['c']['row'].' ph-checkout-shipping-cost-box">';

	foreach($this->t['paymentmethods'] as $k => $v) {


		$checked = '';
		if (isset($v->selected) && $v->selected == 1 ) {
			$checked = 'checked="checked"';
		}

		$priceI = $price->getPriceItemsPayment($v->cost, $v->cost_additional, $v->calculation_type, $total[0], $v->taxid, $v->taxrate, $v->taxcalculationtype, $v->taxtitle, 0, 1, 'PAYMENT_', $v->taxhide);

		echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-payment-title-box">';
		echo '<div class="'.$this->s['c']['controls'] .'">';

		echo '<label><input type="radio" class="'.$this->s['c']['inputbox.radio'].'" name="phpaymentopt" id="phpaymentopt'.$v->id.'" value="'.$v->id.'" '.$checked.' >';


		if ($v->image != '') {
			echo '<span class="ph-payment-image"><img src="'.Uri::base(true) .'/'. $v->image.'" alt="'.htmlspecialchars(strip_tags($v->title)).'" /></span>';
		}

		echo '<span class="ph-payment-title">'.$v->title.'</span>';
		echo '</label>';
		echo '</div>';



		if ($this->t['display_payment_desc'] && $v->description != '') {
			echo '<div class="ph-checkout-payment-desc">'.HTMLHelper::_('content.prepare', $v->description).'</div>';
		}

		echo '</div>';

		echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-payment-price-box">';
		echo '<div class="radio">';

		// We can hide netto and tax by tax method
		$displayPriceItems = PhocaCartPrice::displayPriceItems($priceI, 'checkoutpayment');
		if ($displayPriceItems['tax'] == 0) {
			$priceI['taxformat'] = '';
		}
		if ($displayPriceItems['netto'] == 0) {
			$priceI['nettoformat'] = '';
		}

		echo '<div class="'.$this->s['c']['row'].'">';
		if ($this->t['zero_payment_price'] == 0 && $priceI['zero'] == 1) {
			// Display blank price field
		} else if ($this->t['zero_payment_price'] == 2 && $priceI['zero'] == 1) {
			// Display free text
			echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].' ph-checkout-payment-free-txt"></div>';
			echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-checkout-payment-free">'.Text::_('COM_PHOCACART_FREE').'</div>';
		} else {

			if ($priceI['nettoformat'] == $priceI['bruttoformat']) {

			} else if ($priceI['nettoformat'] != '') {
				echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].' ph-checkout-payment-netto-txt">'.$priceI['nettotxt'].'</div>';
				echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-checkout-payment-netto">'.$priceI['nettoformat'].'</div>';
			}

			if ($priceI['taxformat'] != '') {
				echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].' ph-checkout-payment-tax-txt">'.$priceI['taxtxt'].'</div>';
				echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-checkout-payment-tax">'.$priceI['taxformat'].'</div>';
			}

			if ($priceI['bruttoformat'] != '') {
				echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].' ph-checkout-payment-brutto-txt">'.$priceI['bruttotxt'].'</div>';
				echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-checkout-payment-brutto">'.$priceI['bruttoformat'].'</div>';
			}

			if ($priceI['costinfo'] != '') {
				// Possible variables:
				// $priceI['costinfoprice'] ... raw price without price additional
				// $priceI['costinfopriceadditional'] ... raw additional price
				echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].' ph-checkout-payment-cost-info-txt"></div>';
				echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-right ph-checkout-payment-cost-info">'.$priceI['costinfo'].'</div>';

			}
		}

		echo '</div>';// end row

		echo '</div>';// end radio

		echo '</div>';// end row second column
		//echo '<div class="ph-cb grid"></div>';

	}

	//echo '<div class="ph-cb"></div>';

	// COUPON CODE
	if ($this->t['enable_coupons'] > 0 && $this->t['display_apply_coupon_form'] == 1) {
		echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].'">';
		echo '<label>'.Text::_('COM_PHOCACART_COUPON_CODE'). ' <small>('.Text::_('COM_PHOCACART_APPLY_COUPON_CODE').')</small><br /><input class="'.$this->s['c']['form-control'].' ph-input-sm ph-input-apply-coupon" type="text" name="phcoupon" id="phcoupon" value="'.$this->t['couponcodevalue'].'" ></label>';
		echo '</div>';
		//echo '<div class="ph-cb"></div>';
	}

	// REWARD POINTS
	if ($this->t['rewards']['apply'] && $this->t['display_apply_reward_points_form'] == 1) {

		echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].'">';
		echo '<label>'.Text::_('COM_PHOCACART_REWARD_POINTS').' '.$this->t['rewards']['text'].'<br /><input class="'.$this->s['c']['form-control'].' ph-input-sm ph-input-apply-reward-points" type="text" name="phreward" id="phreward" value="'.$this->t['rewards']['usedvalue'].'" ></label>';
		echo '</div>';
		//echo '<div class="ph-cb"></div>';
	}

	//echo '</div>';// end payment cost box
	//echo '</div>';// end payment row

	//echo '<div class="ph-cb"></div>';

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].'">';
	echo '<div class="'.$this->s['c']['pull-right'].' ph-checkout-payment-save">';
	echo '<button class="'.$this->s['c']['btn.btn-primary.btn-sm'].' ph-btn">'.PhocacartRenderIcon::icon($this->s['i']['save'], '', ' ') .Text::_('COM_PHOCACART_CHECKOUT_PAYMENT_SAVE').'</button>';
	echo '</div>';
	echo '</div>';

	//echo '<div class="ph-cb"></div>';
	echo '</div>'."\n";// end box action

	echo '<input type="hidden" name="task" value="checkout.savepayment" />'. "\n";
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo HTMLHelper::_('form.token');
	echo '</form>'. "\n";

	echo '</div>';// end box payment

// PAYMENT NOT ADDED OR SHIPPING IS EDITED OR ADDRESS IS EDITED
} else {

	$d['status']	= 'pending';

	echo '<div class="ph-checkout-box-payment ph-checkout-box-status-'.$d['status'].'">';

	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row">';
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-box-header-pas">'.$layoutI->render($d).'<h3>'.$this->t['np'].'. '.Text::_('COM_PHOCACART_PAYMENT_OPTIONS').'</h3></div>';
	echo '</div>';

	echo '</div>';// end box payment
}
?>
