<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
$d 		= $displayData;
$price 	= new PhocacartPrice();
$taxes 	= PhocacartTax::getAllTaxesIncludingCountryRegionPlugin();


$p = array();
$p['report_calculation'] 		= $d['params']->get( 'report_calculation', 1);
$p['report_display_tax'] 		= $d['params']->get( 'report_display_tax', 1);
$p['report_display_rounding'] 	= $d['params']->get( 'report_display_rounding', 1);
$p['report_header'] 			= $d['params']->get( 'report_header', '');


// STYLE
$cRDoc 		= 'class="ph-report-doc"';
$cRHead 	= 'class="ph-report-header"';
$cRDate     = 'class="ph-report-date"';
$cRItemTitle 	= 'class="ph-report-product-title"';
$cRT 		= 'class="ph-report-table"';
$cRTRH 		= 'class="ph-report-table-row-header"';
$cRTRHC 	= 'class="ph-report-table-row-header-col"';
$cRTRI 		= 'class="ph-report-table-row-items"';
$cRD 		= 'class="ph-report-date"';
$cRON		= 'class="ph-report-order-number"';
$cRC		= 'class="ph-report-customer"';
$cRB		= 'class="ph-report-bold"';
$cRQty		= 'class="ph-report-quantity"';
$cRNetto	= 'class="ph-report-netto"';
$cRTax		= 'class="ph-report-tax"';
$cRBrutto	= 'class="ph-report-brutto"';
$cRRounding	= 'class="ph-report-rounding"';
$cRTotalR	= 'class="ph-report-total-row"';
$cRTotalC1	= 'class="ph-report-total-col1"';
$cRTotalC2	= 'class="ph-report-total-col2"';
$cRPA        = 'class="ph-report-product-attributes"';
switch($d['format']) {

	case 'raw':


		$cRDoc 		= 'style="font-family: sans-serif,arial;"';
		$cRHead		= 'style="padding: 3px;"';
		$cRDate	    = 'style="padding: 3px;"';
		$cRT 		= 'style="border: 1px solid #f0f0f0;border-collapse:collapse;font-size:90%;width:100%"';
		$cRTRH 		= 'style="border: 1px solid #f0f0f0;padding:3px;"';
		$cRTRHC 	= 'style="border: 1px solid #f0f0f0;padding:3px;background: #f7f7f7;"';
		$cRTRI 		= 'style=""';
		$cRD 		= 'style="text-align:center;border: 1px solid #f0f0f0;padding:5px;"';
		$cRON		= 'style="text-align:center;border: 1px solid #f0f0f0;padding:5px;"';
		$cRC		= 'style="text-align:left;border: 1px solid #f0f0f0;padding:5px;"';
		$cRB		= 'style="font-weight:bold"';
        $cRItemTitle = 'style="text-align:left;border: 1px solid #f0f0f0;padding:0;"';
		$cRQty		= 'style="text-align:center;border: 1px solid #f0f0f0;padding:5px;"';
		$cRNetto	= 'style="text-align:right;border: 1px solid #f0f0f0;padding:5px;"';
		$cRTax		= 'style="text-align:right;border: 1px solid #f0f0f0;padding:5px;"';
		$cRBrutto	= 'style="text-align:right;border: 1px solid #f0f0f0;padding:5px;"';
		$cRRounding	= 'style="text-align:right;border: 1px solid #f0f0f0;padding:5px;"';
		$cRTotalR	= 'style="background: #ffffbf;vertical-align:top;text-align:right;font-weight:bold;border: 1px solid #f0f0f0;padding:5px;"';
		$cRTotalC1	= 'style="text-align:left;vertical-align:left;border: 1px solid #f0f0f0;padding:5px;"';
		$cRTotalC2	= 'style="border: 1px solid #f0f0f0;padding:3px;"';
        $cRPA       = 'style="font-size: small;color: #777;margin:0;padding:0;"';

	break;

	case 'pdf':
        // Don't set font-family attribute here because it can break when displaying specific characters by specific font in Phoca PDF (e.g. arial does not include latin extended, etc.)
		$cRDoc 		= 'style=""';
		$cRHead		= 'style="margin: 0pt;padding: 0pt;"';
		$cRDate		= 'style="margin: 0pt;padding: 0pt;"';
		$cRT 		= 'style="border: 1pt solid #f0f0f0;border-collapse:collapse;font-size:60%;width:100%" cellpadding="1"';
		$cRTRH 		= 'style="border: 1pt solid #f0f0f0;"';
		$cRTRHC 	= 'style="border: 1pt solid #f0f0f0;background-color: #f7f7f7;font-size: 90%"';
		$cRTRI 		= 'style=""';
		$cRD 		= 'style="text-align:center;border: 1pt solid #f0f0f0;"';
		$cRON		= 'style="text-align:center;border: 1pt solid #f0f0f0;"';
		$cRC		= 'style="text-align:left;border: 1pt solid #f0f0f0;padding:5px;"';
		$cRB		= 'style="font-weight:bold"';
		$cRItemTitle		= 'style="text-align:left;border: 1pt solid #f0f0f0;"';
        $cRQty		= 'style="text-align:center;border: 1pt solid #f0f0f0;"';
		$cRNetto	= 'style="text-align:right;border: 1pt solid #f0f0f0;"';
		$cRTax		= 'style="text-align:right;border: 1pt solid #f0f0f0;"';
		$cRBrutto	= 'style="text-align:right;border: 1pt solid #f0f0f0;"';
		$cRRounding	= 'style="text-align:right;border: 1pt solid #f0f0f0;"';
		$cRTotalR	= 'style="background-color: #ffffbf;vertical-align:top;text-align:right;font-weight:bold;border: 1pt solid #f0f0f0;"';
		$cRTotalC1	= 'style="text-align:left;vertical-align:left;border: 1pt solid #f0f0f0;"';
		$cRTotalC2	= 'style="border: 1pt solid #f0f0f0;"';
        $cRPA       = 'style="font-size: small;color: #777;margin:0;padding:0;"';

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
	echo Text::_('COM_PHOCACART_DATE') . ': ';
	echo $d['date_from'] . ' - ' . $d['date_to'];
	echo '</div>';



}

// HEADER
echo '<table '.$cRT.'>';
echo '<tr '.$cRTRH.'>';
if ($d['report_type'] == 3) {
    echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_SHIPPING_METHOD').'</th>';
} else {
    echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_PAYMENT_METHOD').'</th>';
}

//echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_NUMBER_OF_PRODUCTS_SOLD').'</th>';
echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_QTY').'</th>';

if ($p['report_display_tax'] == 1) {
	//echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_AMOUNT_EXCLUDING_TAX').'</th>'; // TRC
	echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_AMOUNT_EXCLUDING_TAX').'</th>';
	//echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_AMOUNT_TAX').'</th>'; // TRC
	echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_AMOUNT_TAX').'</th>';
}
/*if ($p['report_display_rounding'] == 1) {
	//echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_ROUNDING').'</th>'; // TRC
	echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_ROUNDING').'</th>';
}*/
if ($p['report_display_tax'] == 1) {
    // BE AWARE it is not amount inclusive tax, it is total amount because there can be added items which are sold without VAT
	echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_REPORT_TOTAL_AMOUNT').'</th>';
} else {
	echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_AMOUNT').'</th>';
}

/*if ($d['report_type'] == 1 && (int)$d['order_status'] > 0) {
    echo '<th '.$cRTRHC.'>'.Text::_('COM_PHOCACART_ORDER_STATUS_DATE').'</th>';
}*/

echo '</tr>';


if ($d['report_type'] == 3) {
    $netto = 'snetto';
    $tax = 'stax';
    $brutto = 'sbrutto';
} else {
    $netto = 'pnetto';
    $tax = 'ptax';
    $brutto = 'pbrutto';
}


// PREPARE ITEMS
$items = [];




foreach($d['items'] as $k => $v) {

    if ($d['report_type'] == 3) {
        $itemId = $v->shipping_id;
    } else {
        $itemId = $v->payment_id;
    }

    if (!isset($items[$itemId])){


        if ($d['report_type'] == 3) {
            $items[$itemId]['title'] = $v->stitle;
        } else {
            $items[$itemId]['title'] = $v->ptitle;
        }

        if ($v->type == $netto) {
            $items[$itemId][$netto] = $v->amount;

            // we have 3 rows for each order id, so count only in netto
            if (!isset($items[$itemId]['quantity'] )) {
                 $items[$itemId]['quantity'] =  1;
            } else {
                 $items[$itemId]['quantity'] =  $items[$itemId]['quantity'] + 1;
            }
        }

        if ($v->type == $tax) {
            $items[$itemId][$tax] = $v->amount;
        }

        if ($v->type == $brutto) {
            $items[$itemId][$brutto] = $v->amount;
        }

    } else {

        if ($v->type == $netto) {
            if (isset($items[$itemId][$netto])) {
                $items[$itemId][$netto] += $v->amount;
            } else {
                 $items[$itemId][$netto] = $v->amount;
            }

            if (!isset($items[$itemId]['quantity'] )) {
                 $items[$itemId]['quantity'] =  1;
            } else {
                 $items[$itemId]['quantity'] =  $items[$itemId]['quantity'] + 1;
            }

        }

        if ($v->type == $tax) {
            if (isset($items[$itemId][$tax])) {
                $items[$itemId][$tax] += $v->amount;
            } else {
                 $items[$itemId][$tax] = $v->amount;
            }

        }

        if ($v->type == $brutto) {
            if (isset($items[$itemId][$brutto])) {
                $items[$itemId][$brutto] += $v->amount;
            } else {
                 $items[$itemId][$brutto] = $v->amount;
            }

        }

    }
}

// ITEMS
foreach($items as $k => $v) {

    $vNetto = isset($v[$netto]) ? $v[$netto] : 0;
    $vTax = isset($v[$tax]) ? $v[$tax] : 0;
    $vBrutto = isset($v[$brutto]) ? $v[$brutto] : 0;

	echo '<tr '.$cRTRI.'>';

	// Product Title
	echo '<td '.$cRItemTitle.'>'.$v['title'];

    echo '</td>';

	// SUM
	echo '<td '.$cRQty.'>'.$v['quantity'].'</td>';

	// Netto
	if ($p['report_display_tax'] == 1) {

        // Netto
        echo '<td '.$cRNetto.'>';
        echo isset($vNetto) ? $price->getPriceFormat($vNetto, 0, 1): '';
        echo '</td>';

        // Tax
        echo '<td '.$cRTax.'>';
        echo isset($vTax) ? $price->getPriceFormat($vTax, 0, 1): '';
        echo '</td>';

	}

	// Brutto
	echo '<td '.$cRBrutto.'>';
	echo isset($vBrutto) ? $price->getPriceFormat($vBrutto, 0, 1): '';
	echo '</td>';

	echo '</tr>';
}

echo '</table>';

echo '</div>';// end doc

echo '<p>&nbsp;</p>';
