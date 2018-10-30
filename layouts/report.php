<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
$d 		= $displayData;
$price 	= new PhocacartPrice();
$taxes 	= PhocacartTax::getAllTaxes();

$p = array();
$p['report_display_tax'] 		= $d['params']->get( 'report_display_tax', 1);
$p['report_display_rounding'] 	= $d['params']->get( 'report_display_rounding', 1);
$p['report_header'] 			= $d['params']->get( 'report_header', '');


// STYLE
$cRDoc 		= 'class="ph-report-doc"';
$cRHead 	= 'class="ph-report-header"';
$cRDate 	= 'class="ph-report-date"';
$cRT 		= 'class="ph-report-table"';
$cRTRH 		= 'class="ph-report-table-row-header"';
$cRTRHC 	= 'class="ph-report-table-row-header-col"';
$cRTRI 		= 'class="ph-report-table-row-items"';
$cRD 		= 'class="ph-report-date"';
$cRON		= 'class="ph-report-order-number"';
$cRC		= 'class="ph-report-customer"';
$cRB		= 'class="ph-report-bold"';
$cRCur		= 'class="ph-report-currency"';
$cRNetto	= 'class="ph-report-netto"';
$cRTax		= 'class="ph-report-tax"';
$cRBrutto	= 'class="ph-report-brutto"';
$cRRounding	= 'class="ph-report-rounding"';
$cRTotalR	= 'class="ph-report-total-row"';
$cRTotalC1	= 'class="ph-report-total-col1"';
$cRTotalC2	= 'class="ph-report-total-col2"';
switch($d['format']) {
	
	case 'raw':
		
		
		$cRDoc 		= 'style="font-family: sans-serif,arial;"';
		$cRHead		= 'style="padding: 3px;"';
		$cRDate		= 'style="padding: 3px;"';
		$cRT 		= 'style="border: 1px solid #f0f0f0;border-collapse:collapse;font-size:90%;width:100%"';
		$cRTRH 		= 'style="border: 1px solid #f0f0f0;padding:3px;"';
		$cRTRHC 	= 'style="border: 1px solid #f0f0f0;padding:3px;background: #f7f7f7;"';
		$cRTRI 		= 'style=""';
		$cRD 		= 'style="text-align:center;border: 1px solid #f0f0f0;padding:5px;"';
		$cRON		= 'style="text-align:center;border: 1px solid #f0f0f0;padding:5px;"';
		$cRC		= 'style="text-align:left;border: 1px solid #f0f0f0;padding:5px;"';
		$cRB		= 'style="font-weight:bold"';
		$cRCur		= 'style="text-align:center;border: 1px solid #f0f0f0;padding:5px;"';
		$cRNetto	= 'style="text-align:right;border: 1px solid #f0f0f0;padding:5px;"';
		$cRTax		= 'style="text-align:right;border: 1px solid #f0f0f0;padding:5px;"';
		$cRBrutto	= 'style="text-align:right;border: 1px solid #f0f0f0;padding:5px;"';
		$cRRounding	= 'style="text-align:right;border: 1px solid #f0f0f0;padding:5px;"';
		$cRTotalR	= 'style="background: #ffffbf;vertical-align:top;text-align:right;font-weight:bold;border: 1px solid #f0f0f0;padding:5px;"';
		$cRTotalC1	= 'style="text-align:left;vertical-align:left;border: 1px solid #f0f0f0;padding:5px;"';
		$cRTotalC2	= 'style="border: 1px solid #f0f0f0;padding:3px;"';
		
	break;
	
	case 'pdf':
		
		$cRDoc 		= 'style="font-family: sans-serif,arial;"';
		$cRHead		= 'style="font-family: sans-serif,arial;margin: 0pt;padding: 0pt;"';
		$cRDate		= 'style="font-family: sans-serif,arial;margin: 0pt;padding: 0pt;"';
		$cRT 		= 'style="border: 1pt solid #f0f0f0;border-collapse:collapse;font-size:60%;width:100%" cellpadding="1"';
		$cRTRH 		= 'style="border: 1pt solid #f0f0f0;"';
		$cRTRHC 	= 'style="border: 1pt solid #f0f0f0;background-color: #f7f7f7;font-size: 90%"';
		$cRTRI 		= 'style=""';
		$cRD 		= 'style="text-align:center;border: 1pt solid #f0f0f0;"';
		$cRON		= 'style="text-align:center;border: 1pt solid #f0f0f0;"';
		$cRC		= 'style="text-align:left;border: 1pt solid #f0f0f0;padding:5px;"';
		$cRB		= 'style="font-weight:bold"';
		$cRCur		= 'style="text-align:center;border: 1pt solid #f0f0f0;"';
		$cRNetto	= 'style="text-align:right;border: 1pt solid #f0f0f0;"';
		$cRTax		= 'style="text-align:right;border: 1pt solid #f0f0f0;"';
		$cRBrutto	= 'style="text-align:right;border: 1pt solid #f0f0f0;"';
		$cRRounding	= 'style="text-align:right;border: 1pt solid #f0f0f0;"';
		$cRTotalR	= 'style="background-color: #ffffbf;vertical-align:top;text-align:right;font-weight:bold;border: 1pt solid #f0f0f0;"';
		$cRTotalC1	= 'style="text-align:left;vertical-align:left;border: 1pt solid #f0f0f0;"';
		$cRTotalC2	= 'style="border: 1pt solid #f0f0f0;"';
		
	break;
		
	default:
	break;
}

echo '<div '.$cRDoc.'>';// start doc


if ($d['format'] == 'raw' || $d['format'] == 'pdf') {
	
	
	$header = PhocacartRenderFront::renderArticle($p['report_header'], $d['format']);
	if ($header != '') {
		echo '<div '.$cRHead.'>';
		echo $header;
		echo '</div>';
	}
	
	echo '<div '.$cRDate.'>';
	echo JText::_('COM_PHOCACART_DATE') . ': ';
	echo $d['date_from'] . ' - ' . $d['date_to'];
	echo '</div>'; 
	
	
	
}

// HEADER
echo '<table '.$cRT.'>';
echo '<tr '.$cRTRH.'>';
echo '<th '.$cRTRHC.'>'.JText::_('COM_PHOCACART_DATE').'</th>';
echo '<th '.$cRTRHC.'>'.JText::_('COM_PHOCACART_ORDER_NUMBER').'</th>';
echo '<th '.$cRTRHC.'>'.JText::_('COM_PHOCACART_CUSTOMER').'</th>';
echo '<th '.$cRTRHC.'>'.JText::_('COM_PHOCACART_PAYMENT').'</th>';
echo '<th '.$cRTRHC.'>'.JText::_('COM_PHOCACART_CURRENCY').'</th>';
if ($p['report_display_tax'] == 1) {
	echo '<th '.$cRTRHC.'>'.JText::_('COM_PHOCACART_AMOUNT_EXCLUDING_TAX').'</th>';
	echo '<th '.$cRTRHC.'>'.JText::_('COM_PHOCACART_AMOUNT_TAX').'</th>';
}
if ($p['report_display_rounding'] == 1) {
	echo '<th '.$cRTRHC.'>'.JText::_('COM_PHOCACART_ROUNDING').'</th>';
}
if ($p['report_display_tax'] == 1) {
	echo '<th '.$cRTRHC.'>'.JText::_('COM_PHOCACART_AMOUNT_INCLUDING_TAX').'</th>';
} else {
	echo '<th '.$cRTRHC.'>'.JText::_('COM_PHOCACART_AMOUNT').'</th>';
}
	
echo '</tr>';

// ITEMS

foreach($d['items'] as $k => $v) {
	
	echo '<tr '.$cRTRI.'>';
	
	// Date
	echo '<td '.$cRD.'>';
	$dateTime = new DateTime($v->date);
	echo $dateTime->format('Y-m-d');
	echo '</td>';
	
	// Order Number
	echo '<td '.$cRON.'>'.$v->order_number.'</td>';
	
	// Customer
	echo '<td '.$cRC.'>';
	echo isset($v->user_company) && $v->user_company != '' ? $v->user_company . '<br>' : '';
	echo isset($v->user_vat_1) && $v->user_vat_1 != '' ? '('.JText::_('COM_PHOCACART_VAT_NUMBER').': ' . $v->user_vat_1 . ')<br>' : '';
	echo '<span '.$cRB.'>' . $v->user_name_first . ' ' . $v->user_name_last . '</span><br>';
	
	echo isset($v->user_address_1) && $v->user_address_1 != '' ? $v->user_address_1 . '<br>' : '';
	echo isset($v->user_zip) && $v->user_zip != '' ? ' ' . $v->user_zip : '';
	echo isset($v->user_city) && $v->user_city != '' ? ' ' . $v->user_city . '<br>' : '';
	echo isset($v->user_country) && $v->user_country != '' ? ' ' . $v->user_country : '';
	echo '</td>';
	
	
	// Payment Title
	echo '<td '.$cRCur.'>'.$v->payment_title.'</td>';
	
	// Currency
	echo '<td '.$cRCur.'>'.$v->currency_code.'</td>';
	
	
	// Total
	$price->setCurrency($v->currency_id, $v->id);

	// Netto
	if ($p['report_display_tax'] == 1) {
		echo '<td '.$cRNetto.'>';
		if (isset($v->brutto)) {
			$v->rouding = isset($v->rounding) ? $v->rounding : 0;
			$v->taxsum = isset($v->taxsum) ? $v->taxsum : 0;
			
			$netto = $v->brutto - $v->rounding - $v->taxsum;
			echo $price->getPriceFormat($netto, 0, 1);
		}
		echo '</td>';
		
		// Tax
		echo '<td '.$cRTax.'>';
		if (!empty($v->tax)) {
			foreach($v->tax as $kT => $vT) {
				echo isset($taxes[$kT]['title']) ? $taxes[$kT]['title'] . ': ' : '';
				echo $price->getPriceFormat($vT,0,1) . '<br>';
			}
		}
		echo '</td>';
	}
	
	// Rounding
	if ($p['report_display_rounding'] == 1) {
		echo '<td '.$cRRounding.'>';
		echo isset($v->rounding) ? $price->getPriceFormat($v->rounding, 0, 1): '';
		echo '</td>';
	}
	
	
	// Brutto
	echo '<td '.$cRBrutto.'>';
	echo isset($v->brutto) ? $price->getPriceFormat($v->brutto, 0, 1): '';
	echo '</td>';
	
	
	echo '</tr>';
}


// TOTAL
if (!empty($d['total'])) {
	$i = 0;
	foreach ($d['total'] as $k => $v) {
		
		$netto 		= $brutto 		= $rounding 	= $tax = 0;
		$nettoTxt	= $bruttoTxt	= $roundingTxt	= $taxTxt = '';
		if (!empty($v)) {
			$price->setCurrency($k);
			echo '<tr '.$cRTotalR.' id="phReportTotalRow'.$i.'">';
			
			echo '<td '.$cRTotalC1.' colspan="5">';
			echo '' . JText::_('COM_PHOCACART_TOTAL'). ' ';
			echo '('. $price->getPriceCurrencyTitle(). ')';
			echo '</td>';
			
			if (isset($v['brutto'])) {
				$brutto 	= $v['brutto'];
				$bruttoTxt	= $price->getPriceFormat($v['brutto'], 0, 1);
			}
			$rounding = isset($v['rounding']) ? $price->getPriceFormat($v['rounding'], 0, 1) : '';
			if (isset($v['rounding'])) {
				$rounding 	= $v['rounding'];
				$roundingTxt = $price->getPriceFormat($v['rounding'], 0, 1);
			}
			
			if (!empty($v['tax'])) {
				$taxTxt = '';
				foreach($v['tax'] as $kT => $vT) {
					$tax	+= $vT;
					$taxTxt .= isset($taxes[$kT]['title']) ? $taxes[$kT]['title'] . ': ' : '';
					$taxTxt .= $price->getPriceFormat($vT,0,1) . '<br>';
				}
			}
			

	
			$netto 		= $brutto - $rounding - $tax;
			$nettoTxt	= $price->getPriceFormat($netto, 0, 1);
			
			if ($p['report_display_tax'] == 1) {
				echo '<td '.$cRTotalC2.'>'.$nettoTxt.'</td>';
				echo '<td '.$cRTotalC2.'>'.$taxTxt.'</td>';
			}
			
			if ($p['report_display_rounding'] == 1) {
				echo '<td '.$cRTotalC2.'>'.$roundingTxt.'</td>';
			}
			echo '<td '.$cRTotalC2.'>'.$bruttoTxt.'</td>';
			
			
			
			echo '</tr>';
		}
		$i++;
	}
}


	
echo '</table>';

echo '</div>';// end doc

echo '<p>&nbsp;</p>';