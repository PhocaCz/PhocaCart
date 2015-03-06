<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartOrderRender
{
	public function __construct() {}
	
	public function render($id, $type = 1, $format = 1, $token = '') {
		
		if ($id < 1) {
			return 'NO ORDER FOUND';
		}
		
		 
		
		
		// Type 1 ... order/receipt, 2 ... invoice, 3 ... delivery note
		// Format 1 ... html site / html email 2 ... pdf 3 ... rss
		$paramsC 		= JComponentHelper::getParams('com_phocacart');
		$store_title	= $paramsC->get( 'store_title', '' );
		$store_logo		= $paramsC->get( 'store_logo', '' );
		$store_info		= $paramsC->get( 'store_info', '' );
		$invoice_prefix	= $paramsC->get( 'invoice_prefix', '');
		$invoice_tp		= $paramsC->get( 'invoice_terms_payment', '');
		
		JHTML::stylesheet('media/com_phocacart/css/main.css' );
		
		$order 		= new PhocaCartOrderView();
		$common		= $order->getItemCommon($id);
		
		$app 	= JFactory::getApplication();
		if (!$app->isAdmin()){
			$user = JFactory::getUser();
			
			
			if ((int)$user->id < 1 && $token == '') {
				die (JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
			}
			if (!isset($common->user_id) && $token == '') {
				die (JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
			}
			if ($user->id != $common->user_id && $token == '') {
				die (JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
			}
			if ((int)$user->id < 1 && $token != '' && ($token != $common->order_token)) {
				die (JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND'));
			}
		}
		
		$bas		= $order->getItemBaS($id, 1);
		
		$products 	= $order->getItemProducts($id);
		$total 		= $order->getItemTotal($id);
		
		
		// FORMAT - HTML
		$box		= 'class="ph-idnr-box"';
		$table 		= 'class="ph-idnr-box-in"';
		$pho1 		= 'class="pho1"';
		$pho2 		= 'class="pho2"';
		$pho3 		= 'class="pho3"';
		$pho4 		= 'class="pho4"';
		$pho5 		= 'class="pho5"';
		$pho6 		= 'class="pho6"';
		$pho7 		= 'class="pho7"';
		$pho6Sep 	= 'class="pho6 ph-idnr-sep"';
		$pho7Sep 	= 'class="pho7 ph-idnr-sep"';
		$pho8 		= 'class="pho8"';
		$pho9 		= 'class="pho9"';
		$pho10 		= 'class="pho10"';
		$pho11 		= 'class="pho11"';
		$pho12 		= 'class="pho12"';
		$sep		= 'class="ph-idnr-sep"';
		$bBox		= 'class="ph-idnr-billing-box"';
		$bBoxIn		= 'class="ph-idnr-billing-box-in"';
		$sBox		= 'class="ph-idnr-shipping-box"';
		$sBoxIn		= 'class="ph-idnr-shipping-box-in"';
		$boxIn 		= 'class="ph-idnr-box-in"';
		$hProduct 	= 'class="ph-idnr-header-product"';
		$bProduct	= 'class="ph-idnr-body-product"';
		$sepH		= 'class="ph-idnr-sep-horizontal"';
		$totalF		= 'class="ph-idnr-total"';
		$toPay		= 'class="ph-idnr-to-pay"';
		
		if ($format == 2) {
			$box		= '';
			$table 		= 'style="width: 100%; font-family: sans-serif, arial; font-size: 90%;"';
			$pho1 		= 'style="width: 8.3333%;"';
			$pho2 		= 'style="width: 8.3333%;"';
			$pho3 		= 'style="width: 8.3333%;"';
			$pho4 		= 'style="width: 8.3333%;"';
			$pho5 		= 'style="width: 8.3333%;"';
			$pho6 		= 'style="width: 8.3333%;"';
			$pho7 		= 'style="width: 8.3333%;"';
			$pho6Sep 	= 'style="width: 3%;"';
			$pho7Sep 	= 'style="width: 3%;"';
			$pho8 		= 'style="width: 8.3333%;"';
			$pho9 		= 'style="width: 8.3333%;"';
			$pho10 		= 'style="width: 8.3333%;"';
			$pho11 		= 'style="width: 8.3333%;"';
			$pho12 		= 'style="width: 8.3333%;"';
			$sep		= 'style="width: 3%;"';
			$bBox		= 'style="border: 1px solid #ddd;padding: 10px;"';
			$bBoxIn		= 'style=""';
			$sBox		= 'style="border: 1px solid #ddd;padding: 10px;"';
			$sBoxIn		= 'style=""';
			$boxIn 		= 'style="width: 100%; font-family: sans-serif, arial; font-size: 90%;"';
			$hProduct 	= 'style="white-space:nowrap;padding: 5px;font-weight: bold;background: #ddd;"';
			$bProduct	= 'style="white-space:nowrap;padding: 5px;"';
			$sepH		= 'style="border-top: 1px solid #ddd;"';
			$totalF		= 'style=""';
			$toPay		= 'style="background: #ddd;padding: 20px;"';
		
		}
		
		
		$o = array();
		$o[] = '<div '.$box.'>';
		$o[] = '<table '.$table.'>';
		
		$o[] = '<tr>';
		$o[] = '<td '.$pho1.'>&nbsp;</td><td '.$pho2.'>&nbsp;</td><td '.$pho3.'>&nbsp;</td><td '.$pho4.'>&nbsp;</td>';	
		$o[] = '<td '.$pho5.'>&nbsp;</td><td '.$pho6Sep.'>&nbsp;</td><td '.$pho7Sep.'>&nbsp;</td><td '.$pho8.'>&nbsp;</td>';	
		$o[] = '<td '.$pho9.'>&nbsp;</td><td '.$pho10.'>&nbsp;</td><td '.$pho11.'>&nbsp;</td><td '.$pho12.'>&nbsp;</td>';	
		$o[] = '</tr>';
		
		// --------
		// HEADER LEFT
		$o[] = '<tr><td colspan="5">';
		if ($store_title != '') {
			$o[] = '<div><h1>'.$store_title.'</h1></div>';
		}
		if ($store_logo != '') {
			$o[] = '<div><img class="ph-idnr-header-img" src="'.JURI::root(false). ''.$store_logo.'" /></div>';
		}
		if ($store_info != '') {
			$o[] = '<div>'.$store_info.'</div>';
		}
		$o[] = '</td>';
		
		$o[] = '<td colspan="2" '.$sep.'></td>';
		
		// --------
		// HEADER RIGHT
		$o[] = '<td colspan="5">';
		if ($type == 1) {
			$o[] = '<div><h1>'.JText::_('COM_PHOCACART_ORDER').'</h1></div>';
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_ORDER_NUMBER').'</b>: '.PhocaCartOrder::getOrderNumber($common->id).'</div>';
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_ORDER_DATE').'</b>: '.JHtml::date($common->date, 'd. m. Y').'</div>';
		} else if ($type == 2) {
		
			$o[] = '<div><h1>'.JText::_('COM_PHOCACART_INVOICE').'</h1></div>';
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_INVOICE_NUMBER').'</b>: '.PhocaCartOrder::getInvoiceNumber($common->id, $invoice_prefix).'</div>';
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_ORDER_DATE').'</b>: '.JHtml::date($common->date, 'd. m. Y').'</div>';
			
	
		
		} else if ($type == 3) {
			$o[] = '<div><h1>'.JText::_('COM_PHOCACART_DELIVERY_NOTE').'</h1></div>';
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_ORDER_NUMBER').'</b>: '.PhocaCartOrder::getOrderNumber($common->id).'</div>';
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_ORDER_DATE').'</b>: '.JHtml::date($common->date, 'd. m. Y').'</div>';
		
		}
		$o[] = '<div>&nbsp;</div>';
		if (isset($common->paymenttitle) && $common->paymenttitle != '') {
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_PAYMENT').'</b>: '.$common->paymenttitle.'</div>';
		}
		
		if ($type == 2 && $invoice_tp	!= '') {
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_TERMS_OF_PAYMENT').'</b>: '.$invoice_tp.'</div>';
		}
		
		if (isset($common->shippingtitle) && $common->shippingtitle != '') {
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_SHIPPING').'</b>: '.$common->shippingtitle.'</div>';
		}
		
		$o[] = '</td></tr>';
		
		$o[] = '<tr><td colspan="12">&nbsp;</td></tr>';
		
		
		// --------
		// BILLING AND SHIPPING HEADER
		$o[] = '<tr><td colspan="5"><b>'.JText::_('COM_PHOCACART_BILLING_ADDRESS').'</b></td>';
		$o[] = '<td colspan="2"></td>';
		$o[] = '<td colspan="5"><b>'.JText::_('COM_PHOCACART_SHIPPING_ADDRESS').'</b></td></tr>';
		
		// --------
		// BILLING
		$o[] = '<tr><td colspan="5" '.$bBox.' ><div '.$bBoxIn.'>';
		
		if (!empty($bas['b'])) {
			$v = $bas['b'];
			if ($v['company'] != '') { $o[] = '<b>'.$v['company'].'</b><br />';}
			$name = array();
			if ($v['name_degree'] != '') { $name[] = $v['name_degree'];}
			if ($v['name_first'] != '') { $name[] = $v['name_first'];}
			if ($v['name_first'] != '') { $name[] = $v['name_middle'];}
			if ($v['name_first'] != '') { $name[] = $v['name_last'];}
			if (!empty($name)) {$o[] = '<b>' . implode("\n", $name).'</b><br />';}
			if ($v['address_1'] != '') { $o[] = $v['address_1'].'<br />';}
			if ($v['address_2'] != '') { $o[] = $v['address_2'].'<br />';}
			$city = array();
			if ($v['zip'] != '') { $city[] = $v['zip'];}
			if ($v['city'] != '') { $city[] = $v['city'];}
			if (!empty($city)) {$o[] = implode("\n", $city).'<br />';}
			echo '<br />';
			if (!empty($v['regiontitle'])) {$o[] = $v['regiontitle'].'<br />';}
			if (!empty($v['countrytitle'])) {$o[] = $v['countrytitle'].'<br />';}
			echo '<br />';
			if ($v['vat_1'] != '') { $o[] = '<br />'.JText::_('COM_PHOCACART_VAT1').': '. $v['vat_1'].'<br />';}
			if ($v['vat_2'] != '') { $o[] = JText::_('COM_PHOCACART_VAT2').': '.$v['vat_2'].'<br />';}
		}
			
		$o[] = '</div></td>';
		
		$o[] = '<td colspan="2"></td>';
		
		// --------
		// SHIPPING
		$o[] = '<td colspan="5" '.$sBox.'><div '.$sBoxIn.'>';
		
		if (!empty($bas['s'])) {
			$v = $bas['s'];
			if ($v['company'] != '') { $o[] = '<b>'.$v['company'].'</b><br />';}
			$name = array();
			if ($v['name_degree'] != '') { $name[] = $v['name_degree'];}
			if ($v['name_first'] != '') { $name[] = $v['name_first'];}
			if ($v['name_first'] != '') { $name[] = $v['name_middle'];}
			if ($v['name_first'] != '') { $name[] = $v['name_last'];}
			if (!empty($name)) {$o[] = '<b>' . implode("\n", $name).'</b><br />';}
			if ($v['address_1'] != '') { $o[] = $v['address_1'].'<br />';}
			if ($v['address_2'] != '') { $o[] = $v['address_2'].'<br />';}
			$city = array();
			if ($v['zip'] != '') { $city[] = $v['zip'];}
			if ($v['city'] != '') { $city[] = $v['city'];}
			if (!empty($city)) {$o[] = implode("\n", $city).'<br />';}
			echo '<br />';
			if (!empty($v['regiontitle'])) {$o[] = $v['regiontitle'].'<br />';}
			if (!empty($v['countrytitle'])) {$o[] = $v['countrytitle'].'<br />';}
			echo '<br />';
			if ($v['vat_1'] != '') { $o[] = '<br />'.JText::_('COM_PHOCACART_VAT1').': '. $v['vat_1'].'<br />';}
			if ($v['vat_2'] != '') { $o[] = JText::_('COM_PHOCACART_VAT2').': '.$v['vat_2'].'<br />';}
		}
			
		$o[] = '</div></td></tr>';
		
		$o[] = '<tr><td colspan="12">&nbsp;</td></tr>';
		
		
		// Second area
		$o[] = '</table>';
		
		$o[] = '<table '.$boxIn.'>';
		$o[] = '<tr>';
		$o[] = '<td '.$pho1.'>&nbsp;</td><td '.$pho2.'>&nbsp;</td><td '.$pho3.'>&nbsp;</td><td '.$pho4.'>&nbsp;</td>';	
		$o[] = '<td '.$pho5.'>&nbsp;</td><td '.$pho6.'>&nbsp;</td><td '.$pho7.'>&nbsp;</td><td '.$pho8.'>&nbsp;</td>';	
		$o[] = '<td '.$pho9.'>&nbsp;</td><td '.$pho10.'>&nbsp;</td><td '.$pho11.'>&nbsp;</td><td '.$pho12.'>&nbsp;</td>';	
		$o[] = '</tr>';
		
		
		$dDiscount 	= 0; // Display Discount (Coupon, cnetto)
		$cTitle		= 3; // Colspan Title
		
		$price = new PhocaCartPrice();
		$price->setCurrency($common->currency_id);
		$p = array();
		if (!empty($products)) {
		
			// Prepare header and body
			foreach ($products as $k => $v) {
				if ($v->damount > 0) {
					$dDiscount 	= 1;
					$cTitle 	= 2;
				}
			}
			if ($type == 3) {
				$cTitle	= 10;
			}
		
			$p[] = '<tr '.$hProduct.'>';
			$p[] = '<td>'.JText::_('COM_PHOCACART_SKU').'</td>';
			$p[] = '<td colspan="'.$cTitle.'">'.JText::_('COM_PHOCACART_ITEM').'</td>';
			$p[] = '<td style="text-align:center">'.JText::_('COM_PHOCACART_QTY').'</td>';
			
			if ($type != 3) {
				$p[] = '<td style="text-align:right" colspan="2">'.JText::_('COM_PHOCACART_PRICE_UNIT').'</td>';
				if ($dDiscount == 1) {
					$p[] = '<td style="text-align:center"">'.JText::_('COM_PHOCACART_DISCOUNT').'</td>';
				}
				$p[] = '<td style="text-align:right" colspan="2">'.JText::_('COM_PHOCACART_PRICE_EXCL_TAX').'</td>';
				$p[] = '<td style="text-align:right">'.JText::_('COM_PHOCACART_TAX').'</td>';
				$p[] = '<td style="text-align:right" colspan="2">'.JText::_('COM_PHOCACART_PRICE_INCL_TAX').'</td>';
			}	
			$p[] = '</tr>';
			
			foreach($products as $k => $v) {
				$p[] = '<tr '.$bProduct.'>';
				$p[] = '<td>'.$v->sku.'</td>';
				$p[] = '<td colspan="'.$cTitle.'">'.$v->title.'</td>';
				$p[] = '<td style="text-align:center">'.$v->quantity.'</td>';
				
				if ($type != 3) {
					$p[] = '<td style="text-align:right" colspan="2">'.$price->getPriceFormat($v->netto).'</td>';
					
					if ($dDiscount == 1) {
						if ($v->dtype == 0) {
							$dAmount = $v->netto - $v->dnetto;
							if ($dAmount > 0 && $v->dnetto > 0) {
								$dAmount = '- '. $price->getPriceFormat($dAmount);
							} else {
								$dAmount = '';
							}
						} else if ($v->dtype == 1) {
							if ($v->damount > 0) {
								$dAmount = $v->damount . ' %';
							} else {
								$dAmount = '';
							}
						}
						$p[] = '<td style="text-align:center">'.$dAmount.'</td>';
					}
					
					if ($v->dnetto > 0 ) {
						$netto = (int)$v->quantity * $v->dnetto;
					} else {
						$netto = (int)$v->quantity * $v->netto;
					}
					$p[] = '<td style="text-align:right" colspan="2">'.$price->getPriceFormat($netto).'</td>';
					
					if ($v->dtax > 0) {
						$tax = (int)$v->quantity * $v->dtax;
					} else {
						$tax = (int)$v->quantity * $v->tax;
					}
					$p[] = '<td style="text-align:right" colspan="1">'.$price->getPriceFormat($tax).'</td>';
					
					if ($v->dbrutto > 0) {
						$brutto = (int)$v->quantity * $v->dbrutto;
					} else {
						$brutto = (int)$v->quantity * $v->brutto;
					}
					$p[] = '<td style="text-align:right" colspan="2">'.$price->getPriceFormat($brutto).'</td>';
				}
				$p[] = '</tr>';
				
				if (!empty($v->attributes)) {
					$p[] = '<tr>';
					//$p[] = '<td></td>';
					$p[] = '<td colspan="3" align="left"><ul class="ph-idnr-ul">';
					foreach ($v->attributes as $k2 => $v2) {
						$p[] = '<li><span class="ph-small ph-cart-small-attribute ph-idnr-li">'.$v2->attribute_title .' '.$v2->option_title.'</span></li>';
					}
					$p[] = '</ul></td>';
					$p[] = '<td colspan="9"></td>';
					$p[] = '</tr>';
				}
			
			}
		
		}
		
		$o[] = implode("\n", $p);
		
		$o[] = '<tr><td colspan="12" '.$sepH.'>&nbsp;</td></tr>';

		$toPay = 0;
		if (!empty($total)) {
			foreach($total as $k => $v) {
				
				
				
				
				if($v->amount == 0) {
					// Don't display coupon if null
					
				} else if ($v->type == 'netto' || $v->type == 'brutto') {
					$t[] = '<tr '.$totalF.'>';
					$t[] = '<td colspan="7"></td>';
					$t[] = '<td colspan="3"><b>'.$v->title.'</b></td>';
					$t[] = '<td style="text-align:right" colspan="2"><b>'.$price->getPriceFormat($v->amount).'</b></td>';
					$t[] = '</tr>';
				} else {
					$t[] = '<tr '.$totalF.'>';
					$t[] = '<td colspan="7"></td>';
					$t[] = '<td colspan="3">'.$v->title.'</td>';
					$t[] = '<td style="text-align:right" colspan="2">'.$price->getPriceFormat($v->amount).'</td>';
					$t[] = '</tr>';
				}
				
				
				if ($v->type == 'brutto' && $type == 2) {
					$toPay = $v->amount;
				}
			}
		}
		
		if ($type != 3) {
			$o[] = implode("\n", $t);
		}
		
		
		$o[] = '<tr><td colspan="12">&nbsp;</td></tr>';
		if ($toPay > 0) {
			$o[] = '<tr class="ph-idnr-to-pay-box">';
			$o[] = '<td colspan="7">&nbsp;</td>';
			$o[] = '<td colspan="3" '.$toPay.'><b>'.JText::_('COM_PHOCACART_TO_PAY').'</b></td>';
			$o[] = '<td style="text-align:right" colspan="2" '.$toPay.'><b>'.$price->getPriceFormat($toPay).'</b></td>';
			$o[] = '</tr>';
		}
		
		
		$o[] = '<table>';// End box in
		$o[] = '</div>';// End box
		
		
		
		
		
		$o2 = implode("\n", $o);
		return $o2;
	}
}

?>