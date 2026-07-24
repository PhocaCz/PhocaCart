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
$taxes 	        = PhocacartTax::getAllTaxesIncludingCountryRegionPlugin();
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
$s .= '.ph-catalog-category-header { font-size: 130%; font-weight: bold; border-bottom: 1px solid #000; margin-bottom: 8px; }';
$s .= '.ph-catalog-item-table { width: 100%; }';
$s .= '.ph-catalog-title { font-weight: bold; font-size: 110%; }';
$s .= '.ph-catalog-desc-long, .ph-catalog-desc, .ph-catalog-features { font-size: 85%; }';
$s .= '.ph-catalog-sku, .ph-catalog-ean { font-size: 80%; }';
$s .= '.ph-catalog-sep { border-bottom: 1px solid #e0e0e0; margin-top: 5px; margin-bottom: 5px; }';
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

// ITEMS
// Order items based on category
uasort($d['items'], function($a, $b) {
    $cmp = strcmp($a['category_title'], $b['category_title']);
    if ($cmp === 0) {
        // order by product title
       // $cmp = strcmp($a['title'], $b['title']);
    }
    return $cmp;
});

$previousCatid = 0;



foreach($d['items'] as $k => $v) {



    // Category Title
    if ($p['printed_catalog_display_category_title'] == 1 && $v['category_title'] != '' && $v['category_id'] != $previousCatid) {
        echo '<div class="ph-catalog-category-header">'.$v['category_title']. '</div>';
        $previousCatid = $v['category_id'];
    }


    echo '<table class="ph-catalog-item-table" width="100%" cellspacing="0" cellpadding="2" border="0" nobr="true">';
    echo '<tr>';

	// 1) COLUMN - Image
	echo '<td width="20%" valign="top" align="center" class="ph-catalog-col1">';
	if ($v['image'] != '') {

	    $image 	= PhocacartImage::getThumbnailName($pathItem, $v['image'], 'small');

        if ($d['format'] == 'pdf') {

            $sigAbs = JPATH_ROOT . '/' . ltrim($image->rel, '/');

            if (file_exists($sigAbs)) {
                $sigSize = @getimagesize($sigAbs);
                $sigMime = $sigSize['mime'] ?? 'image/png';
                $sigAttr = $sigSize ? ' ' . $sigSize[3] : '';
                $base64Sig = 'data:' . $sigMime . ';base64,' . base64_encode(file_get_contents($sigAbs));
                echo '<img class="ph-catalog-img" src="' . $base64Sig . '"' . $sigAttr . ' alt="'.PhocacartText::filterValue($v['title'], 'text').'" />';
            } else {
                echo '&nbsp;';
            }
        } else {
            echo '<img class="ph-catalog-img" src="'. Uri::root(true) . '/' . $image->rel.'" alt="'.PhocacartText::filterValue($v['title'], 'text').'" />';
        }
    } else {
        echo '&nbsp;';
    }
	echo '</td>';

	// 2) COLUMN - Text
	echo '<td width="55%" valign="top" class="ph-catalog-col2">';
	echo '<div class="ph-catalog-title">'. $v['title'].'</div>';

	if ($v['description_long'] != '') {
	    echo '<div class="ph-catalog-desc-long">'. $v['description_long'].'</div>';
    } else if ($v['description']) {
	    echo '<div class="ph-catalog-desc">'. $v['description'].'</div>';
    } else if ($v['features']) {
	    echo '<div class="ph-catalog-features">'. $v['features'].'</div>';
    }

	// 2)1) SUBCLUMN SKU EAN
	// SKU
	if ($v['sku'] != '') {
	     echo '<div class="ph-catalog-sku">SKU: '.$v['sku'].'</div>';
    }

	// EAN
	if ($v['ean'] != '') {
        if ($d['format'] == 'pdf') {
            echo '<div class="ph-catalog-ean">{phocapdfeancode|'.$v['ean'].'}</div>';
        } else {
             echo '<div class="ph-catalog-ean">EAN: '.$v['ean'].'</div>';
        }
    }

	echo '</td>';

	// 2)2) SUBCOLUMN PRICE
	echo '<td width="25%" valign="top" align="right" class="ph-catalog-col3">';
    $priceItems	= $price->getPriceItems($v['price'], $v['taxid'], $v['taxrate'], $v['taxcalculationtype'], $v['taxtitle'], $v['unit_amount'], $v['unit_unit'], 1, 1, NULL, $v['taxhide']);


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


        $displayPriceItems = PhocaCartPrice::displayPriceItems($priceItems, 'catalog');

        if ($displayPriceItems['netto'] == 1) {
            echo '<span class="ph-catalog-price-item-txt">' . $priceItems['nettotxt'] . '</span> <span class="ph-catalog-price-item">' . $priceItems['nettoformat'] . '</span><br />';
        }

        if ($displayPriceItems['tax'] == 1) {
            echo '<span class="ph-catalog-price-item-txt">' . $priceItems['taxtxt'] . '</span> <span class="ph-catalog-price-item">' . $priceItems['taxformat'] . '</span><br />';
        }

        if ($displayPriceItems['brutto'] == 1) {
            echo '<b><span class="ph-catalog-price-item-txt">' . $priceItems['bruttotxt'] . '</span> <span class="ph-catalog-price-item">' . $priceItems['bruttoformat'] . '</span></b>';
        }
    }


	echo '</td>';

	echo '</tr>';
	echo '</table>';


	echo '<div class="ph-catalog-sep"></div>';

}


echo '</div>';// end doc


if ($d['format'] == 'raw') {
    echo '</body></html>';
}
