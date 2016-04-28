<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$price	= new PhocaCartPrice();
	
if ($this->a->shippingnotused == 1) {
	
	// Shipping not used
	
// ONLY DISPLAY - shipping method was added and user don't want to edit it
} else if ($this->a->shippingview) {

	// Header
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header" id="phcheckoutshippingview"><div class="pull-right"><span class="glyphicon glyphicon-ok-circle ph-checkout-icon-ok"></span></div><h3>'.$this->t['ns'].'. '.JText::_('COM_PHOCACART_SHIPPING_OPTIONS').'</h3></div>';
	echo '</div><div class="ph-cb"></div>';
	
	
	echo '<div class="ph-checkout-box-action">';
	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutAddress">';
	echo '<div id="ph-request-message" style="display:none"></div>';
	
	echo '<div class="col-sm-12 col-md-12 ph-checkout-shipping-row" id="phShippingMethods" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_SHIPPING_METHODS').'</div>';
	
	
	
	/*foreach($this->t['shippingmethods'] as $k => $v) {
		
		// Display the current one selected
		if (isset($this->t['cartitems']['shipping']) && (int)$this->t['cartitems']['shipping'] == (int)$v->id) {
			
			//$priceI = $price->getPriceItems($v->cost, $v->taxrate, $v->taxcalctype, $v->taxtitle, 1, 1);

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
	
		echo '<div class="col-sm-8 col-md-8 ">'.$this->t['shippingmethod']['title'];
		
		if ($this->t['display_shipping_desc'] && $this->t['shippingmethod']['description'] != '') {
			echo '<div class="ph-checkout-shipping-desc">'.JHTML::_('content.prepare', $this->t['shippingmethod']['description']).'</div>';
		}
		
		echo '</div>';
		
		echo '<div class="col-sm-4 col-md-4 ">';
		echo '<div class="pull-right ph-checkout-shipping-edit">';
		echo '<button class="btn btn-success btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-edit"></span> '.JText::_('COM_PHOCACART_EDIT_SHIPPING').'</button>';
		echo '</div>';
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



	// Header
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header" id="phcheckoutshippingedit"><div class="pull-right"><span class="glyphicon glyphicon-remove-circle ph-checkout-icon-not-ok"></span></div><h3>'.$this->t['ns'].'. '.JText::_('COM_PHOCACART_SHIPPING_OPTIONS').'</h3></div>';
	echo '</div><div class="ph-cb"></div>';
	
	
	echo '<div class="ph-checkout-box-action">';
	echo '<div id="ph-request-message" style="display:none"></div>';
	
	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phCheckoutAddress">';
	
	echo '<div class="col-sm-12 col-md-12 ph-checkout-shipping-row" id="phShippingMethods" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_SHIPPING_METHODS').'</div>';
	
	echo '<div class="ph-checkout-shipping-cost-box">';
	
	foreach($this->t['shippingmethods'] as $k => $v) {
		
		$checked = '';
		if (isset($this->t['cartitems']['shipping']) && (int)$this->t['cartitems']['shipping'] == (int)$v->id) {
			$checked = 'checked="checked"';
		}
		$priceI = $price->getPriceItems($v->cost, $v->taxrate, $v->taxcalctype, $v->taxtitle, 1);

		
		echo '<div class="col-sm-6 col-md-6 ">';
		echo '<div class="radio">';
		echo '<label><input type="radio" name="phshippingopt" id="phshippingopt'.$v->id.'" value="'.$v->id.'" '.$checked.' >'.$v->title.'</label>';
		echo '</div>';
		if ($this->t['display_shipping_desc'] && $v->description != '') {
			echo '<div class="ph-checkout-shipping-desc">'.JHTML::_('content.prepare', $v->description).'</div>';
		}
		echo '</div>';
		
		echo '<div class="col-sm-6 col-md-6"><div class="radio">';
		
		if (isset($priceI['nettoformat']) && isset($priceI['bruttoformat']) && $priceI['nettoformat'] == $priceI['bruttoformat']) {
	
		} else if (isset($priceI['nettoformat']) && $priceI['nettoformat'] != '' && isset($priceI['nettotxt']) && $priceI['nettotxt'] != '') {
			//echo '<div class="col-sm-6 col-md-6"></div>';
			echo '<div class="col-sm-8 col-md-8">'.$priceI['nettotxt'].'</div>';
			echo '<div class="col-sm-4 col-md-4 ph-checkout-shipping-netto">'.$priceI['nettoformat'].'</div>';
		}
		
		if (isset($priceI['taxformat']) && $priceI['taxformat'] != '' && isset($priceI['taxtxt']) && $priceI['taxtxt'] != '') {
			//echo '<div class="col-sm-6 col-md-6"></div>';
			echo '<div class="col-sm-8 col-md-8">'.$priceI['taxtxt'].'</div>';
			echo '<div class="col-sm-4 col-md-4 ph-checkout-shipping-tax">'.$priceI['taxformat'].'</div>';
		}
		
		if (isset($priceI['bruttoformat']) && $priceI['bruttoformat'] != '' && isset($priceI['bruttotxt']) && $priceI['bruttotxt'] != '') {
			//echo '<div class="col-sm-6 col-md-6"></div>';
			echo '<div class="col-sm-8 col-md-8">'.$priceI['bruttotxt'].'</div>';
			echo '<div class="col-sm-4 col-md-4 ph-checkout-shipping-brutto">'.$priceI['bruttoformat'].'</div>';
		}
		echo '</div></div>';
	
		echo '<div class="ph-cb">&nbsp;</div>';
		
	}
	echo '</div>';// end shipping cost box
	
	echo '<div class="ph-cb"></div>';

	echo '<div class="pull-right ph-checkout-shipping-save">';
		echo '<button class="btn btn-primary btn-sm ph-btn" role="button"><span class="glyphicon glyphicon-floppy-disk"></span> '.JText::_('COM_PHOCACART_SAVE').'</button>';
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
	echo '<div class="col-sm-12 col-md-12 ph-checkout-box-row" >';
	echo '<div class="ph-checkout-box-header-pas"><div class="pull-right"><span class="glyphicon glyphicon-remove-circle ph-checkout-icon-not-ok"></span></div><h3>'.$this->t['ns'].'. '.JText::_('COM_PHOCACART_SHIPPING_OPTIONS').'</h3></div>';
	echo '</div><div class="ph-cb"></div>';
}
	
	

?>