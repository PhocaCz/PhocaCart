<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event;

$layoutI 		= new FileLayout('icon_checkout_status', null, array('component' => 'com_phocacart'));
$d				= array();
$d['s']			= $this->s;
$d['suffix']	= $this->t['icon_suffix'];
$d['number']	= $this->t['ns'];
$d['type']		= $this->t['checkout_icon_status'];

$price	= new PhocacartPrice();

if ($this->a->shippingnotused == 1) {

	// Shipping not used

// ONLY DISPLAY - shipping method was added and user don't want to edit it
} else if ($this->a->shippingview) {

	$d['status']	= 'finished';

	echo '<div class="ph-checkout-box-shipping ph-checkout-box-status-'.$d['status'].'">';

	// Header
	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" >';
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-box-header" id="phcheckoutshippingview">'.$layoutI->render($d).'<h3>'.$this->t['ns'].'. '.Text::_('COM_PHOCACART_SHIPPING_OPTIONS').'</h3></div>';
	echo '</div>';


	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="'.$this->s['c']['form-horizontal.form-validate'].'" role="form" id="phCheckoutAddress">';
	echo '<div id="ph-request-message" style="display:none"></div>';

	// Body
	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-action">';

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-shipping-row" id="phShippingMethods" >';
	echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_SHIPPING_METHODS').'</div>';
	echo '</div>';


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



	if (isset($this->t['shippingmethod']) && isset($this->t['shippingmethod']['title']) && $this->t['shippingmethod']['title'] != '') {

		//echo '<div class="'.$this->s['c']['row'].'">';
		echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].' ph-checkout-shipping-row-item">';

		if (isset($this->t['shippingmethod']['image']) && $this->t['shippingmethod']['image'] != '') {
			echo '<div class="ph-shipping-image"><img src="'.Uri::base(true) .'/'. $this->t['shippingmethod']['image'].'" alt="'.htmlspecialchars(strip_tags($this->t['shippingmethod']['title'])).'" /></div>';
		}

		echo '<div class="ph-shipping-title">'.$this->t['shippingmethod']['title'].'</div>';

		// Event
		$paramsShipping = array();
		if (isset($this->t['shippingmethod']['params_shipping']) && !empty($this->t['shippingmethod']['params_shipping'])) {
			$paramsShipping = json_decode($this->t['shippingmethod']['params_shipping'], true);
		}

		if (isset($this->t['shippingmethod']['method']) && $this->t['shippingmethod']['method'] != '') {
			$results = Dispatcher::dispatch(new Event\Shipping\GetShippingBranchInfo('com_phocacart.checkout', $this->t['shippingmethod'], $paramsShipping, [
				'pluginname' => $this->t['shippingmethod']['method'],
			]));

			if (!empty($results)) {
				foreach ($results as $k => $v) {
					if ($v != false && isset($v['content']) && $v['content'] != '') {
						echo $v['content'];
					}
				}
			}
			/*
			// INSTRUCTIONS: test the plugin in event this way:

			protected $name 	= 'plugin_name';

			function PCSgetShippingBranchInfo($context, $shippingMethod, $eventData) {
				if (!isset($eventData['pluginname']) || isset($eventData['pluginname']) && $eventData['pluginname'] != $this->name) {
					return false;
				}
			}
			*/

		}


		if ($this->t['display_shipping_desc'] && $this->t['shippingmethod']['description'] != '') {
			echo '<div class="ph-checkout-shipping-desc">'.HTMLHelper::_('content.prepare', $this->t['shippingmethod']['description']).'</div>';
		}

		echo '</div>';

		echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].'">';
        if ($this->a->shippingdisplayeditbutton) {
            echo '<div class="'.$this->s['c']['pull-right'].' ph-checkout-shipping-edit">';
            echo '<button class="'.$this->s['c']['btn.btn-success.btn-sm'].' ph-btn">';
			//echo '<span class="' . $this->s['i']['edit'] . '"></span> ';
			echo PhocacartRenderIcon::icon($this->s['i']['edit'], '', ' ');
			echo Text::_('COM_PHOCACART_EDIT_SHIPPING') . '</button>';
            echo '</div>';
        }
		echo '</div>';
        //echo '</div>'; // end checkout_shipping_row_display

	}
	//echo '<div class="ph-cb"></div>';
	echo '</div>';// end Body
	//echo '<div class="ph-cb"></div>'."\n";// end row action

	//echo '</div>'."\n";// end box action

	echo '<input type="hidden" name="shippingedit" value="1" />';
	//echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo HTMLHelper::_('form.token');
	echo '</form>'. "\n";
	//echo '</div>';// end checkout box row

	echo '</div>';// end box shipping

// ADD OR EDIT - user didn't add the shipping yet or user wants to edit it now
} else if ($this->a->shippingedit == 1)  {

	$d['status']	= 'pending';
	$total			= $this->cart->getTotal();
	$price			= new PhocacartPrice();

	echo '<div class="ph-checkout-box-shipping ph-checkout-box-status-'.$d['status'].'">';


	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" >';

	// Header
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-box-header" id="phcheckoutshippingedit">'.$layoutI->render($d).'<h3>'.$this->t['ns'].'. '.Text::_('COM_PHOCACART_SHIPPING_OPTIONS').'</h3></div>';
	echo '</div>';


	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="'.$this->s['c']['form-horizontal.form-validate'].'" role="form" id="phCheckoutAddress">';
	echo '<div id="ph-request-message" style="display:none"></div>';


	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-action">';

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-shipping-row" id="phShippingMethods" >';
	echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_SHIPPING_METHODS').'</div>';
	echo '</div>';


	//echo '<div class="'.$this->s['c']['row'].' ph-checkout-shipping-cost-box">';

	foreach($this->t['shippingmethods'] as $k => $v) {


		$checked = '';
		if (isset($v->selected) && $v->selected == 1 ) {
			$checked = 'checked="checked"';
		}

		$priceI = $price->getPriceItemsShipping($v->cost, $v->cost_additional, $v->calculation_type, $total[0], $v->taxid, $v->taxrate, $v->taxcalculationtype, $v->taxtitle, 0, 1, 'SHIPPING_', $v->taxhide);

		echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-shipping-title-box">';

		echo '<div class="'.$this->s['c']['controls'] .'">';
		echo '<label><input type="radio" class="'.$this->s['c']['inputbox.radio'].'" name="phshippingopt" id="phshippingopt'.$v->id.'" value="'.$v->id.'" '.$checked.' >';


		if ($v->image != '') {
			echo '<span class="ph-shipping-image"><img src="'.Uri::base(true) .'/'. $v->image.'" alt="'.htmlspecialchars(strip_tags($v->title)).'" /></span>';
		}

		echo '<span class="ph-shipping-title">'.$v->title.'</span>';
		echo '</label>';
		echo '</div>';

		// Event
		if (isset($v->method) && $v->method != '') {
			$results = Dispatcher::dispatch(new Event\Shipping\GetShippingBranches('com_phocacart.checkout', $v, [
				'pluginname' => $v->method,
			]));

			if (!empty($results)) {
				foreach ($results as $k2 => $v2) {
					if ($v2 != false && isset($v2['content']) && $v2['content'] != '') {
						echo $v2['content'];
					}
				}
			}
		}

		if ($this->t['display_shipping_desc'] && $v->description != '') {
			echo '<div class="ph-checkout-shipping-desc">'.HTMLHelper::_('content.prepare', $v->description).'</div>';
		}
		echo '</div>';

		echo '<div class="'.$this->s['c']['col.xs12.sm6.md6'].' ph-shipping-price-box">';
		echo '<div class="radio">';

		// We can hide netto and tax by tax method
		$displayPriceItems = PhocaCartPrice::displayPriceItems($priceI, 'checkoutshipping');
		if ($displayPriceItems['tax'] == 0) {
			$priceI['taxformat'] = '';
		}
		if ($displayPriceItems['netto'] == 0) {
			$priceI['nettoformat'] = '';
		}


		echo '<div class="'.$this->s['c']['row'].'">';
		if ($this->t['zero_shipping_price'] == 0 && $priceI['zero'] == 1) {
			// Display blank price field
		} else if ($this->t['zero_shipping_price'] == 2 && $priceI['zero'] == 1) {
			// Display free text
			echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].' ph-checkout-shipping-free-txt"></div>';
			echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-checkout-shipping-free">'.Text::_('COM_PHOCACART_FREE').'</div>';
		} else {
			if ($priceI['nettoformat'] == $priceI['bruttoformat']) {

			} else if ($priceI['nettoformat'] != '') {
				echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].' ph-checkout-shipping-netto-txt">'.$priceI['nettotxt'].'</div>';
				echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-checkout-shipping-netto">'.$priceI['nettoformat'].'</div>';
			}

			if ($priceI['taxformat'] != '') {
				echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].' ph-checkout-shipping-tax-txt">'.$priceI['taxtxt'].'</div>';
				echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-checkout-shipping-tax">'.$priceI['taxformat'].'</div>';
			}

			if ($priceI['bruttoformat'] != '') {
				echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].' ph-checkout-shipping-brutto-txt">'.$priceI['bruttotxt'].'</div>';
				echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-checkout-shipping-brutto">'.$priceI['bruttoformat'].'</div>';
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

	//echo '</div>';// end shipping cost box
	//echo '</div>';// end shipping row

	//echo '<div class="ph-cb"></div>';

	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].'">';
	echo '<div class="'.$this->s['c']['pull-right'].' ph-checkout-shipping-save">';
	echo '<button class="'.$this->s['c']['btn.btn-primary.btn-sm'].' ph-btn">'.PhocacartRenderIcon::icon($this->s['i']['save'], '', ' ') .Text::_('COM_PHOCACART_CHECKOUT_SHIPPING_SAVE').'</button>';
	echo '</div>';
	echo '</div>';

	//echo '<div class="ph-cb"></div>';

	echo '</div>'."\n";// end box action

	echo '<input type="hidden" name="task" value="checkout.saveshipping" />'. "\n";
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo HTMLHelper::_('form.token');
	echo '</form>'. "\n";

	echo '</div>';// end box shipping


}  else {



	$d['status']	= 'pending';

	echo '<div class="ph-checkout-box-shipping ph-checkout-box-status-'.$d['status'].'">';

	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" >';
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-box-header-pas">'.$layoutI->render($d).'<h3>'.$this->t['ns'].'. '.Text::_('COM_PHOCACART_SHIPPING_OPTIONS').'</h3></div>';
	echo '</div>';

	echo '</div>';// end box shipping
}



?>
