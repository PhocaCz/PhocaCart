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
$d['number']	= $this->t['ns'];
$d['type']		= $this->t['checkout_icon_status'];


$price	= new PhocacartPrice();

if ($this->a->shippingnotused == 1) {


	// Shipping not used

// ONLY DISPLAY - shipping method was added and user don't want to edit it
} else if ($this->a->shippingview) {

	$d['status']	= 'finished';
	// Header
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header" id="phcheckoutshippingview">'.$layoutI->render($d).'<h3>'.$this->t['ns'].'. '.JText::_('COM_PHOCACART_SHIPPING_OPTIONS').'</h3></div>';
	echo '</div><div class="ph-cb"></div>';


	echo '<div class="ph-checkout-box-action">';
	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutAddress">';
	echo '<div id="ph-request-message" style="display:none"></div>';

	echo '<div class="col-sm-12 col-md-12 ph-checkout-shipping-row" id="phShippingMethods" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_SHIPPING_METHODS').'</div>';



	/*foreach($this->t['shippingmethods'] as $k => $v) {

		// Display the current one selected
		if (isset($this->t['cartitems']['shipping']) && (int)$this->t['cartitems']['shipping'] == (int)$v->id) {

			//$priceI = $price->getPriceItems($v->cost, $v->taxid, $v->taxrate, $v->taxcalculationtype, $v->taxtitle, 1, 1);

			echo '<div class="col-sm-8 col-md-8 ">'.$v->title.'</div>';

			echo '<div class="col-sm-4 col-md-4">';
		/*
			if (isset($priceI['netto']) && $priceI['netto'] != '' && isset($priceI['nettotxt']) && $priceI['nettotxt'] != '') {
				//echo '<div class="col-sm-6 col-md-6"></div>';
				echo '<div class="col-sm-8 col-md-8">'.$priceI['nettotxt'].'</div>';
				echo '<div class="col-sm-4 col-md-4 ph-checkout-shipping-netto">'.$priceI['netto'].'</div>';
			}

			if (isset($priceI['tax']) && $priceI['tax'] != '' && isset($priceI['taxtxt']) && $priceI['taxtxt'] != '') {
				//echo '<div class="col-sm-6 col-md-6"></div>';
				echo '<div class="col-sm-8 col-md-8">'.$priceI['taxtxt'].'</div>';
				echo '<div class="col-sm-4 col-md-4 ph-checkout-shipping-tax">'.$priceI['tax'].'</div>';
			}

			if (isset($priceI['brutto']) && $priceI['brutto'] != '' && isset($priceI['bruttotxt']) && $priceI['bruttotxt'] != '') {
				//echo '<div class="col-sm-6 col-md-6"></div>';
				echo '<div class="col-sm-8 col-md-8">'.$priceI['bruttotxt'].'</div>';
				echo '<div class="col-sm-4 col-md-4 ph-checkout-shipping-brutto">'.$priceI['brutto'].'</div>';
			}*/
			/*echo '</div>';
			echo '<div class="ph-cb">&nbsp;</div>';
		}
	}
	echo '</div>';// end shipping cost box
	echo '<div class="ph-cb"></div>';*/


	if (isset($this->t['shippingmethod']) && $this->t['shippingmethod']['title'] != '') {

		echo '<div class="col-sm-8 col-md-8 ">';

		if (isset($this->t['shippingmethod']['image']) && $this->t['shippingmethod']['image'] != '') {
			echo '<div class="ph-shipping-image"><img src="'.JURI::base(true) .'/'. $this->t['shippingmethod']['image'].'" alt="'.htmlspecialchars(strip_tags($this->t['shippingmethod']['title'])).'" /></div>';
		}

		echo '<div class="ph-shipping-title">'.$this->t['shippingmethod']['title'].'</div>';

		if ($this->t['display_shipping_desc'] && $this->t['shippingmethod']['description'] != '') {
			echo '<div class="ph-checkout-shipping-desc">'.JHtml::_('content.prepare', $this->t['shippingmethod']['description']).'</div>';
		}

		echo '</div>';

		echo '<div class="col-sm-4 col-md-4 ">';
        if ($this->a->shippingdisplayeditbutton) {
            echo '<div class="ph-pull-right ph-checkout-shipping-edit">';
            echo '<button class="btn btn-success btn-sm ph-btn"><span class="' . PhocacartRenderIcon::getClass('edit') . '"></span> ' . JText::_('COM_PHOCACART_EDIT_SHIPPING') . '</button>';
            echo '</div>';
        }
		echo '</div>';

	}
	echo '<div class="ph-cb"></div>';
	echo '</div><div class="ph-cb"></div>'."\n";// end row action

	echo '<input type="hidden" name="shippingedit" value="1" />';
	//echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo JHtml::_('form.token');
	echo '</form>'. "\n";
	echo '</div>';// end checkout box row

// ADD OR EDIT - user didn't add the shipping yet or user wants to edit it now
} else if ($this->a->shippingedit)  {

	$d['status']	= 'pending';

	$total	= $this->cart->getTotal();
	$price	= new PhocacartPrice();

	// Header
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header" id="phcheckoutshippingedit">'.$layoutI->render($d).'<h3>'.$this->t['ns'].'. '.JText::_('COM_PHOCACART_SHIPPING_OPTIONS').'</h3></div>';
	echo '</div><div class="ph-cb"></div>';


	echo '<div class="ph-checkout-box-action">';
	echo '<div id="ph-request-message" style="display:none"></div>';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutAddress">';

	echo '<div class="col-sm-12 col-md-12 ph-checkout-shipping-row" id="phShippingMethods" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_SHIPPING_METHODS').'</div>';

	echo '<div class="ph-checkout-shipping-cost-box">';

	foreach($this->t['shippingmethods'] as $k => $v) {


		$checked = '';
		if (isset($v->selected) && $v->selected == 1 ) {
			$checked = 'checked="checked"';
		}

		$priceI = $price->getPriceItemsShipping($v->cost, $v->cost_additional, $v->calculation_type, $total[0], $v->taxid, $v->taxrate, $v->taxcalculationtype, $v->taxtitle, 0, 1, '');

		echo '<div class="col-sm-6 col-md-6 ">';
		echo '<div class="radio">';
		echo '<label><input type="radio" name="phshippingopt" id="phshippingopt'.$v->id.'" value="'.$v->id.'" '.$checked.' >';


		if ($v->image != '') {
			echo '<span class="ph-shipping-image"><img src="'.JURI::base(true) .'/'. $v->image.'" alt="'.htmlspecialchars(strip_tags($v->title)).'" /></span>';
		}

		echo '<span class="ph-shipping-title">'.$v->title.'</span>';

		echo '</label>';


		echo '</div>';
		if ($this->t['display_shipping_desc'] && $v->description != '') {
			echo '<div class="ph-checkout-shipping-desc">'.JHtml::_('content.prepare', $v->description).'</div>';
		}
		echo '</div>';

		echo '<div class="col-sm-6 col-md-6"><div class="radio">';


		if ($this->t['zero_shipping_price'] == 0 && $priceI['zero'] == 1) {
			// Display blank price field
		} else if ($this->t['zero_shipping_price'] == 2 && $priceI['zero'] == 1) {
			// Display free text
			echo '<div class="col-sm-8 col-md-8"></div>';
			echo '<div class="col-sm-4 col-md-4 ph-checkout-shipping-tax">'.JText::_('COM_PHOCACART_FREE').'</div>';
		} else {
			if ($priceI['nettoformat'] == $priceI['bruttoformat']) {

			} else if ($priceI['nettoformat'] != '') {
				echo '<div class="col-sm-8 col-md-8">'.$priceI['nettotxt'].'</div>';
				echo '<div class="col-sm-4 col-md-4 ph-checkout-shipping-netto">'.$priceI['nettoformat'].'</div>';
			}

			if ($priceI['taxformat'] != '') {
				echo '<div class="col-sm-8 col-md-8">'.$priceI['taxtxt'].'</div>';
				echo '<div class="col-sm-4 col-md-4 ph-checkout-shipping-tax">'.$priceI['taxformat'].'</div>';
			}

			if ($priceI['bruttoformat'] != '') {
				echo '<div class="col-sm-8 col-md-8">'.$priceI['bruttotxt'].'</div>';
				echo '<div class="col-sm-4 col-md-4 ph-checkout-shipping-brutto">'.$priceI['bruttoformat'].'</div>';
			}

			if ($priceI['costinfo'] != '') {
				echo '<div class="col-sm-4 col-md-4"></div>';
				echo '<div class="col-sm-8 col-md-8 ph-right ph-checkout-payment-cost-info">'.$priceI['costinfo'].'</div>';

			}
		}

		echo '</div></div>';

		echo '<div class="ph-cb">&nbsp;</div>';

	}
	echo '</div>';// end shipping cost box

	echo '<div class="ph-cb"></div>';

	echo '<div class="ph-pull-right ph-checkout-shipping-save">';
		echo '<button class="btn btn-primary btn-sm ph-btn"><span class="'.PhocacartRenderIcon::getClass('save').'"></span> '.JText::_('COM_PHOCACART_SAVE').'</button>';
	echo '</div>';

	echo '<div class="ph-cb"></div>';
	echo '</div><div class="ph-cb"></div>'."\n";// end box action

	echo '<input type="hidden" name="task" value="checkout.saveshipping" />'. "\n";
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo JHtml::_('form.token');
	echo '</form>'. "\n";
	echo '</div>';// end checkout box row

}  else {

	$d['status']	= 'pending';

	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header-pas">'.$layoutI->render($d).'<h3>'.$this->t['ns'].'. '.JText::_('COM_PHOCACART_SHIPPING_OPTIONS').'</h3></div>';
	echo '</div><div class="ph-cb"></div>';
}



?>
