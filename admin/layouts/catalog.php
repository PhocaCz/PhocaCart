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
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$d 		        = $displayData;
$price 	        = new PhocacartPrice();
$taxes 	        = PhocacartTax::getAllTaxesIncludingCountryRegion();
$pathItem 		= PhocacartPath::getPath('productimage');



$p = array();
$p['printed_catalog_enable'] 	                = $d['params']->get( 'printed_catalog_enable', 0);
$p['printed_catalog_header'] 	                = $d['params']->get( 'printed_catalog_header', '');
$p['printed_catalog_document_title'] 	        = $d['params']->get( 'printed_catalog_document_title', '');
$p['printed_catalog_css'] 	                    = $d['params']->get( 'printed_catalog_css', '');
$p['printed_catalog_display_category_title'] 	= $d['params']->get( 'printed_catalog_display_category_title', 1);
$p['printed_catalog_display_price_label'] 	    = $d['params']->get( 'printed_catalog_display_price_label', 0);


if ($p['printed_catalog_enable'] == 0) {
    echo Text::_('COM_PHOCACART_ERROR_CREATING_PRINTED_CATALOG_DISABLED');
    exit;
}


$document = Factory::getDocument();
$document->setTitle(Text::_($p['printed_catalog_document_title']));


$s = '<style>';
/*echo '.ph-catalog-doc {line-height:0.8;}
.ph-catalog-header {line-height:0.5;}
.ph-catalog-img {height: auto;width: auto;}
.ph-catalog-col1 {width: 10%;padding-right:3px;}
.ph-catalog-col2 {width: 90%;padding-left:3px;}
.ph-catalog-price {text-align: right;font-weight: bold;}
.ph-catalog-sep {border-bottom: 1px solid #f0f0f0;}
.ph-catalog-sep-margin {font-size: 8px;}
.ph-catalog-ean {line-height:0.1;}
.ph-catalog-table, .ph-catalog-table-in {width: 100%;}
.ph-catalog-title {font-weight:bold;font-size:160%;color:blue;line-height:0.8;}
.ph-catalog-desc-long {font-size: 85%;line-height:1;}
.ph-catalog-price {line-height: 1;font-size: 90%;text-align:right;}
.ph-catalog-price-table {width: 200pt;}
.ph-catalog-price-item {text-align:right; font-weight: bold;}
.ph-catalog-price-item-txt {text-align:left;}';*/
$s .= trim(strip_tags($p['printed_catalog_css']));
$s .= '</style>';

if ($d['format'] == 'pdf'){
    echo $s;
} else if ($d['format'] == 'html'){
    $document->addCustomTag($s);
} else if ($d['format'] == 'raw') {
    echo '<html><head><title>'.Text::_('COM_PHOCACART_CATALOG').'</title>'.$s.'</head><body>';
}




echo '<div class="ph-catalog-doc">';// start doc

// HEADER
$header = PhocacartRenderFront::renderArticle($p['printed_catalog_header'], $d['format']);
if ($header != '') {
    echo '<div class="ph-catalog-header">';
    echo $header;
    echo '</div>';
}

echo '<table class="ph-catalog-table" cellspacing="0" cellpadding="0" >';


// ITEMS
$previousCatid = 0;
foreach($d['items'] as $k => $v) {


    // Category Title
    if ($p['printed_catalog_display_category_title'] == 1 && $v['category_title'] != '' && $v['category_id'] != $previousCatid) {
        echo '<tr nobr="true"><td style="width:100%">';
        echo '<div class="ph-catalog-category-header">'.$v['category_title']. '</div>';
        $previousCatid = $v['category_id'];
        echo '</td></tr>';
    }


    echo '<tr nobr="true"><td style="width:100%">';


    echo '<table class="ph-catalog-table-in" cellspacing="0" cellpadding="1">';


    echo '<tr>';

	// 1) COLUMN - Image
	echo '<td class="ph-catalog-col1">';
	if ($v['image'] != '') {

	    $image 	= PhocacartImage::getThumbnailName($pathItem, $v['image'], 'small');
	    echo '<img class="ph-catalog-img" src="'. Uri::root(true) . '/' . $image->rel.'" alt="'.PhocacartText::filterValue($v['title'], 'text').'" />';
    }
	echo '</td>';

	// 2) COLUMN - Text
	echo '<td class="ph-catalog-col2">';
	echo '<div class="ph-catalog-title">'. $v['title'].'</div>';

	if ($v['description_long'] != '') {
	    echo '<div class="ph-catalog-desc-long">'. $v['description_long'].'</div>';
    } else if ($v['description']) {
	    echo '<div class="ph-catalog-desc">'. $v['description'].'</div>';
    } else if ($v['features']) {
	    echo '<div class="ph-catalog-features">'. $v['features'].'</div>';
    }


	echo '<table><tr><td>';

	// 2)1) SUBCLUMN SKU EAN
	// SKU
	if ($v['sku'] != '') {

	     echo '<div class="ph-catalog-sku">'.$v['sku'].'</div>';

    }

	// EAN
	if ($v['ean'] != '') {

	    if ($d['format'] == 'pdf') {
            echo '<div class="ph-catalog-ean">{phocapdfeancode|'.urlencode((int)$v['ean']).'}</div>';
	    } else {
	        echo '<div class="ph-catalog-ean">'.(int)$v['ean'].'</div>';
        }

    }

	echo '</td>';

	echo '<td>';

    // 2)2) SUBCOLUMN PRICE
    $priceItems	= $price->getPriceItems($v['price'], $v['taxid'], $v['taxrate'], $v['taxcalculationtype'], $v['taxtitle'], $v['unit_amount'], $v['unit_unit'], 1, 1, NULL);


	//echo '<div class="ph-catalog-price">'. $price->getPriceFormat($v['price']).'</div>';

    echo '<div class="ph-catalog-price"><br />';

    if (!empty($priceItems)) {

        if ($p['printed_catalog_display_price_label'] == 1) {
            $priceItems['nettotxt']     .= ': ';
            $priceItems['taxtxt']       .= ': ';
            $priceItems['bruttotxt']    .= ': ';
        } else {
            $priceItems['nettotxt']     = '';
            $priceItems['taxtxt']       = '';
            $priceItems['bruttotxt']    = '';
        }


        echo '<table class="ph-catalog-price-table">';

        if ($priceItems['netto'] != 0 && $priceItems['netto'] != $priceItems['brutto']) {
            echo '<tr><td class="ph-catalog-price-item-txt">' . $priceItems['nettotxt'] . ' </td><td class="ph-catalog-price-item">' . $priceItems['nettoformat'] . '</td></tr>';
        } else {
            echo '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
        }
        if ($priceItems['tax'] != 0 && $priceItems['netto'] != $priceItems['brutto']) {
            echo '<tr><td class="ph-catalog-price-item-txt">' . $priceItems['taxtxt'] . ' </td><td class="ph-catalog-price-item">' . $priceItems['taxformat'] . '</td></tr>';
        } else {
            echo '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
        }
        if ($priceItems['brutto'] != 0) {
            echo '<tr><td class="ph-catalog-price-item-txt">' . $priceItems['bruttotxt'] . ' </td><td class="ph-catalog-price-item">' . $priceItems['bruttoformat'] . '</td></tr>';
        } else {
            echo '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
        }

        echo '</table>';
    }

    echo '</div>';

    echo '</td></tr></table>';


	echo '</td>';

	echo '</tr>';
	echo '</table>';


	echo '<div class="ph-catalog-sep"></div>';
	echo '<div class="ph-catalog-sep-margin">&nbsp;</div>';
	echo '</td></tr>';

}


echo '</table>';

echo '</div>';// end doc

echo '<p>&nbsp;</p>';


if ($d['format'] == 'raw') {
    echo '</body></html>';
}


