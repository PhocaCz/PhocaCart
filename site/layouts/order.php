<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */


/*
 * +-------------------------------------------+
 * |        TYPE      |      FORMAT            |
 * +------------------+------------------------+
 * | 1. ORDER/RECEIPT |  html - HTML/SITE      |
 * | 2. INVOICE       |  pdf - PDF             |
 * | 3. DELIVERY NOTE |  mail - Mail           |
 * | 4. RECEIPT (POS) |  rss - RSS             |
 * |                  |  raw - RAW (POS PRINT) |
 * +------------------+------------------------+
 */

defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Phoca\PhocaCart\Dispatcher\Dispatcher;

$layoutAl 	= new FileLayout('alert', null, array('component' => 'com_phocacart', 'client' => 0));

$d = $displayData;

/*
 * Parameters
 */
$store_title						= $d['params']->get( 'store_title', '' );
$store_logo							= $d['params']->get( 'store_logo', '' );
$store_info							= $d['params']->get( 'store_info', '' );
$store_info							= PhocacartRenderFront::renderArticle($store_info, $d['format']);
//$invoice_prefix					= $d['params']->get( 'invoice_prefix', '');
//$invoice_number_format			= $d['params']->get( 'invoice_number_format', '');
//$invoice_number_chars				= $d['params']->get( 'invoice_number_chars', 12);
$invoice_tp							= $d['params']->get( 'invoice_terms_payment', '');
$display_discount_price_product		= $d['params']->get( 'display_discount_price_product', 1);

$tax_calculation                    = $d['params']->get( 'tax_calculation', 0 );

$store_title_pos						= $d['params']->get( 'store_title_pos', '' );
$store_logo_pos							= $d['params']->get( 'store_logo_pos', '' );
$store_info_pos							= $d['params']->get( 'store_info_pos', '' );
$store_info_footer_pos					= $d['params']->get( 'store_info_footer_pos', '' );

// Used in Phoca PDF Phocacart plugin because of converting the TCPDF QR code into html
//$pdf_invoice_qr_code					= $d['params']->get( 'pdf_invoice_qr_code', '' );
$pdf_invoice_signature_image			= $d['params']->get( 'pdf_invoice_signature_image', '' );
$pdf_invoice_qr_information				= $d['params']->get( 'pdf_invoice_qr_information', '' );
$invoice_global_top_desc				= $d['params']->get( 'invoice_global_top_desc', 0 );// Article ID
$invoice_global_middle_desc				= $d['params']->get( 'invoice_global_middle_desc', 0 );
$invoice_global_bottom_desc				= $d['params']->get( 'invoice_global_bottom_desc', 0 );

$order_global_top_desc					= $d['params']->get( 'order_global_top_desc', 0 );// Article ID
$order_global_middle_desc				= $d['params']->get( 'order_global_middle_desc', 0 );
$order_global_bottom_desc				= $d['params']->get( 'order_global_bottom_desc', 0 );

$dn_global_top_desc					= $d['params']->get( 'dn_global_top_desc', 0 );// Article ID
$dn_global_middle_desc				= $d['params']->get( 'dn_global_middle_desc', 0 );
$dn_global_bottom_desc				= $d['params']->get( 'dn_global_bottom_desc', 0 );

$oidn_global_billing_desc			    = $d['params']->get( 'oidn_global_billing_desc', 0 );
$oidn_global_shipping_desc			    = $d['params']->get( 'oidn_global_shipping_desc', 0 );

$display_tax_recapitulation_invoice		= $d['params']->get( 'display_tax_recapitulation_invoice', 0 );
$display_tax_recapitulation_pos			= $d['params']->get( 'display_tax_recapitulation_pos', 0 );

$display_reward_points_invoice			= $d['params']->get( 'display_reward_points_invoice', 0 );
$display_reward_points_pos				= $d['params']->get( 'display_reward_points_pos', 0 );

$display_time_of_supply_invoice			= $d['params']->get( 'display_time_of_supply_invoice', 0 );


$store_logo = PhocacartUtils::realCleanImageUrl($store_logo);
$store_logo_pos = PhocacartUtils::realCleanImageUrl($store_logo_pos);

if($d['type'] == 1 && $d['common']->order_number == '') {
	echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_ORDER_DOES_NOT_EXIST')));
	return;
}
if($d['type'] == 3 && $d['common']->order_number == '') {
	echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_DELIVERY_NOTE_NOT_YET_ISSUED')));
	return;
}
if($d['type'] == 1 && $d['common']->receipt_number == '') {
	echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_RECEIPT_NOT_YET_ISSUED')));
	return;
}
if($d['type'] == 2 && $d['common']->invoice_number == '') {
    echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_INVOICE_NOT_YET_ISSUED')));
	return;
}

if(!isset($d['bas']['b'])) {
	$d['bas']['b'] = array();
}
if(!isset($d['bas']['s'])) {
	$d['bas']['s'] = array();
}

/*
 * FORMAT
 */
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
$bDesc		= 'class="ph-idnr-body-desc"';
$hrSmall	= 'class="ph-idnr-hr-small"';
$taxRecTable= 'class="ph-idnr-tax-rec"';
$taxRecTd	= 'class="ph-idnr-tax-rec-td"';
$taxRecTdRight= 'class="ph-idnr-tax-rec-td ph-right"';
$bQrInfo	= '';
$firstRow	= '';


// POS RECEIPT
$pR 	= false;

if ($d['format'] == 'raw' && $d['type'] == 4) {
	$pR 	= true;
	$oPr	= array();
	$pP 	= new PhocacartPosPrint(0);


}

if ($d['format'] == 'pdf') {
	// FORMAT PDF


	// Products
	if ($tax_calculation > 0) {
		$colW = 8.3333;// 12 cols x 8.3333 = 100%
	} else {
		$colW = 11.11;// 9 cols x 11.11 = 100%
	}
	$box		= '';
	$table 		= 'style="width: 100%; font-size: 80%;padding:3px;margin-top:-200px"';
	$pho1 		= 'style="width: '.$colW.'%;"';
	$pho2 		= 'style="width: '.$colW.'%;"';
	$pho3 		= 'style="width: '.$colW.'%;"';
	$pho4 		= 'style="width: '.$colW.'%;"';
	$pho5 		= 'style="width: '.$colW.'%;"';
	$pho6 		= 'style="width: '.$colW.'%;"';
	$pho7 		= 'style="width: '.$colW.'%;"';
	$pho6Sep 	= 'style="width: 3%;"';
	$pho7Sep 	= 'style="width: 3%;"';
	$pho8 		= 'style="width: '.$colW.'%;"';
	$pho9 		= 'style="width: '.$colW.'%;"';
	$pho10 		= 'style="width: '.$colW.'%;"';
	$pho11 		= 'style="width: '.$colW.'%;"';
	$pho12 		= 'style="width: '.$colW.'%;"';
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
	//$boxIn 		= 'style="width: 100%; font-family: sans-serif, arial; font-size: 60%;padding:3px 1px;"';
	$boxIn 		= 'style="width: 100%; font-size: 60%;padding:1px 1px;"';
	$hProduct 	= 'style="white-space:nowrap;font-weight: bold;background-color: #dddddd;"';
	$bProduct	= 'style="white-space:nowrap;"';
	$sepH		= 'style="border-top: 1pt solid #dddddd;"';
	$totalF		= 'style=""';
	$toPayS		= 'style="background-color: #eeeeee;padding: 20px;"';
	$toPaySV	= 'style="background-color: #eeeeee;padding: 20px;text-align:right;"';
	$firstRow	= 'style="font-size:0pt;"';

	$bDesc		= 'style="padding: 2px 0px 0px 0px;margin:0;font-size:60%;"';
	$hrSmall	= 'style="font-size:30%;"';
	$taxRecTable= 'style="border: 1pt solid #dddddd; width: 70%;font-size: 60%;"';
	$taxRecTd	= 'style="border: 1pt solid #dddddd;"';
	$taxRecTdRight= 'style="border: 1pt solid #dddddd;text-align:right;"';
	$bQrInfo	= 'style="font-size: 70%"';

} else if ($d['format'] == 'mail') {

	// FORMAT EMAIL
	$box		= '';
	//$table 		= 'style="width: 100%; font-family: sans-serif, arial; font-size: 90%;"';
	$table 		= 'style="width: 100%; font-size: 90%;"';
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
	//$boxIn 		= 'style="width: 100%; font-family: sans-serif, arial; font-size: 90%;"';
	$boxIn 		= 'style="width: 100%; font-size: 90%;"';
	$hProduct 	= 'style="white-space:nowrap;padding: 5px;font-weight: bold;background: #ddd;"';
	$bProduct	= 'style="white-space:nowrap;padding: 5px;"';
	$sepH		= 'style="border-top: 1px solid #ddd;"';
	$totalF		= 'style=""';
	$toPayS		= 'style="background-color: #eeeeee;padding: 20px;"';
	$toPaySV	= 'style="background-color: #eeeeee;padding: 20px;text-align:right;"';
	$firstRow	= '';
	$taxRecTable= 'style="border: 1pt solid #dddddd; width: 50%;"';
	$taxRecTd	= 'style="border: 1pt solid #dddddd;"';

}

// -----------
// R E N D E R
// -----------
$o = array();
$o[] = '<div '.$box.'>';


// -----------
// 1. PART
// -----------
$o[] = '<table '.$table.'>';

$o[] = '<tr '.$firstRow.'>';
$o[] = '<td '.$pho12.'>&nbsp;</td><td '.$pho22.'>&nbsp;</td><td '.$pho32.'>&nbsp;</td><td '.$pho42.'>&nbsp;</td>';
$o[] = '<td '.$pho52.'>&nbsp;</td><td '.$pho6Sep2.'>&nbsp;</td><td '.$pho7Sep2.'>&nbsp;</td><td '.$pho82.'>&nbsp;</td>';
$o[] = '<td '.$pho92.'>&nbsp;</td><td '.$pho102.'>&nbsp;</td><td '.$pho112.'>&nbsp;</td><td '.$pho122.'>&nbsp;</td>';
$o[] = '</tr>';


// -----------
// HEADER LEFT
// -----------
$o[] = '<tr><td colspan="5">';
if ($store_title != '') {
	$o[] = '<div><h1>'.$store_title.'</h1></div>';
}
if ($store_logo != '') {
	$o[] = '<div><img class="ph-idnr-header-img" src="'.Uri::root(false). ''.$store_logo.'" /></div>';
}
if ($store_info != '') {
	$o[] = '<div>'.$store_info.'</div>';
}
$o[] = '</td>';

$o[] = '<td colspan="2" '.$sep2.'></td>';


// -----------
// HEADER RIGHT
// -----------
$o[] = '<td colspan="5">';
if ($d['type'] == 1) {
	$o[] = '<div><h1>'.Text::_('COM_PHOCACART_ORDER').'</h1></div>';
	$o[] = '<div><b>'.Text::_('COM_PHOCACART_ORDER_NR').'</b>: '.PhocacartOrder::getOrderNumber($d['common']->id, $d['common']->date, $d['common']->order_number).'</div>';
	$o[] = '<div><b>'.Text::_('COM_PHOCACART_ORDER_DATE').'</b>: '.HTMLHelper::date($d['common']->date, 'DATE_FORMAT_LC4').'</div>';
} else if ($d['type'] == 2) {

	$o[] = '<div><h1>'.Text::_('COM_PHOCACART_INVOICE').'</h1></div>';
	$o[] = '<div><b>'.Text::_('COM_PHOCACART_INVOICE_NR').'</b>: '.PhocacartOrder::getInvoiceNumber($d['common']->id, $d['common']->date, $d['common']->invoice_number).'</div>';
	$o[] = '<div><b>'.Text::_('COM_PHOCACART_INVOICE_DATE').'</b>: '.HTMLHelper::date($d['common']->invoice_date, 'DATE_FORMAT_LC4').'</div>';

	if ($display_time_of_supply_invoice	== 1 && $d['common']->invoice_time_of_supply != '' && $d['common']->invoice_time_of_supply != '0000-00-00 00:00:00') {
        $o[] = '<div><b>' . Text::_('COM_PHOCACART_DATE_OF_TAXABLE_SUPPLY') . '</b>: ' . HTMLHelper::date($d['common']->invoice_time_of_supply, 'DATE_FORMAT_LC4') . '</div>';
    }

    $o[] = '<div><b>'.Text::_('COM_PHOCACART_INVOICE_DUE_DATE').'</b>: '.PhocacartOrder::getInvoiceDueDate($d['common']->id, $d['common']->date, $d['common']->invoice_due_date, 'DATE_FORMAT_LC4').'</div>';
	$o[] = '<div><b>'.Text::_('COM_PHOCACART_PAYMENT_REFERENCE_NUMBER').'</b>: '.PhocacartOrder::getPaymentReferenceNumber($d['common']->id, $d['common']->date, $d['common']->invoice_prn).'</div>';
	// Display order number in invoice because order number can be different to invoice number
	$o[] = '<div><b>'.Text::_('COM_PHOCACART_ORDER_NR').'</b>: '.PhocacartOrder::getOrderNumber($d['common']->id, $d['common']->date, $d['common']->order_number).'</div>';

} else if ($d['type'] == 3) {
	$o[] = '<div><h1>'.Text::_('COM_PHOCACART_DELIVERY_NOTE').'</h1></div>';
	$o[] = '<div style="margin:0;"><b>'.Text::_('COM_PHOCACART_ORDER_NR').'</b>: '.PhocacartOrder::getOrderNumber($d['common']->id, $d['common']->date, $d['common']->order_number).'</div>';
	$o[] = '<div style="margin:0"><b>'.Text::_('COM_PHOCACART_ORDER_DATE').'</b>: '.HTMLHelper::date($d['common']->date, 'DATE_FORMAT_LC4').'</div>';

}

$o[] = '<div>&nbsp;</div>';
if (isset($d['common']->paymenttitle) && $d['common']->paymenttitle != '') {
	$o[] = '<div><b>'.Text::_('COM_PHOCACART_PAYMENT').'</b>: '.$d['common']->paymenttitle.'</div>';
}

if ($d['type'] == 2 && $invoice_tp	!= '') {
	$o[] = '<div><b>'.Text::_('COM_PHOCACART_TERMS_OF_PAYMENT').'</b>: '.$invoice_tp.'</div>';
}

if (isset($d['common']->shippingtitle) && $d['common']->shippingtitle != '') {
	$o[] = '<div><b>'.Text::_('COM_PHOCACART_SHIPPING').'</b>: '.$d['common']->shippingtitle.'</div>';
}

$o[] = '</td></tr>';

$o[] = '<tr><td colspan="12">&nbsp;</td></tr>';


// POS HEADER
if ($pR) {
	$oPr[] = $pP->printImage($store_logo_pos);
}
if ($pR) {
	$storeTitlePos = array();
	if ($store_title_pos != '') {
		$storeTitlePos = explode("\n", $store_title_pos);
	}
	$oPr[] = $pP->printFeed(1);
	$oPr[] = $pP->printLine($storeTitlePos, 'pDoubleSizeCenter');
	$oPr[] = $pP->printFeed(1);
}
if ($pR) {

	$storeInfoPos = array();
	if ($store_info_pos != '') {
		$store_info_pos 	= PhocacartText::completeText($store_info_pos, $d['preparereplace'], 1);
		$storeInfoPos = explode("\n", strip_tags($store_info_pos));
	}

	$oPr[] = $pP->printLine($storeInfoPos, 'pCenter');
}




// -----------
// BILLING AND SHIPPING HEADER
// -----------
$o[] = '<tr><td colspan="5"><b>'.Text::_('COM_PHOCACART_BILLING_ADDRESS').'</b></td>';
$o[] = '<td colspan="2"></td>';
$o[] = '<td colspan="5"><b>'.Text::_('COM_PHOCACART_SHIPPING_ADDRESS').'</b></td></tr>';


// -----------
// BILLING
// -----------

$ob = array();
$ob2 = array();// specific case for $oidn_global_billing_desc
if (!empty($d['bas']['b'])) {

	$v = $d['bas']['b'];


	if ($v['company'] != '') { $ob[] = '<b>'.$v['company'].'</b><br />';}
	$name = array();
	if ($v['name_degree'] != '') { $name[] = $v['name_degree'];}
	if ($v['name_first'] != '') { $name[] = $v['name_first'];}
	if ($v['name_middle'] != '') { $name[] = $v['name_middle'];}
	if ($v['name_last'] != '') { $name[] = $v['name_last'];}
	if (!empty($name)) {$ob[] = '<b>' . implode(" ", $name).'</b><br />';}
	if ($v['address_1'] != '') { $ob[] = $v['address_1'].'<br />';}
	if ($v['address_2'] != '') { $ob[] = $v['address_2'].'<br />';}
	$city = array();
	if ($v['zip'] != '') { $city[] = $v['zip'];}
	if ($v['city'] != '') { $city[] = $v['city'];}
	if (!empty($city)) {$ob[] = implode(" ", $city).'<br />';}
	//echo '<br />';
	if (!empty($v['regiontitle'])) {$ob[] = $v['regiontitle'].'<br />';}
	if (!empty($v['countrytitle'])) {$ob[] = $v['countrytitle'].'<br />';}
	//echo '<br />';
	if ($v['vat_1'] != '') { $ob[] = '<br />'.Text::_('COM_PHOCACART_VAT_1_LABEL').': '. $v['vat_1'].'<br />';}
	if ($v['vat_2'] != '') { $ob[] = Text::_('COM_PHOCACART_VAT_2_LABEL').': '.$v['vat_2'].'<br />';}

	// -----------------------
	// ORDER | INVOICE | DELIVERY NOTE BILLING ADDRESS DESCRIPTION
	// -----------------------
	if ($d['type'] == 1 || $d['type'] == 2 || $d['type'] == 3) {

		$oidnBillingDescArticle = '';
		if ($d['common']->oidn_spec_billing_desc != '') {
			$oidnBillingDescArticle = $d['common']->oidn_spec_billing_desc;
		} else if ((int)$oidn_global_billing_desc > 0) {
			$oidnBillingDescArticle = PhocacartRenderFront::renderArticle((int)$oidn_global_billing_desc, $d['format']);
		}

		if ($oidnBillingDescArticle != '') {
			//$o[] = '<div '.$hrSmall.'>&nbsp;</div>';
			$oidnBillingDescArticle 	= PhocacartPdf::skipStartAndLastTag($oidnBillingDescArticle, 'p');
			$oidnBillingDescArticle 	= PhocacartText::completeText($oidnBillingDescArticle, $d['preparereplace'], 1);
			//$oidnBillingDescArticle 	= PhocacartText::completeTextFormFields($oidnBillingDescArticle, $d['bas']['b'], 1);
			//$oidnBillingDescArticle 	= PhocacartText::completeTextFormFields($oidnBillingDescArticle, $d['bas']['s'], 2);
			$oidnBillingDescArticle 	= PhocacartText::completeTextFormFields($oidnBillingDescArticle, $d['bas']['b'], $d['bas']['s']);
			$ob2[] = $oidnBillingDescArticle;
		}
	}

}


// -----------
// SHIPPING
// -----------
$os = array();
$os2 = array();// specific case for $oidn_global_shipping_desc
if (!empty($d['bas']['s'])) {
	$v = $d['bas']['s'];
	if ($v['company'] != '') { $os[] = '<b>'.$v['company'].'</b><br />';}
	$name = array();
	if ($v['name_degree'] != '') { $name[] = $v['name_degree'];}
	if ($v['name_first'] != '') { $name[] = $v['name_first'];}
	if ($v['name_middle'] != '') { $name[] = $v['name_middle'];}
	if ($v['name_last'] != '') { $name[] = $v['name_last'];}
	if (!empty($name)) {$os[] = '<b>' . implode(" ", $name).'</b><br />';}
	if ($v['address_1'] != '') { $os[] = $v['address_1'].'<br />';}
	if ($v['address_2'] != '') { $os[] = $v['address_2'].'<br />';}
	$city = array();
	if ($v['zip'] != '') { $city[] = $v['zip'];}
	if ($v['city'] != '') { $city[] = $v['city'];}
	if (!empty($city)) {$os[] = implode(" ", $city).'<br />';}
	//echo '<br />';
	if (!empty($v['regiontitle'])) {$os[] = $v['regiontitle'].'<br />';}
	if (!empty($v['countrytitle'])) {$os[] = $v['countrytitle'].'<br />';}
	//echo '<br />';
	if ($v['vat_1'] != '') { $os[] = '<br />'.Text::_('COM_PHOCACART_VAT1').': '. $v['vat_1'].'<br />';}
	if ($v['vat_2'] != '') { $os[] = Text::_('COM_PHOCACART_VAT2').': '.$v['vat_2'].'<br />';}


	// -----------------------
    // ORDER | INVOICE | DELIVERY NOTE SHIPPING ADDRESS DESCRIPTION
    // -----------------------
    if ($d['type'] == 1 || $d['type'] == 2 || $d['type'] == 3) {

		$oidnShippingDescArticle = '';
		if ($d['common']->oidn_spec_shipping_desc != '') {
			$oidnShippingDescArticle = $d['common']->oidn_spec_shipping_desc;
		} else if ((int)$oidn_global_shipping_desc > 0) {
			$oidnShippingDescArticle = PhocacartRenderFront::renderArticle((int)$oidn_global_shipping_desc, $d['format']);
		}


		if ($oidnShippingDescArticle != '') {
			//$o[] = '<div '.$hrSmall.'>&nbsp;</div>';
			$oidnShippingDescArticle 	= PhocacartPdf::skipStartAndLastTag($oidnShippingDescArticle, 'p');
			$oidnShippingDescArticle 	= PhocacartText::completeText($oidnShippingDescArticle, $d['preparereplace'], 1);
			//$oidnShippingDescArticle 	= PhocacartText::completeTextFormFields($oidnShippingDescArticle, $d['bas']['b'], 1);
			//$oidnShippingDescArticle 	= PhocacartText::completeTextFormFields($oidnShippingDescArticle, $d['bas']['s'], 2);
			$oidnShippingDescArticle 	= PhocacartText::completeTextFormFields($oidnShippingDescArticle, $d['bas']['b'], $d['bas']['s']);
			$os2[] = $oidnShippingDescArticle;
		}
	}
}


// BILLING OUTPUT
$o[] = '<tr><td colspan="5" '.$bBox.' ><div '.$bBoxIn.'>';
$o[] = implode("\n", $ob);
$o[] = implode("\n", $ob2);
$o[] = '</div></td>';
$o[] = '<td colspan="2">&nbsp;</td>';


// SHIPPING OUTPUT
$o[] = '<td colspan="5" '.$sBox.'><div '.$sBoxIn.'>';
if ((isset($d['bas']['b']['ba_sa']) && $d['bas']['b']['ba_sa'] == 1) || (isset($d['bas']['s']['ba_sa']) && $d['bas']['s']['ba_sa'] == 1)) {
	$o[] = implode("\n", $ob);
	//$o[] = implode("\n", $ob2); Don't display shipping description in billing
	// Possible TO DO - parameter if display shipping description in case there is no shipping address but in fact the billing one
	$o[] = implode("\n", $os2);
} else {
	$o[] = implode("\n", $os);
	$o[] = implode("\n", $os2);
}
$o[] = '</div></td></tr>';
//$o[] = '<tr><td colspan="12">&nbsp;</td></tr>';
$o[] = '</table>';


// -----------------------
// INVOICE TOP DESCRIPTION
// -----------------------
if ($d['type'] == 2) {

	$invoiceTopDescArticle = '';
	if ($d['common']->invoice_spec_top_desc != '') {
		$invoiceTopDescArticle = $d['common']->invoice_spec_top_desc;
	} else if ((int)$invoice_global_top_desc > 0) {
		$invoiceTopDescArticle = PhocacartRenderFront::renderArticle((int)$invoice_global_top_desc, $d['format']);
	}

	if ($invoiceTopDescArticle != '') {
		$o[] = '<div '.$hrSmall.'>&nbsp;</div>';
		$invoiceTopDescArticle 	= PhocacartPdf::skipStartAndLastTag($invoiceTopDescArticle, 'p');

        if ($d['qrcode'] != '' && $d['format'] == 'pdf') {
            $invoiceTopDescArticle = str_replace('{invoiceqr}', '{phocapdfqrcode|' . urlencode($d['qrcode']) . '}', $invoiceTopDescArticle);
        }

		$invoiceTopDescArticle 	= PhocacartText::completeText($invoiceTopDescArticle, $d['preparereplace'], 1);
		//$invoiceTopDescArticle 	= PhocacartText::completeTextFormFields($invoiceTopDescArticle, $d['bas']['b'], 1);
		//$invoiceTopDescArticle 	= PhocacartText::completeTextFormFields($invoiceTopDescArticle, $d['bas']['s'], 2);
		$invoiceTopDescArticle 	= PhocacartText::completeTextFormFields($invoiceTopDescArticle, $d['bas']['b'], $d['bas']['s']);
		$o[] = '<table '.$bDesc.'><tr><td>'.$invoiceTopDescArticle.'</td></tr></table>';
	}
} else if ($d['type'] == 1) {
	$orderTopDescArticle = PhocacartRenderFront::renderArticle((int)$order_global_top_desc, $d['format']);

	if ($orderTopDescArticle != '') {
		$o[] = '<div '.$hrSmall.'>&nbsp;</div>';
		$orderTopDescArticle 	= PhocacartPdf::skipStartAndLastTag($orderTopDescArticle, 'p');

        if ($d['qrcode'] != '' && $d['format'] == 'pdf') {
            $orderTopDescArticle = str_replace('{invoiceqr}', '{phocapdfqrcode|' . urlencode($d['qrcode']) . '}', $orderTopDescArticle);
        }

		$orderTopDescArticle 	= PhocacartText::completeText($orderTopDescArticle, $d['preparereplace'], 1);
		//$orderTopDescArticle 	= PhocacartText::completeTextFormFields($orderTopDescArticle, $d['bas']['b'], 1);
		//$orderTopDescArticle 	= PhocacartText::completeTextFormFields($orderTopDescArticle, $d['bas']['s'], 2);
		$orderTopDescArticle 	= PhocacartText::completeTextFormFields($orderTopDescArticle, $d['bas']['b'], $d['bas']['s']);
		$o[] = '<table '.$bDesc.'><tr><td>'.$orderTopDescArticle.'</td></tr></table>';
	}
} else if ($d['type'] == 3) {
	$dnTopDescArticle = PhocacartRenderFront::renderArticle((int)$dn_global_top_desc, $d['format']);

	if ($dnTopDescArticle != '') {
		$o[] = '<div '.$hrSmall.'>&nbsp;</div>';
		$dnTopDescArticle 	= PhocacartPdf::skipStartAndLastTag($dnTopDescArticle, 'p');

        if ($d['qrcode'] != '' && $d['format'] == 'pdf') {
            $dnTopDescArticle = str_replace('{invoiceqr}', '{phocapdfqrcode|' . urlencode($d['qrcode']) . '}', $dnTopDescArticle);
        }

		$dnTopDescArticle 	= PhocacartText::completeText($dnTopDescArticle, $d['preparereplace'], 1);
		$dnTopDescArticle 	= PhocacartText::completeTextFormFields($dnTopDescArticle, $d['bas']['b'], $d['bas']['s']);
		$o[] = '<table '.$bDesc.'><tr><td>'.$dnTopDescArticle.'</td></tr></table>';
	}
}


// -----------
// 2. PART
// -----------
$o[] = '<table '.$boxIn.'>';
$o[] = '<tr>';
$o[] = '<td '.$pho1.'>&nbsp;</td><td '.$pho2.'>&nbsp;</td><td '.$pho3.'>&nbsp;</td><td '.$pho4.'>&nbsp;</td>';
$o[] = '<td '.$pho5.'>&nbsp;</td><td '.$pho6.'>&nbsp;</td><td '.$pho7.'>&nbsp;</td><td '.$pho8.'>&nbsp;</td>';
$o[] = '<td '.$pho9.'>&nbsp;</td>';
if ($tax_calculation > 0) {
	$o[] = '<td '.$pho10.'>&nbsp;</td><td '.$pho11.'>&nbsp;</td><td '.$pho12.'>&nbsp;</td>';
}
$o[] = '</tr>';


$dDiscount 	= 0; // Display Discount (Coupon, cnetto)
$cTitle		= 3; // Colspan Title


$p = array();
if (!empty($d['products'])) {

	// Prepare header and body
	foreach ($d['products'] as $k => $v) {
		if ($v->damount > 0) {
			$dDiscount 	= 1;
			$cTitle 	= 2;
		}
	}

	if ($d['type'] == 3) {
		$cTitle	= 10;
	}

	$p[] = '<tr '.$hProduct.'>';
	$p[] = '<td>'.Text::_('COM_PHOCACART_SKU').'</td>';
	$p[] = '<td colspan="'.$cTitle.'">'.Text::_('COM_PHOCACART_ITEM').'</td>';
	$p[] = '<td style="text-align:center">'.Text::_('COM_PHOCACART_QTY').'</td>';

	if ($d['type'] != 3) {
		$p[] = '<td style="text-align:right" colspan="2">'.Text::_('COM_PHOCACART_PRICE_UNIT').'</td>';
		if ($dDiscount == 1) {
			$p[] = '<td style="text-align:center"">'.Text::_('COM_PHOCACART_DISCOUNT').'</td>';
		}
		if ($tax_calculation > 0) {
			$p[] = '<td style="text-align:right" colspan="2">'.Text::_('COM_PHOCACART_PRICE_EXCL_TAX').'</td>';
			$p[] = '<td style="text-align:right">'.Text::_('COM_PHOCACART_TAX').'</td>';
			$p[] = '<td style="text-align:right" colspan="2">'.Text::_('COM_PHOCACART_PRICE_INCL_TAX').'</td>';
		} else {
			$p[] = '<td style="text-align:right" colspan="2">'.Text::_('COM_PHOCACART_PRICE').'</td>';
		}

	}
	$p[] = '</tr>';

	if ($pR) { $oPr[] = $pP->printSeparator(); }

	foreach($d['products'] as $k => $v) {

		// $codes = PhocacartProduct::getProductCodes((int)$v->product_id);
		// echo $codes['isbn']; getting codes like isbn, ean, jpn, serial_number from product
		// codes are the latest stored in database not codes which were valid in date of order

		/*
		$productImage 		= PhocacartProduct::getImageByProductId($v->product_id);
		$path				= PhocacartPath::getPath('productimage');// add before foreach
		if ($productImage != '') {
			$productThumbnail 	= PhocacartImage::getThumbnailName($path, $productImage, 'small');
			$productImageOutput = '<img src="'.Uri::root().''.$productThumbnail->rel.'" alt="" />';
		}
		*/

		$p[] = '<tr '.$bProduct.'>';
		$p[] = '<td>'.$v->sku.'</td>';
		$p[] = '<td colspan="'.$cTitle.'">'.$v->title.'</td>';

		if ($pR) { $oPr[] = $pP->printLineColumns(array($v->sku, $v->title), 1); }

		$p[] = '<td style="text-align:center">'.$v->quantity.'</td>';


		$netto 		= (int)$v->quantity * $v->netto;
		$nettoUnit	= $v->netto;
		$tax 		= (int)$v->quantity * $v->tax;
		$brutto 	= (int)$v->quantity * $v->brutto;
		if ($d['type'] != 3) {
			if ($tax_calculation > 0) {
				$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($v->netto).'</td>';
				$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($netto).'</td>';
				$p[] = '<td style="text-align:right" colspan="1">'.$d['price']->getPriceFormat($tax).'</td>';
				$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($brutto).'</td>';
			} else {
				$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($v->netto).'</td>';
				$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($brutto).'</td>';
			}

		}
		$p[] = '</tr>';

		if (!empty($v->attributes)) {
			$p[] = '<tr>';
			$p[] = '<td></td>';
			$p[] = '<td colspan="3" align="left"><ul class="ph-idnr-ul">';
			foreach ($v->attributes as $k2 => $v2) {
				$p[] = '<li><span class="ph-small ph-cart-small-attribute ph-idnr-li">'.$v2->attribute_title .' '.$v2->option_title.'</span></li>';

				/* Should we display the values of attributes added by users in order/delivery note/receipt/invoice?

				$p[] = '<li><span class="ph-small ph-cart-small-attribute ph-idnr-li">'.$v2->attribute_title .' '.$v2->option_title.'</span>';
				 if (isset($v2->option_value) && urldecode($v2->option_value) != '') {
                    $p[] =  ': <span class="ph-small ph-cart-small-attribute">' . htmlspecialchars(urldecode($v2->option_value), ENT_QUOTES, 'UTF-8') . '</span>';
                }
				$p[] = '</li>';
				*/

				if ($pR) { $oPr[] = $pP->printLineColumns(array(' - ' .$v2->attribute_title .' '.$v2->option_title)); }

			}
			$p[] = '</ul></td>';
			$p[] = '<td colspan="8"></td>';
			$p[] = '</tr>';
		}

		if ($pR) {
			$brutto = (int)$v->quantity * $v->brutto;
			$oPr[] = $pP->printLineColumns(array((int)$v->quantity . ' x ' . $d['price']->getPriceFormat($v->brutto), $d['price']->getPriceFormat($brutto)));
		}

		$lastSaleNettoUnit 	= array();
		$lastSaleNetto 		= array();
		$lastSaleTax 		= array();
		$lastSaleBrutto 	= array();
		if (!empty($d['discounts'][$v->product_id_key]) && $d['type'] != 3) {

			$lastSaleNettoUnit[$v->product_id_key] 	= $nettoUnit;
			$lastSaleNetto[$v->product_id_key] 		= $netto;
			$lastSaleTax[$v->product_id_key] 		= $tax;
			$lastSaleBrutto[$v->product_id_key] 	= $brutto;


			foreach($d['discounts'][$v->product_id_key] as $k3 => $v3) {

				$nettoUnit3 							= $v3->netto;
				$netto3									= (int)$v->quantity * $v3->netto;
				$tax3 									= (int)$v->quantity * $v3->tax;
				$brutto3 								= (int)$v->quantity * $v3->brutto;

				$saleNettoUnit							= $lastSaleNettoUnit[$v->product_id_key] 	- $nettoUnit3;
				$saleNetto								= $lastSaleNetto[$v->product_id_key] 		- $netto3;
				$saleTax								= $lastSaleTax[$v->product_id_key] 			- $tax3;
				$saleBrutto								= $lastSaleBrutto[$v->product_id_key] 		- $brutto3;

				$lastSaleNettoUnit[$v->product_id_key] 	= $nettoUnit3;
				$lastSaleNetto[$v->product_id_key] 		= $netto3;
				$lastSaleTax[$v->product_id_key] 		= $tax3;
				$lastSaleBrutto[$v->product_id_key] 	= $brutto3;

				if ($display_discount_price_product == 2) {

					$p[] = '<tr '.$bProduct.'>';
					$p[] = '<td></td>';
					$p[] = '<td colspan="'.$cTitle.'">'.$v3->title.'</td>';
					$p[] = '<td style="text-align:center"></td>';
					if ($tax_calculation > 0) {
						$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($saleNettoUnit, 1).'</td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($saleNetto, 1).'</td>';
						$p[] = '<td style="text-align:right" colspan="1">'.$d['price']->getPriceFormat($saleTax, 1).'</td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($saleBrutto, 1).'</td>';
					} else {
						$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($saleNettoUnit, 1).'</td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($saleBrutto, 1).'</td>';
					}

					$p[] = '</tr>';

					if ($pR) {
						$oPr[] = $pP->printLineColumns(array($v3->title, $d['price']->getPriceFormat($saleBrutto, 1)));
					}
				} else if ($display_discount_price_product == 1) {

					$p[] = '<tr '.$bProduct.'>';
					$p[] = '<td></td>';
					$p[] = '<td colspan="'.$cTitle.'">'.$v3->title.'</td>';
					$p[] = '<td style="text-align:center"></td>';
					/*$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($nettoUnit3).'</td>';
					$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($netto3).'</td>';
					$p[] = '<td style="text-align:right" colspan="1">'.$d['price']->getPriceFormat($tax3).'</td>';
					$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($brutto3).'</td>';*/

					if ($tax_calculation > 0) {
						$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($nettoUnit3).'</td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($netto3).'</td>';
						$p[] = '<td style="text-align:right" colspan="1">'.$d['price']->getPriceFormat($tax3).'</td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($brutto3).'</td>';
					} else {
						$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($nettoUnit3).'</td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$d['price']->getPriceFormat($brutto3).'</td>';
					}

					$p[] = '</tr>';

					if ($pR) {
						$oPr[] = $pP->printLineColumns(array($v3->title, $d['price']->getPriceFormat($brutto3)));
					}
				}

			}
		}

	}

	if ($pR) { $oPr[] = $pP->printSeparator(); }

}

$o[] = implode("\n", $p);


if ($tax_calculation > 0 || $d['type'] == 3) {
	$o[] = '<tr><td colspan="12" '.$sepH.'>&nbsp;</td></tr>';
} else {
	$o[] = '<tr><td colspan="9" '.$sepH.'>&nbsp;</td></tr>';
}



// -----------
// TOTAL
// -----------
$t = array();
$toPay = '';

$tColspanLeft = 5;
$tColspanMid = 2;
$tColspanRight = 2;

if ($tax_calculation > 0) {
	$tColspanLeft = 7;
	$tColspanMid = 3;
	$tColspanRight = 2;
}

if (!empty($d['total'])) {
	foreach($d['total'] as $k => $v) {

		// display or not display shipping and payment methods with zero amount
		//if($v->amount == 0 && $v->amount_currency == 0 && $v->type != 'brutto' && $v->type != 'sbrutto' && $v->type != 'pbrutto') {
			if($v->amount == 0 && $v->amount_currency == 0 && $v->type != 'brutto') {
			// Don't display coupon if null

		} else if ($v->type == 'netto') {
			$t[] = '<tr '.$totalF.'>';
			$t[] = '<td colspan="'.$tColspanLeft.'"></td>';
			$t[] = '<td colspan="'.$tColspanMid.'"><b>'. PhocacartLanguage::renderTitle($v->title, $v->title_lang, array(0 => array($v->title_lang_suffix, ' '), 1 => array($v->title_lang_suffix2, ' '))).'</b></td>';
			$t[] = '<td style="text-align:right" colspan="'.$tColspanRight.'"><b>'.$d['price']->getPriceFormat($v->amount).'</b></td>';
			$t[] = '</tr>';

			if ($pR) { $oPr[] = $pP->printLineColumns(array(PhocacartLanguage::renderTitle($v->title, $v->title_lang, array(0 => array($v->title_lang_suffix, ' '), 1 => array($v->title_lang_suffix2, ' '))), $d['price']->getPriceFormat($v->amount))); }

		} else if ($v->type == 'brutto') {

			// Brutto or Brutto currency
			$amount = (isset($v->amount_currency) && $v->amount_currency > 0) ? $d['price']->getPriceFormat($v->amount_currency, 0, 1) : $d['price']->getPriceFormat($v->amount);

			$t[] = '<tr '.$totalF.'>';
			$t[] = '<td colspan="'.$tColspanLeft.'"></td>';
			$t[] = '<td colspan="'.$tColspanMid.'"><b>'.PhocacartLanguage::renderTitle($v->title, $v->title_lang, array(0 => array($v->title_lang_suffix, ' '), 1 => array($v->title_lang_suffix2, ' '))).'</b></td>';
			$t[] = '<td style="text-align:right" colspan="'.$tColspanRight.'"><b>'.$amount.'</b></td>';
			$t[] = '</tr>';


			if ($pR) {
				$oPr[] = $pP->printSeparator();
				$oPr[] = $pP->printLineColumns(array(PhocacartLanguage::renderTitle($v->title, $v->title_lang, array(0 => array($v->title_lang_suffix, ' '), 1 => array($v->title_lang_suffix2, ' '))), $amount), 0, 'pDoubleSize');
				$oPr[] = $pP->printFeed(2);
			}

			if ($d['type'] == 2) {
				$toPay = $amount;
			}

		} else if ($v->type == 'rounding') {

			// Rounding or rounding currency
			$amount = (isset($v->amount_currency) && $v->amount_currency > 0) ? $d['price']->getPriceFormat($v->amount_currency, 0, 1) : $d['price']->getPriceFormat($v->amount);

			$t[] = '<tr '.$totalF.'>';
			$t[] = '<td colspan="'.$tColspanLeft.'"></td>';
			$t[] = '<td colspan="'.$tColspanMid.'">'.PhocacartLanguage::renderTitle($v->title, $v->title_lang, array(0 => array($v->title_lang_suffix, ' '), 1 => array($v->title_lang_suffix2, ' '))).'</td>';
			$t[] = '<td style="text-align:right" colspan="'.$tColspanRight.'">'.$amount.'</td>';
			$t[] = '</tr>';

			if ($pR) { $oPr[] = $pP->printLineColumns(array(PhocacartLanguage::renderTitle($v->title, $v->title_lang, array(0 => array($v->title_lang_suffix, ' '), 1 => array($v->title_lang_suffix2, ' '))), $amount)); }

		} else {
			$t[] = '<tr '.$totalF.'>';
			$t[] = '<td colspan="'.$tColspanLeft.'"></td>';
			$t[] = '<td colspan="'.$tColspanMid.'">'.PhocacartLanguage::renderTitle($v->title, $v->title_lang, array(0 => array($v->title_lang_suffix, ' - '), 1 => array($v->title_lang_suffix2, ' '))).'</td>';
			$t[] = '<td style="text-align:right" colspan="'.$tColspanRight.'">'.$d['price']->getPriceFormat($v->amount).'</td>';
			$t[] = '</tr>';

			if ($pR) { $oPr[] = $pP->printLineColumns(array(PhocacartLanguage::renderTitle($v->title, $v->title_lang, array(0 => array($v->title_lang_suffix, ' - '), 1 => array($v->title_lang_suffix2, ' '))), $d['price']->getPriceFormat($v->amount))); }

		}


	}
}

if ($d['type'] != 3) {
	$o[] = implode("\n", $t);
}


if ($tax_calculation > 0 || $d['type'] == 3) {
	$o[] = '<tr><td colspan="12">&nbsp;</td></tr>';
} else {
	$o[] = '<tr><td colspan="9">&nbsp;</td></tr>';
}


// -----------
// TO PAY
// -----------
if ($toPay != '') {

	$o[] = '<tr class="ph-idnr-to-pay-box">';
	$o[] = '<td colspan="'.$tColspanLeft.'">&nbsp;</td>';
	$o[] = '<td colspan="'.$tColspanMid.'" '.$toPayS.'><b>'.Text::_('COM_PHOCACART_TO_PAY').'</b></td>';
	$o[] = '<td colspan="'.$tColspanRight.'" '.$toPaySV.'><b>'.$toPay.'</b></td>';
	$o[] = '</tr>';
}


$o[] = '</table>';// End box in


// -----------------------
// INVOICE MIDDLE DESCRIPTION
// -----------------------
if ($d['type'] == 2) {

	$invoiceMiddleDescArticle = '';
	if ($d['common']->invoice_spec_middle_desc != '') {
		$invoiceMiddleDescArticle = $d['common']->invoice_spec_middle_desc;
	} else if ((int)$invoice_global_middle_desc > 0) {
		$invoiceMiddleDescArticle = PhocacartRenderFront::renderArticle((int)$invoice_global_middle_desc, $d['format']);
	}

	if ($invoiceMiddleDescArticle != '') {
		$o[] = '<div '.$hrSmall.'>&nbsp;</div>';
		$invoiceMiddleDescArticle 	= PhocacartPdf::skipStartAndLastTag($invoiceMiddleDescArticle, 'p');

        if ($d['qrcode'] != '' && $d['format'] == 'pdf') {
            $invoiceMiddleDescArticle = str_replace('{invoiceqr}', '{phocapdfqrcode|' . urlencode($d['qrcode']) . '}', $invoiceMiddleDescArticle);
        }

		$invoiceMiddleDescArticle 	= PhocacartText::completeText($invoiceMiddleDescArticle, $d['preparereplace'], 1);
		//$invoiceMiddleDescArticle 	= PhocacartText::completeTextFormFields($invoiceMiddleDescArticle, $d['bas']['b'], 1);
		//$invoiceMiddleDescArticle 	= PhocacartText::completeTextFormFields($invoiceMiddleDescArticle, $d['bas']['s'], 2);
		$invoiceMiddleDescArticle 	= PhocacartText::completeTextFormFields($invoiceMiddleDescArticle, $d['bas']['b'], $d['bas']['s']);
		$o[] = '<table '.$bDesc.'><tr><td>'.$invoiceMiddleDescArticle.'</td></tr></table>';
	}
} else if ($d['type'] == 1) {
	$orderMiddleDescArticle = PhocacartRenderFront::renderArticle((int)$order_global_middle_desc, $d['format']);

	if ($orderMiddleDescArticle != '') {
		$o[] = '<div '.$hrSmall.'>&nbsp;</div>';
		$orderMiddleDescArticle 	= PhocacartPdf::skipStartAndLastTag($orderMiddleDescArticle, 'p');

        if ($d['qrcode'] != '' && $d['format'] == 'pdf') {
            $orderMiddleDescArticle = str_replace('{invoiceqr}', '{phocapdfqrcode|' . urlencode($d['qrcode']) . '}', $orderMiddleDescArticle);
        }

        $orderMiddleDescArticle 	= PhocacartText::completeText($orderMiddleDescArticle, $d['preparereplace'], 1);
		//$orderMiddleDescArticle 	= PhocacartText::completeTextFormFields($orderMiddleDescArticle, $d['bas']['b'], 1);
		//$orderMiddleDescArticle 	= PhocacartText::completeTextFormFields($orderMiddleDescArticle, $d['bas']['s'], 2);
		$orderMiddleDescArticle 	= PhocacartText::completeTextFormFields($orderMiddleDescArticle, $d['bas']['b'], $d['bas']['s']);
		$o[] = '<table '.$bDesc.'><tr><td>'.$orderMiddleDescArticle.'</td></tr></table>';
	}
} else if ($d['type'] == 3) {
	$dnMiddleDescArticle = PhocacartRenderFront::renderArticle((int)$dn_global_middle_desc, $d['format']);

	if ($dnMiddleDescArticle != '') {
		$o[] = '<div '.$hrSmall.'>&nbsp;</div>';
		$dnMiddleDescArticle 	= PhocacartPdf::skipStartAndLastTag($dnMiddleDescArticle, 'p');

        if ($d['qrcode'] != '' && $d['format'] == 'pdf') {
            $dnMiddleDescArticle = str_replace('{invoiceqr}', '{phocapdfqrcode|' . urlencode($d['qrcode']) . '}', $dnMiddleDescArticle);
        }

		$dnMiddleDescArticle 	= PhocacartText::completeText($dnMiddleDescArticle, $d['preparereplace'], 1);
		$dnMiddleDescArticle 	= PhocacartText::completeTextFormFields($dnMiddleDescArticle, $d['bas']['b'], $d['bas']['s']);
		$o[] = '<table '.$bDesc.'><tr><td>'.$dnMiddleDescArticle.'</td></tr></table>';
	}
}


// -----------------------
// INVOICE QR CODE, STAMP IMAGE
// -----------------------

// No PDF invoice - we can add QR code to no PDF document using TCPDF method but Phoca PDF must be enabled

if (($d['format'] == 'html' || $d['format'] == '') && $d['type'] == 2 && ($d['qrcode'] != '')) {

    $o[] = PhocacartUtils::getQrImage($d['qrcode']);
}


if ($d['format'] == 'pdf' && $d['type'] == 2 && ($d['qrcode'] != '' || $pdf_invoice_signature_image != '')) {
	$o[] = '<div>&nbsp;</div><div>&nbsp;</div>';
	$o[] = '<table>';// End box in
	$o[] = '<tr><td>';

	if ($pdf_invoice_qr_information != '') {
		$o[] = '<span '.$bQrInfo.'>'.$pdf_invoice_qr_information . '</span><br />';
	}



	if ($d['qrcode'] != '') {
		$o[] = '{phocapdfqrcode|'.urlencode($d['qrcode']).'}';
	}
	$o[] = '</td><td>';
	if ($pdf_invoice_signature_image != '') {
		$o[] = '<img src="'.Uri::root().''.$pdf_invoice_signature_image.'" style="width:80"/>';
	}
	$o[] = '</td></tr>';
	$o[] = '</table>';
}

// -----------------------
// TAX RECAPITULATION
// -----------------------
if (($display_tax_recapitulation_invoice == 1 && $d['type'] == 2 ) ||  ($display_tax_recapitulation_pos == 1 && $d['type'] == 4 )) {



	if (!empty($d['taxrecapitulation'])) {


		$o[] = '<h3>'.Text::_('COM_PHOCACART_TAX_RECAPITULATION').'</h3>';
		if ($pR) {
			$oPr[] = $pP->printLine(array(Text::_('COM_PHOCACART_TAX_RECAPITULATION')), 'pLeft');
		}


		$o[] = '<table '.$taxRecTable.'>';
		$o[] = '<tr>';
		$o[] = '<th '.$taxRecTd.'>'.Text::_('COM_PHOCACART_TITLE').'</th>';
		$o[] = '<th '.$taxRecTd.'>'.Text::_('COM_PHOCACART_TAX_BASIS').'</th>';
		$o[] = '<th '.$taxRecTd.'>'.Text::_('COM_PHOCACART_TAX').'</th>';
		$o[] = '<th '.$taxRecTd.'>'.Text::_('COM_PHOCACART_TOTAL').'</th>';
		//$o[] = '<th>'.Text::_('COM_PHOCACART_TOTAL').' '.Text::_('COM_PHOCACART_CURRENCY').'</td>';
		$o[] = '</tr>';


		foreach($d['taxrecapitulation'] as $k => $v) {


			if (isset($v->amount_brutto_currency) && $v->amount_brutto_currency > 0) {
				$amountBrutto		= $v->amount_brutto_currency;
				$amountBruttoFormat = $d['price']->getPriceFormat($v->amount_brutto_currency, 0, 1);
			} else {
				$amountBrutto		= $v->amount_brutto;
				$amountBruttoFormat = $d['price']->getPriceFormat($v->amount_brutto);
			}

			$amountNettoFormat 	= $v->amount_netto > 0 ? $d['price']->getPriceFormat($v->amount_netto) : '';
			$amountTaxFormat 	= $v->amount_tax > 0 ? $d['price']->getPriceFormat($v->amount_tax) : '';
			$title				= $v->title;

		/*	if ($v->type == 'trcrounding') {
				// In administration edit: Rounding (Incl. Tax Recapitulation Rounding)
				// In documents (invoice): Rouning
				// Skip "(Incl. Tax Recapitulation Rounding)" in documents
				$title = Text::_('COM_PHOCACART_ROUNDING');
			}*/

			if ($v->type == 'brutto') {
				$amountBruttoFormat = '<span class="ph-b">'.$amountBruttoFormat.'</span>';
				$amountNettoFormat = '';
				$amountTaxFormat = '';
			}


			if ($v->type == 'rounding') {
				// Don't display rounding here, only trcrounding (calculation rounding + tax recapitulation rounding)
			} else if ($amountBrutto > 0 || $amountBrutto < 0) {

				$o[] = '<tr>';
				$o[] = '<td '.$taxRecTd.'>'.$title.'</td>';
				$o[] = '<td '.$taxRecTdRight.'>'.$amountNettoFormat.'</td>';
				$o[] = '<td '.$taxRecTdRight.'>'.$amountTaxFormat.'</td>';
				$o[] = '<td '.$taxRecTdRight.'>'.$amountBruttoFormat.'</td>';
				$o[] = '</tr>';

			}

			// POS Receipt - only tax information
			if ($pR && $v->type == 'tax') {
				$oPr[] = $pP->printLineColumns(array($title, $d['price']->getPriceFormat($v->amount_tax)));
			}

		}

		$o[] = '</table>';
		if ($pR) {
			$oPr[] = $pP->printFeed(1);
		}
	}
	/*$orderCalc 		= new PhocacartOrderCalculation();
	$calcItems		= array();
	$calcItems[0]	= $d['common'];
	$orderCalc->calculateOrderItems($calcItems);
	$calcTotal		= $orderCalc->getTotal();
	$taxes 			= PhocacartTax::getAllTaxes();
	if (!empty($calcTotal)) {
		foreach ($calcTotal as $k => $v) {


			if (!empty($v)) {
				$d['price']->setCurrency($k);


				if ($pR) {
					$oPr[] = $pP->printLine(array(Text::_('COM_PHOCACART_TAX_RECAPITULATION')), 'pLeft');
				}

				if (!empty($v['tax'])) {

					$o[] = '<table '.$taxRecTable.'>';
					$o[] = '<tr><th colspan="2">'.Text::_('COM_PHOCACART_TAX_RECAPITULATION').'</th></tr>';

					foreach($v['tax'] as $kT => $vT) {

						$calcTitle = isset($taxes[$kT]['title']) ? $taxes[$kT]['title'] : '';

						$o[] = '<tr><td '.$taxRecTd.'>'.$calcTitle.'</td>';
						$o[] = '<td '.$taxRecTd.'>'.$d['price']->getPriceFormat($vT,0,1) . '</td></tr>';

						if ($pR) {
							$oPr[] = $pP->printLineColumns(array($calcTitle, $d['price']->getPriceFormat($vT,0,1)));
						}
					}

					$o[] = '</table>';
					if ($pR) {
						$oPr[] = $pP->printFeed(1);
					}
				}
			}
		}
	}*/
}


// -----------------------
// POINTS RECEIVED
// -----------------------

if (($display_reward_points_invoice == 1 && $d['type'] == 2 ) ||  ($display_reward_points_pos == 1 && $d['type'] == 4 )) {
	if ((int)$d['common']->user_id > 0 && (int)$d['common']->id > 0) {
		$pointsUser 	= PhocacartReward::getTotalPointsByUserIdExceptCurrentOrder($d['common']->user_id, $d['common']->id);
		$pointsOrder 	= PhocacartReward::getTotalPointsByOrderId($d['common']->id);


		$o[] = '<div>'.Text::_('COM_PHOCACART_YOUR_CURRENT_REWARD_POINTS_BALANCE').': '.$pointsUser.'</div>';
		$o[] = '<div>'.Text::_('COM_PHOCACART_POINTS_RECEIVED_FOR_THIS_PURCHASE').': '.$pointsOrder.'</div>';

		if ($pR) {
			$oPr[] = $pP->printLineColumns(array(Text::_('COM_PHOCACART_YOUR_CURRENT_REWARD_POINTS_BALANCE').': ', $pointsUser));
			$oPr[] = $pP->printLineColumns(array(Text::_('COM_PHOCACART_POINTS_RECEIVED_FOR_THIS_PURCHASE'). ': ', $pointsOrder));
			$oPr[] = $pP->printFeed(1);
		}
	}
}

// -----------------------
// INVOICE BOTTOM DESCRIPTION
// -----------------------
if ($d['type'] == 2) {

	$invoiceBottomDescArticle = '';
	if ($d['common']->invoice_spec_bottom_desc != '') {
		$invoiceBottomDescArticle = $d['common']->invoice_spec_bottom_desc;
	} else if ((int)$invoice_global_bottom_desc > 0) {
		$invoiceBottomDescArticle = PhocacartRenderFront::renderArticle((int)$invoice_global_bottom_desc, $d['format']);
	}

	if ($invoiceBottomDescArticle != '') {
		$o[] = '<div '.$hrSmall.'>&nbsp;</div>';
		$invoiceBottomDescArticle 	= PhocacartPdf::skipStartAndLastTag($invoiceBottomDescArticle, 'p');

        if ($d['qrcode'] != '' && $d['format'] == 'pdf') {
            $invoiceBottomDescArticle = str_replace('{invoiceqr}', '{phocapdfqrcode|' . urlencode($d['qrcode']) . '}', $invoiceBottomDescArticle);
        }

        $invoiceBottomDescArticle 	= PhocacartText::completeText($invoiceBottomDescArticle, $d['preparereplace'], 1);
		//$invoiceBottomDescArticle 	= PhocacartText::completeTextFormFields($invoiceBottomDescArticle, $d['bas']['b'], 1);
		//$invoiceBottomDescArticle 	= PhocacartText::completeTextFormFields($invoiceBottomDescArticle, $d['bas']['s'], 2);
		$invoiceBottomDescArticle 	= PhocacartText::completeTextFormFields($invoiceBottomDescArticle, $d['bas']['b'], $d['bas']['s']);
		$o[] = '<table '.$bDesc.'><tr><td>'.$invoiceBottomDescArticle.'</td></tr></table>';
	}
} else if ($d['type'] == 1) {
	$orderBottomDescArticle = PhocacartRenderFront::renderArticle((int)$order_global_bottom_desc, $d['format']);

	if ($orderBottomDescArticle != '') {
		$o[] = '<div '.$hrSmall.'>&nbsp;</div>';
		$orderBottomDescArticle 	= PhocacartPdf::skipStartAndLastTag($orderBottomDescArticle, 'p');

        if ($d['qrcode'] != '' && $d['format'] == 'pdf') {
            $orderBottomDescArticle = str_replace('{invoiceqr}', '{phocapdfqrcode|' . urlencode($d['qrcode']) . '}', $orderBottomDescArticle);
        }
		$orderBottomDescArticle 	= PhocacartText::completeText($orderBottomDescArticle, $d['preparereplace'], 1);
		//$orderBottomDescArticle 	= PhocacartText::completeTextFormFields($orderBottomDescArticle, $d['bas']['b'], 1);
		//$orderBottomDescArticle 	= PhocacartText::completeTextFormFields($orderBottomDescArticle, $d['bas']['s'], 2);
		$orderBottomDescArticle 	= PhocacartText::completeTextFormFields($orderBottomDescArticle, $d['bas']['b'], $d['bas']['s']);
		$o[] = '<table '.$bDesc.'><tr><td>'.$orderBottomDescArticle.'</td></tr></table>';


	}
} else if ($d['type'] == 3) {
	$dnBottomDescArticle = PhocacartRenderFront::renderArticle((int)$dn_global_bottom_desc, $d['format']);

	if ($dnBottomDescArticle != '') {
		$o[] = '<div '.$hrSmall.'>&nbsp;</div>';
		$dnBottomDescArticle 	= PhocacartPdf::skipStartAndLastTag($dnBottomDescArticle, 'p');

        if ($d['qrcode'] != '' && $d['format'] == 'pdf') {
            $dnBottomDescArticle = str_replace('{invoiceqr}', '{phocapdfqrcode|' . urlencode($d['qrcode']) . '}', $dnBottomDescArticle);
        }

		$dnBottomDescArticle 	= PhocacartText::completeText($dnBottomDescArticle, $d['preparereplace'], 1);
		$dnBottomDescArticle 	= PhocacartText::completeTextFormFields($dnBottomDescArticle, $d['bas']['b'], $d['bas']['s']);
		$o[] = '<table '.$bDesc.'><tr><td>'.$dnBottomDescArticle.'</td></tr></table>';
	}
}



$o[] = '</div>';// End box


// POS FOOTER
if ($pR) {



	if (isset($d['common']->amount_tendered) && $d['common']->amount_tendered > 0 && isset($d['common']->amount_change) && ($d['common']->amount_change > 0 || $d['common']->amount_change == 0)) {
		//$oPr[] = $pP->printLine(array(Text::_('COM_PHOCACART_RECEIPT_AMOUNT_TENDERED').': '.$d['price']->getPriceFormat($d['common']->amount_tendered)), 'pLeft');
		//$oPr[] = $pP->printLine(array(Text::_('COM_PHOCACART_RECEIPT_AMOUNT_CHANGED').': '.$d['price']->getPriceFormat($d['common']->amount_change)), 'pLeft');
		$oPr[] = $pP->printLineColumns(array(Text::_('COM_PHOCACART_RECEIPT_AMOUNT_TENDERED').': ', $d['price']->getPriceFormat($d['common']->amount_tendered)));
		$oPr[] = $pP->printLineColumns(array(Text::_('COM_PHOCACART_RECEIPT_AMOUNT_CHANGED').': ', $d['price']->getPriceFormat($d['common']->amount_change)));
		$oPr[] = $pP->printFeed(1);
	}


	$oPr[] = $pP->printLine(array(Text::_('COM_PHOCACART_RECEIPT_NR').': '.PhocacartOrder::getReceiptNumber($d['common']->id, $d['common']->date, $d['common']->receipt_number)), 'pLeft');
	$oPr[] = $pP->printLine(array(Text::_('COM_PHOCACART_PURCHASE_DATE').': '.HTMLHelper::date($d['common']->date, 'DATE_FORMAT_LC6')), 'pLeft');
	$oPr[] = $pP->printFeed(1);

	$storeInfoFooterPos = array();
	if ($store_info_footer_pos != '') {
		$store_info_footer_pos 	= PhocacartText::completeText($store_info_footer_pos, $d['preparereplace'], 1);
		$storeInfoFooterPos = explode("\n", strip_tags($store_info_footer_pos));
	}

	$oPr[] = $pP->printLine($storeInfoFooterPos, 'pCenter');

}

PluginHelper::importPlugin( 'system' );
PluginHelper::importPlugin('plgSystemMultilanguagesck');

if ($pR) {
	//$oPr2 = implode("\n", $oPr);
	$oPr2 = implode("", $oPr);// new rows set in print library

	// Run content plugins e.g. because of translation
	// Disable emailclock for PDF | MAIL
	if ($d['format'] == 'pdf' || $d['format'] == 'mail') {
		$oPr2 = '{emailcloak=off}' . $oPr2;
	}

	$oPr2 = HTMLHelper::_('content.prepare', $oPr2);

	Dispatcher::dispatchChangeText($oPr2);

	echo $oPr2;
} else {

	$o2 = implode("\n", $o);

	// Run content plugins e.g. because of translation
	// Disable emailclock for PDF | MAIL
	if ($d['format'] == 'pdf' || $d['format'] == 'mail') {
		$o2 = '{emailcloak=off}' . $o2;
	}
	$o2 = HTMLHelper::_('content.prepare', $o2);

	Dispatcher::dispatchChangeText($o2);
	echo $o2;
}
