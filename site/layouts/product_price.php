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
$paramsC 					= PhocacartUtils::getComponentParameters();
$zero_price_text			= $paramsC->get( 'zero_price_text', '' );
$zero_price_label			= $paramsC->get( 'zero_price_label', '' );
$price_on_demand_text       = $paramsC->get( 'price_on_demand_text', '' );
$price_on_demand_label      = $paramsC->get( 'price_on_demand_label', '' );
if ($zero_price_label == '0') {

}

$d 			= $displayData;
$classPS	= 'ph-standard';// class price suffix
if (isset($d['discount']) && $d['discount']) {
	$classPS	= 'ph-line-through';
}


?>
<div id="phItemPriceBox<?php echo $d['typeview'] . (int)$d['product_id']; ?>">
	<div class="<?php echo $d['class']; ?>">
	<?php if (isset($d['priceitemsorig']['bruttoformat']) && $d['priceitemsorig']['bruttoformat']) { ?>
		<div class="ph-price-txt ph-price-original-txt"><?php echo Text::_('COM_PHOCACART_ORIGINAL_PRICE') ?></div>
		<div class="ph-price-original"><?php echo $d['priceitemsorig']['bruttoformat'] ?></div>
	<?php } ?>

	<?php /*
	if ($d['priceitemsorig']['brutto'] > $d['priceitems']['brutto']) {
		if (isset($d['priceitemsorig']['bruttoformat']) && $d['priceitemsorig']['bruttoformat']) { ?>
			<div class="ph-price-txt"><?php echo Text::_('COM_PHOCACART_ORIGINAL_PRICE') ?></div>
			<div class="ph-price-original"><?php echo $d['priceitemsorig']['bruttoformat'] ?></div>
	<?php }
	} */ ?>
	<?php


    // Display Price - there are a lot of variants
    // a) when e.g. price is zero, different text can be displayed
    // b) when product type (PRODUCTTYPE) is "Price On Demand" - different text can be displayed (it can overwrite zero_price but if not set, zero_price text and label are active
    $labelNetto     = '';
    $labelTax       = '';
    $labelBrutto    = '';

    $priceNetto     = '';
    $priceTax       = '';
    $priceBrutto    = '';

    $displayPrice   = 1;// At start display price = yes. If there will be some condition which will hide the price then use the information e.g. for discount prices (even disable them)
    $displayLabel   = 1;
    if ($d['priceitems']['netto'] && $d['priceitems']['taxcalc'] > 0 && ($d['priceitems']['netto'] != $d['priceitems']['brutto'])) {
		$labelNetto = '<div class="ph-price-txt ph-price-netto-txt '.$classPS.'-txt">'. $d['priceitems']['nettotxt'].'</div>';
		$priceNetto = '<div class="ph-price-netto '.$classPS.'">'.$d['priceitems']['nettoformat'].'</div>';
	}

	if ($d['priceitems']['tax'] && $d['priceitems']['taxcalc'] > 0) {
		$labelTax = '<div class="ph-tax-txt '.$classPS.'-txt">'.$d['priceitems']['taxtxt'].'</div>';
		$priceTax = '<div class="ph-tax '.$classPS.'">'.$d['priceitems']['taxformat'].'</div>';
	}

	if (isset($d['priceitems']['brutto'])) {
		$labelBrutto = '<div class="ph-price-txt ph-price-brutto-txt '.$classPS.'-txt">'.$d['priceitems']['bruttotxt'].'</div>';
		$priceBrutto = '<div class="ph-price-brutto '.$classPS.'">'.$d['priceitems']['bruttoformat'].'</div>';
	}


    if (isset($d['priceitems']['brutto']) && $d['priceitems']['brutto'] == 0 && isset($d['zero_price']) && $d['zero_price'] == 1) {

		// Text and Label instead of zero price
		// Label - Nothing | Custom Text | Standard "Price" String (ONLY IN CASE THE PRICE IS ZERO)
        if ($zero_price_label == '0') {
			$labelBrutto = '<div class="ph-price-txt '.$classPS.'-txt"></div>';
			$displayLabel   = 0;
		} else if ($zero_price_label != '') {
			$labelBrutto = '<div class="ph-price-txt '.$classPS.'-txt">'.Text::_($zero_price_label).'</div>';
			$displayLabel   = 0;
		} else {
			$labelBrutto = '<div class="ph-price-txt '.$classPS.'-txt">'.$d['priceitems']['bruttotxt'].'</div>';
		}

        // Price - Custom Text | Standard Price (ONLY IN CASE THE PRICE IS ZERO)
		if ($zero_price_text == '0') {
			$priceBrutto = '<div class="ph-price-brutto '.$classPS.'>-txt"></div>';
			$displayPrice   = 0;
		} else if ($zero_price_text != '') {
			$priceBrutto = '<div class="ph-price-brutto '.$classPS.'">'.Text::_($zero_price_text).'</div>';
			$displayPrice   = 0;
		} else {
			$priceBrutto = '<div class="ph-price-brutto '.$classPS.'">'.$d['priceitems']['bruttoformat'].'</div>';
		}



    }

    if (isset($d['type']) && $d['type'] == 3) {



        if ($price_on_demand_label == '0') {
            $labelBrutto    = '<div class="ph-price-txt '.$classPS.'-txt"></div>';
            //$displayPrice   = 0;
            $displayLabel   = 0;
        } else if ($price_on_demand_label != '') {
            $labelBrutto    = '<div class="ph-price-txt '.$classPS.'-txt">'.Text::_($price_on_demand_label).'</div>';
            //$displayPrice   = 0;
            $displayLabel   = 0;
        }

        if ($price_on_demand_text == '0') {
            $priceBrutto    = '<div class="ph-price-brutto '.$classPS.'-txt"></div>';
            $displayPrice   = 0;
        } else if ($price_on_demand_text != '') {
            $priceBrutto    = '<div class="ph-price-brutto '.$classPS.'-txt">'.Text::_($price_on_demand_text).'</div>';
            $displayPrice   = 0;
        }
    }

    if ($displayLabel == 0) {
        $labelNetto     = '';
        $labelTax       = '';
    }

    if ($displayPrice == 0) {
        $priceNetto     = '';
        $priceTax       = '';
    }



    echo $labelNetto . $priceNetto;
    echo $labelTax . $priceTax;
    echo $labelBrutto . $priceBrutto;

	?>




	<?php

	// PRODUCT DISCOUNT
	if (isset ($d['discount']) && $d['discount'] && $displayPrice == 1) { ?>
		<?php if ($d['priceitemsdiscount']['netto'] && $d['priceitemsdiscount']['taxcalc'] > 0
                    && ($d['priceitemsdiscount']['brutto'] != $d['priceitemsdiscount']['netto'])) { ?>
			<div class="ph-price-txt ph-price-netto-txt ph-price-discount"><?php echo $d['priceitemsdiscount']['nettotxt'] ?></div>
			<div class="ph-price-netto ph-price-discount"><?php echo $d['priceitemsdiscount']['nettoformat'] ?></div>
		<?php } ?>

		<?php if ($d['priceitemsdiscount']['tax'] && $d['priceitemsdiscount']['taxcalc'] > 0) { ?>
			<div class="ph-tax-txt ph-price-discount"><?php echo $d['priceitemsdiscount']['taxtxt'] ?></div>
			<div class="ph-tax ph-price-discount"><?php echo $d['priceitemsdiscount']['taxformat'] ?></div>
		<?php } ?>

		<?php if ($d['priceitemsdiscount']['brutto']) { ?>
			<div class="ph-price-txt ph-price-brutto-txt ph-price-discount"><?php echo $d['priceitemsdiscount']['bruttotxt'] ?></div>
			<div class="ph-price-brutto ph-price-discount ph-price-discount-product"><?php echo $d['priceitemsdiscount']['bruttoformat'] ?></div>
		<?php } ?>
	<?php }?>

	<?php
	// CART DISCOUNT DISPLAYED IN PRODUCT VIEWS (under specific conditions only)
	if (isset ($d['discountcart']) && $d['discountcart'] && $displayPrice == 1) { ?>
		<?php if ($d['priceitemsdiscountcart']['netto'] && $d['priceitemsdiscountcart']['taxcalc'] > 0
                && ($d['priceitemsdiscountcart']['brutto'] != $d['priceitemsdiscountcart']['netto'])) { ?>
			<div class="ph-price-txt ph-price-netto-txt ph-price-discount"><?php echo $d['priceitemsdiscountcart']['nettotxt'] ?></div>
			<div class="ph-price-netto ph-price-discount"><?php echo $d['priceitemsdiscountcart']['nettoformat'] ?></div>
		<?php } ?>

		<?php if ($d['priceitemsdiscountcart']['tax'] && $d['priceitemsdiscountcart']['taxcalc'] > 0) { ?>
			<div class="ph-tax-txt ph-price-discount"><?php echo $d['priceitemsdiscountcart']['taxtxt'] ?></div>
			<div class="ph-tax ph-price-discount"><?php echo $d['priceitemsdiscountcart']['taxformat'] ?></div>
		<?php } ?>

		<?php if ($d['priceitemsdiscountcart']['brutto']) { ?>
			<div class="ph-price-txt ph-price-brutto-txt ph-price-discount"><?php echo $d['priceitemsdiscountcart']['bruttotxt'] ?></div>
			<div class="ph-price-brutto ph-price-discount ph-price-discount-cart"><?php echo $d['priceitemsdiscountcart']['bruttoformat'] ?></div>
		<?php } ?>
	<?php }?>




	<?php if ($d['priceitems']['baseformat']) { ?>
		<div class="ph-price-txt"><?php echo Text::_('COM_PHOCACART_UNIT_PRICE') ?></div>
		<div class="ph-price-base"><?php echo $d['priceitems']['baseformat'] ?></div>
	<?php } ?>

		<div class="ph-cb"></div>
	</div>
</div>
<div class="ph-cb"></div>
