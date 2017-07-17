<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

class PhocacartOrderRender
{
	public function __construct() {}
	
	public function render($id, $type = 1, $format = 'html', $token = '') {
		
	
		if ($id < 1) {
			return JText::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND');
		}
		
		 
		
		
		// Type 1 ... order/receipt, 2 ... invoice, 3 ... delivery note
		// Format 1 ... html site / html email 2 ... pdf 3 ... rss
		$app					= JFactory::getApplication();
		$paramsC 				= PhocacartUtils::getComponentParameters();
		$store_title			= $paramsC->get( 'store_title', '' );
		$store_logo				= $paramsC->get( 'store_logo', '' );
		$store_info				= $paramsC->get( 'store_info', '' );
		$store_info				= PhocacartRenderFront::renderArticle($store_info);
		$invoice_prefix			= $paramsC->get( 'invoice_prefix', '');
		$invoice_number_format	= $paramsC->get( 'invoice_number_format', '{prefix}{orderdate}{orderid}');
		$invoice_number_chars	= $paramsC->get( 'invoice_number_chars', 12);
		$invoice_tp				= $paramsC->get( 'invoice_terms_payment', '');
		
		JHTML::stylesheet('media/com_phocacart/css/main.css' );
		
		$order 		= new PhocacartOrderView();
		$common		= $order->getItemCommon($id);
		$app 		= JFactory::getApplication();
		
		// Not for admin
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
		$discounts	= $order->getItemProductDiscounts($id, 1);
		$total 		= $order->getItemTotal($id, 1);
		
		
		// FORMAT - HTML
		$box		= 'class="ph-idnr-box"';
		$table 		= 'class="ph-idnr-box-in"';
		$pho1 		= $pho12	= 'class="pho1"';
		$pho2 		= $pho22	= 'class="pho2"';
		$pho3 		= $pho32	= 'class="pho3"';
		$pho4 		= $pho42	= 'class="pho4"';
		$pho5 		= $pho52	= 'class="pho5"';
		$pho6 		= $pho62	= 'class="pho6"';
		$pho7 		= $pho72	= 'class="pho7"';
		$pho6Sep 	= $pho6Sep2	= 'class="pho6 ph-idnr-sep"';
		$pho7Sep 	= $pho7Sep2	= 'class="pho7 ph-idnr-sep"';
		$pho8 		= $pho82	= 'class="pho8"';
		$pho9 		= $pho92	= 'class="pho9"';
		$pho10 		= $pho102	= 'class="pho10"';
		$pho11 		= $pho112	= 'class="pho11"';
		$pho12 		= $pho122	= 'class="pho12"';
		$sep		= $sep2		= 'class="ph-idnr-sep"';
		$bBox		= 'class="ph-idnr-billing-box"';
		$bBoxIn		= 'class="ph-idnr-billing-box-in"';
		$sBox		= 'class="ph-idnr-shipping-box"';
		$sBoxIn		= 'class="ph-idnr-shipping-box-in"';
		$boxIn 		= 'class="ph-idnr-box-in"';
		$hProduct 	= 'class="ph-idnr-header-product"';
		$bProduct	= 'class="ph-idnr-body-product"';
		$sepH		= 'class="ph-idnr-sep-horizontal"';
		$totalF		= 'class="ph-idnr-total"';
		$toPayS		= 'class="ph-idnr-to-pay"';
		$toPaySV	= 'class="ph-idnr-to-pay-value"';
		$firstRow	= '';
		
		if ($format == 'pdf') {
			$box		= '';
			$table 		= 'style="width: 100%; font-family: sans-serif, arial; font-size: 80%;padding:3px;margin-top:-200px"';
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
			
			$pho12 		= 'style="width: 9%;"';
			$pho22 		= 'style="width: 9%;"';
			$pho32 		= 'style="width: 9%;"';
			$pho42 		= 'style="width: 9%;"';
			$pho52 		= 'style="width: 9%;"';
			$pho62 		= 'style="width: 9%;"';
			$pho72 		= 'style="width: 9%;"';
			$pho6Sep2 	= 'style="width: 5%;"';
			$pho7Sep2 	= 'style="width: 5%;"';
			$pho82 		= 'style="width: 9%;"';
			$pho92 		= 'style="width: 9%;"';
			$pho102 	= 'style="width: 9%;"';
			$pho112 	= 'style="width: 9%;"';
			$pho122 	= 'style="width: 9%;"';
			$seps2		= 'style="width: 10%;"';
			
			
			$bBox		= 'style="border: 1pt solid #dddddd;"';
			$bBoxIn		= 'style=""';
			$sBox		= 'style="border: 1pt solid #dddddd;"';
			$sBoxIn		= 'style=""';
			$boxIn 		= 'style="width: 100%; font-family: sans-serif, arial; font-size: 60%;padding:3px 1px;"';
			$hProduct 	= 'style="white-space:nowrap;font-weight: bold;background-color: #dddddd;"';
			$bProduct	= 'style="white-space:nowrap;"';
			$sepH		= 'style="border-top: 1pt solid #dddddd;"';
			$totalF		= 'style=""';
			$toPayS		= 'style="background-color: #eeeeee;padding: 20px;"';
			$toPaySV	= 'style="background-color: #eeeeee;padding: 20px;text-align:right;"';
			$firstRow	= 'style="font-size:0pt;"';
		
		} else if ($format == 'mail') {
			
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
			
			$pho12 		= 'style="width: 9%;"';
			$pho22 		= 'style="width: 9%;"';
			$pho32 		= 'style="width: 9%;"';
			$pho42 		= 'style="width: 9%;"';
			$pho52 		= 'style="width: 9%;"';
			$pho62 		= 'style="width: 9%;"';
			$pho72 		= 'style="width: 9%;"';
			$pho6Sep2 	= 'style="width: 5%;"';
			$pho7Sep2 	= 'style="width: 5%;"';
			$pho82 		= 'style="width: 9%;"';
			$pho92 		= 'style="width: 9%;"';
			$pho102 	= 'style="width: 9%;"';
			$pho112 	= 'style="width: 9%;"';
			$pho122 	= 'style="width: 9%;"';
			$seps2		= 'style="width: 10%;"';
			
			$bBox		= 'style="border: 1px solid #ddd;padding: 10px;"';
			$bBoxIn		= 'style=""';
			$sBox		= 'style="border: 1px solid #ddd;padding: 10px;"';
			$sBoxIn		= 'style=""';
			$boxIn 		= 'style="width: 100%; font-family: sans-serif, arial; font-size: 90%;"';
			$hProduct 	= 'style="white-space:nowrap;padding: 5px;font-weight: bold;background: #ddd;"';
			$bProduct	= 'style="white-space:nowrap;padding: 5px;"';
			$sepH		= 'style="border-top: 1px solid #ddd;"';
			$totalF		= 'style=""';
			$toPayS		= 'style="background-color: #eeeeee;padding: 20px;"';
			$toPaySV	= 'style="background-color: #eeeeee;padding: 20px;text-align:right;"';
			$firstRow	= '';	
			
		}
		
		
		$o = array();
		$o[] = '<div '.$box.'>';
		$o[] = '<table '.$table.'>';
		
		$o[] = '<tr '.$firstRow.'>';
		$o[] = '<td '.$pho12.'>&nbsp;</td><td '.$pho22.'>&nbsp;</td><td '.$pho32.'>&nbsp;</td><td '.$pho42.'>&nbsp;</td>';	
		$o[] = '<td '.$pho52.'>&nbsp;</td><td '.$pho6Sep2.'>&nbsp;</td><td '.$pho7Sep2.'>&nbsp;</td><td '.$pho82.'>&nbsp;</td>';	
		$o[] = '<td '.$pho92.'>&nbsp;</td><td '.$pho102.'>&nbsp;</td><td '.$pho112.'>&nbsp;</td><td '.$pho122.'>&nbsp;</td>';	
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
		
		$o[] = '<td colspan="2" '.$sep2.'></td>';
		
		// --------
		// HEADER RIGHT
		$o[] = '<td colspan="5">';
		if ($type == 1) {
			$o[] = '<div><h1>'.JText::_('COM_PHOCACART_ORDER').'</h1></div>';
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_ORDER_NR').'</b>: '.PhocacartOrder::getOrderNumber($common->id).'</div>';
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_ORDER_DATE').'</b>: '.JHtml::date($common->date, 'd. m. Y').'</div>';
		} else if ($type == 2) {
		
			$o[] = '<div><h1>'.JText::_('COM_PHOCACART_INVOICE').'</h1></div>';
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_INVOICE_NR').'</b>: '.PhocacartOrder::getInvoiceNumber($common->id, $common->date, $invoice_prefix, $invoice_number_format, $invoice_number_chars).'</div>';
			$o[] = '<div><b>'.JText::_('COM_PHOCACART_ORDER_DATE').'</b>: '.JHtml::date($common->date, 'd. m. Y').'</div>';
			
	
		
		} else if ($type == 3) {
			$o[] = '<div><h1>'.JText::_('COM_PHOCACART_DELIVERY_NOTE').'</h1></div>';
			$o[] = '<div style="margin:0;"><b>'.JText::_('COM_PHOCACART_ORDER_NR').'</b>: '.PhocacartOrder::getOrderNumber($common->id).'</div>';
			$o[] = '<div style="margin:0"><b>'.JText::_('COM_PHOCACART_ORDER_DATE').'</b>: '.JHtml::date($common->date, 'd. m. Y').'</div>';
		
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
		$ob = array();
		if (!empty($bas['b'])) {
			$v = $bas['b'];
			if ($v['company'] != '') { $ob[] = '<b>'.$v['company'].'</b><br />';}
			$name = array();
			if ($v['name_degree'] != '') { $name[] = $v['name_degree'];}
			if ($v['name_first'] != '') { $name[] = $v['name_first'];}
			if ($v['name_first'] != '') { $name[] = $v['name_middle'];}
			if ($v['name_first'] != '') { $name[] = $v['name_last'];}
			if (!empty($name)) {$ob[] = '<b>' . implode("\n", $name).'</b><br />';}
			if ($v['address_1'] != '') { $ob[] = $v['address_1'].'<br />';}
			if ($v['address_2'] != '') { $ob[] = $v['address_2'].'<br />';}
			$city = array();
			if ($v['zip'] != '') { $city[] = $v['zip'];}
			if ($v['city'] != '') { $city[] = $v['city'];}
			if (!empty($city)) {$ob[] = implode("\n", $city).'<br />';}
			//echo '<br />';
			if (!empty($v['regiontitle'])) {$ob[] = $v['regiontitle'].'<br />';}
			if (!empty($v['countrytitle'])) {$ob[] = $v['countrytitle'].'<br />';}
			//echo '<br />';
			if ($v['vat_1'] != '') { $ob[] = '<br />'.JText::_('COM_PHOCACART_VAT1').': '. $v['vat_1'].'<br />';}
			if ($v['vat_2'] != '') { $ob[] = JText::_('COM_PHOCACART_VAT2').': '.$v['vat_2'].'<br />';}
		}
		
		// --------
		// SHIPPING
		$os = array();
		if (!empty($bas['s'])) {
			$v = $bas['s'];
			if ($v['company'] != '') { $os[] = '<b>'.$v['company'].'</b><br />';}
			$name = array();
			if ($v['name_degree'] != '') { $name[] = $v['name_degree'];}
			if ($v['name_first'] != '') { $name[] = $v['name_first'];}
			if ($v['name_first'] != '') { $name[] = $v['name_middle'];}
			if ($v['name_first'] != '') { $name[] = $v['name_last'];}
			if (!empty($name)) {$os[] = '<b>' . implode("\n", $name).'</b><br />';}
			if ($v['address_1'] != '') { $os[] = $v['address_1'].'<br />';}
			if ($v['address_2'] != '') { $os[] = $v['address_2'].'<br />';}
			$city = array();
			if ($v['zip'] != '') { $city[] = $v['zip'];}
			if ($v['city'] != '') { $city[] = $v['city'];}
			if (!empty($city)) {$os[] = implode("\n", $city).'<br />';}
			//echo '<br />';
			if (!empty($v['regiontitle'])) {$os[] = $v['regiontitle'].'<br />';}
			if (!empty($v['countrytitle'])) {$os[] = $v['countrytitle'].'<br />';}
			//echo '<br />';
			if ($v['vat_1'] != '') { $os[] = '<br />'.JText::_('COM_PHOCACART_VAT1').': '. $v['vat_1'].'<br />';}
			if ($v['vat_2'] != '') { $os[] = JText::_('COM_PHOCACART_VAT2').': '.$v['vat_2'].'<br />';}
		}
		
		
		
		// BILLING OUTPUT
		$o[] = '<tr><td colspan="5" '.$bBox.' ><div '.$bBoxIn.'>';
		$o[] = implode("\n", $ob);
		$o[] = '</div></td>';
		$o[] = '<td colspan="2"></td>';
		
		// SHIPPING OUTPUT
		$o[] = '<td colspan="5" '.$sBox.'><div '.$sBoxIn.'>';
		if ((isset($bas['b']['ba_sa']) && $bas['b']['ba_sa'] == 1) || (isset($bas['s']['ba_sa']) && $bas['s']['ba_sa'] == 1)) {
			$o[] = implode("\n", $ob);
		} else {
			$o[] = implode("\n", $os);
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
		
		$price = new PhocacartPrice();
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
					
					/*if ($dDiscount == 1) {
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
					}*/
					$netto = (int)$v->quantity * $v->netto;
					$p[] = '<td style="text-align:right" colspan="2">'.$price->getPriceFormat($netto).'</td>';
					/*
					if ($v->dtax > 0) {
						$tax = (int)$v->quantity * $v->dtax;
					} else {
						$tax = (int)$v->quantity * $v->tax;
					}*/
					$tax = (int)$v->quantity * $v->tax;
					$p[] = '<td style="text-align:right" colspan="1">'.$price->getPriceFormat($tax).'</td>';
					/*
					if ($v->dbrutto > 0) {
						$brutto = (int)$v->quantity * $v->dbrutto;
					} else {
						$brutto = (int)$v->quantity * $v->brutto;
					}*/
					$brutto = (int)$v->quantity * $v->brutto;
					$p[] = '<td style="text-align:right" colspan="2">'.$price->getPriceFormat($brutto).'</td>';
					
				}
				$p[] = '</tr>';
				
				if (!empty($v->attributes)) {
					$p[] = '<tr>';
					$p[] = '<td></td>';
					$p[] = '<td colspan="3" align="left"><ul class="ph-idnr-ul">';
					foreach ($v->attributes as $k2 => $v2) {
						$p[] = '<li><span class="ph-small ph-cart-small-attribute ph-idnr-li">'.$v2->attribute_title .' '.$v2->option_title.'</span></li>';
					}
					$p[] = '</ul></td>';
					$p[] = '<td colspan="8"></td>';
					$p[] = '</tr>';
				}
				
				if (!empty($discounts[$v->product_id_key])) {
					foreach($discounts[$v->product_id_key] as $k3 => $v3) {
						
						$p[] = '<tr '.$bProduct.'>';
						$p[] = '<td></td>';
						$p[] = '<td colspan="'.$cTitle.'">'.$v3->title.'</td>';
						$p[] = '<td style="text-align:center"></td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$price->getPriceFormat($v3->netto).'</td>';
						$netto3 = (int)$v->quantity * $v3->netto;
						$p[] = '<td style="text-align:right" colspan="2">'.$price->getPriceFormat($netto3).'</td>';
						$tax3 = (int)$v->quantity * $v3->tax;
						$p[] = '<td style="text-align:right" colspan="1">'.$price->getPriceFormat($tax3).'</td>';
						$brutto3 = (int)$v->quantity * $v3->brutto;
						$p[] = '<td style="text-align:right" colspan="2">'.$price->getPriceFormat($brutto3).'</td>';
						$p[] = '</tr>';
						
					}
				}
			
			}
		
		}
		
		$o[] = implode("\n", $p);
		
		$o[] = '<tr><td colspan="12" '.$sepH.'>&nbsp;</td></tr>';

		$t = array();
		$toPay = 0;
		
		if (!empty($total)) {
			foreach($total as $k => $v) {
				
				
				
				
				if($v->amount == 0 && $v->amount_currency == 0 && $v->type != 'brutto') {
					// Don't display coupon if null
					
				} else if ($v->type == 'netto') {
					$t[] = '<tr '.$totalF.'>';
					$t[] = '<td colspan="7"></td>';
					$t[] = '<td colspan="3"><b>'.$v->title.'</b></td>';
					$t[] = '<td style="text-align:right" colspan="2"><b>'.$price->getPriceFormat($v->amount).'</b></td>';
					$t[] = '</tr>';
				} else if ($v->type == 'brutto') {
					
					// Brutto or Brutto currency
					$amount = (isset($v->amount_currency) && $v->amount_currency > 0) ? $price->getPriceFormat($v->amount_currency, 0, 1) : $price->getPriceFormat($v->amount);
					
					$t[] = '<tr '.$totalF.'>';
					$t[] = '<td colspan="7"></td>';
					$t[] = '<td colspan="3"><b>'.$v->title.'</b></td>';
					$t[] = '<td style="text-align:right" colspan="2"><b>'.$amount.'</b></td>';
					$t[] = '</tr>';
				} else if ($v->type == 'rounding') {
					
					// Rounding or rounding currency
					$amount = (isset($v->amount_currency) && $v->amount_currency > 0) ? $price->getPriceFormat($v->amount_currency, 0, 1) : $price->getPriceFormat($v->amount);
					
					$t[] = '<tr '.$totalF.'>';
					$t[] = '<td colspan="7"></td>';
					$t[] = '<td colspan="3">'.$v->title.'</td>';
					$t[] = '<td style="text-align:right" colspan="2">'.$amount.'</td>';
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
			$o[] = '<td colspan="3" '.$toPayS.'><b>'.JText::_('COM_PHOCACART_TO_PAY').'</b></td>';
			$o[] = '<td colspan="2" '.$toPaySV.'><b>'.$price->getPriceFormat($toPay).'</b></td>';
			$o[] = '</tr>';
		}
		
		
		$o[] = '</table>';// End box in
		$o[] = '</div>';// End box
		
		
		
		
		
		$o2 = implode("\n", $o);
		return $o2;
	}
}

?>