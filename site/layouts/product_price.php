<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();


use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

$paramsC 					= PhocacartUtils::getComponentParameters();
$zero_price_text			= $paramsC->get( 'zero_price_text', '' );
$zero_price_label			= $paramsC->get( 'zero_price_label', '' );
$price_on_demand_text       = $paramsC->get( 'price_on_demand_text', '' );
$price_on_demand_label      = $paramsC->get( 'price_on_demand_label', '' );

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

    $displayPriceItems = PhocaCartPrice::displayPriceItems($d['priceitems'], 'price');// Display Netto, Tax, Brutto?


    $subscriptionDateInfo = '';
    if (isset($d['subscription_scenario']) && $d['subscription_scenario']) {
		$lang = Factory::getLanguage();
		$lang->load('plg_system_phocacartsubscription', JPATH_ADMINISTRATOR);
		$scenario = $d['subscription_scenario'];
		$priceHelper = new PhocacartPrice;
        $plugin = PluginHelper::getPlugin('system', 'phocacartsubscription');
        $params = new Registry($plugin->params ?? '');

        if ((isset($scenario['signup_fee']) && $scenario['signup_fee'] > 0) || (isset($scenario['renewal_discount']) && $scenario['renewal_discount'] > 0)) {
            // Paradoxically, we can have both a signup fee and a discount on renewal (see the Charge Signup Fee After Expiration parameter)
            // So display the base price only once
            echo '<div class="ph-price-txt ph-price-netto-txt ph-price-subscription-base-price">'.Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_BASE_PRICE').'</div>';
            echo '<div class="ph-price-netto ph-price-subscription-base-price">'.$priceHelper->getPriceFormat($scenario['base_price']).'</div>';
        }
        if (isset($scenario['signup_fee']) && $scenario['signup_fee'] > 0) {
            //echo '<div class="ph-price-txt ph-price-netto-txt ph-price-subscription-base-price">'.Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_BASE_PRICE').'</div>';
            //echo '<div class="ph-price-netto ph-price-subscription-base-price">'.$priceHelper->getPriceFormat($scenario['base_price']).'</div>';
            echo '<div class="ph-price-txt ph-price-netto-txt ph-price-subscription-signup-fee">'.Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_SIGNUP_FEE').'</div>';
            echo '<div class="ph-price-netto ph-price-subscription-signup-fee">'.$priceHelper->getPriceFormat($scenario['signup_fee']).'</div>';
		}
		if (isset($scenario['renewal_discount']) && $scenario['renewal_discount'] > 0) {
            //echo '<div class="ph-price-txt ph-price-netto-txt ph-price-subscription-base-price">'.Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_BASE_PRICE').'</div>';
            //echo '<div class="ph-price-netto ph-price-subscription-base-price">'.$priceHelper->getPriceFormat($scenario['base_price']).'</div>';
            echo '<div class="ph-price-txt ph-price-netto-txt ph-price-subscription-renewal-discount">'.Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_RENEWAL_DISCOUNT').'</div>';
            echo '<div class="ph-price-netto ph-price-subscription-renewal-discount">'.$priceHelper->getPriceFormat($scenario['renewal_discount'], 1).'</div>';


		}

        $subscription_display_period_price = (int)$params->get('subscription_display_period_price', 0);
        $subscription_current_display_period_price = (int)$params->get('subscription_current_display_period_price', 0);
        $subscription_potentional_display_period_price = (int)$params->get('subscription_potentional_display_period_price', 0);

        if ($subscription_display_period_price == 1) {

            $unit                 = $scenario['unit'];
            $unitTxt              = \PhocacartSubscription::getUnit($unit);
            $period               = $scenario['period'];
            //$unitSuffix            = \PhocacartSubscription::getUnitLangSuffix($period);

            $subscriptionDateInfo .= '<div class="ph-subscription-period-header">' . Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_SUBSCRIPTION_LENGTH') . ': </div>';
            $subscriptionDateInfo .= '<div class="ph-subscription-period">' . Text::plural('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_SUBSCRIPTION_'.$unitTxt.'_N', (int)$period). '</div>';

        }


        // We can even work with $scenario['type'] = "new" | "renewal_expired", ...
        if (isset($scenario['renewal_discount']) && $scenario['renewal_discount'] > 0) {
            if (isset($scenario['start_date']) && $scenario['start_date'] != '' && isset($scenario['end_date']) && $scenario['end_date'] != '')  {
                if ($subscription_current_display_period_price == 1) {
                    $dateFrom             = new Date($scenario['start_date_default']);
                    $dateFromFormat       = $dateFrom->format(Text::_('DATE_FORMAT_LC4'));
                    $dateTo               = new Date($scenario['end_date_default']);
                    $dateToFormat         = $dateTo->format(Text::_('DATE_FORMAT_LC4'));
                    $subscriptionDateInfo .= '<div class="ph-active-subscription-period-header">' . Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_YOUR_ACTIVE_SUBSCRIPTION_PERIOD') . ': </div>';
                    $subscriptionDateInfo .= '<div class="ph-active-subscription-period">' . $dateFromFormat . ' - ' . $dateToFormat . '</div>';
                }
            }
        } else {
            if (isset($scenario['start_date']) && $scenario['start_date'] != '' && isset($scenario['end_date']) && $scenario['end_date'] != '')  {
                if ($subscription_potentional_display_period_price == 1) {
                    $dateFrom             = new Date($scenario['start_date']);
                    $dateFromFormat       = $dateFrom->format(Text::_('DATE_FORMAT_LC4'));
                    $dateTo               = new Date($scenario['end_date']);
                    $dateToFormat         = $dateTo->format(Text::_('DATE_FORMAT_LC4'));
                    $subscriptionDateInfo .= '<div class="ph-potentional-subscription-period-header">' . Text::_('PLG_SYSTEM_PHOCACARTSUBSCRIPTION_POTENTIONAL_SUBSCRIPTION_PERIOD') . ': </div>';
                    $subscriptionDateInfo .= '<div class="ph-potentional-subscription-period">' . $dateFromFormat . ' - ' . $dateToFormat . '</div>';
                }
            }
        }


	}


    if ($displayPriceItems['netto'] == 1) {
		$labelNetto = '<div class="ph-price-txt ph-price-netto-txt '.$classPS.'-txt">'. $d['priceitems']['nettotxt'].'</div>';
		$priceNetto = '<div class="ph-price-netto '.$classPS.'">'.$d['priceitems']['nettoformat'].'</div>';
	}

	//if ($d['priceitems']['tax']) && $d['priceitems']['taxcalc'] > 0) {
    if($displayPriceItems['tax'] == 1) {
		$labelTax = '<div class="ph-tax-txt '.$classPS.'-txt">'.$d['priceitems']['taxtxt'].'</div>';
		$priceTax = '<div class="ph-tax '.$classPS.'">'.$d['priceitems']['taxformat'].'</div>';
	}

	if ($displayPriceItems['brutto'] == 1) {
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

	// PRODUCT DISCOUNT
    $displayPriceItems = PhocaCartPrice::displayPriceItems($d['priceitemsdiscount'], 'pricediscount');// Display Netto, Tax, Brutto?
	if (isset ($d['discount']) && $d['discount'] && $displayPrice == 1) {

        if ($d['priceitemsdiscount']['netto'] && $d['priceitemsdiscount']['taxcalc'] > 0
                    && ($d['priceitemsdiscount']['brutto'] != $d['priceitemsdiscount']['netto'])) {

            if ($displayPriceItems['netto'] == 1) {
			    echo '<div class="ph-price-txt ph-price-netto-txt ph-price-discount">'.$d['priceitemsdiscount']['nettotxt'].'</div>';
			    echo '<div class="ph-price-netto ph-price-discount">'.$d['priceitemsdiscount']['nettoformat'].'</div>';
		    }
        }

        if ($d['priceitemsdiscount']['tax'] && $d['priceitemsdiscount']['taxcalc'] > 0) {
            if ($displayPriceItems['tax'] == 1) {
                echo '<div class="ph-tax-txt ph-price-discount">' . $d['priceitemsdiscount']['taxtxt'] . '</div>';
                echo '<div class="ph-tax ph-price-discount">' . $d['priceitemsdiscount']['taxformat'] . '</div>';
            }
        }

        if ($d['priceitemsdiscount']['brutto']) {
            if ($displayPriceItems['brutto'] == 1) {
                echo '<div class="ph-price-txt ph-price-brutto-txt ph-price-discount">' . $d['priceitemsdiscount']['bruttotxt'] . '</div>';
                echo '<div class="ph-price-brutto ph-price-discount ph-price-discount-product">' . $d['priceitemsdiscount']['bruttoformat'] . '</div>';
            }
        }
    }

	// CART DISCOUNT DISPLAYED IN PRODUCT VIEWS (under specific conditions only)
	if (isset ($d['discountcart']) && $d['discountcart'] && $displayPrice == 1) {
		if ($d['priceitemsdiscountcart']['netto'] && $d['priceitemsdiscountcart']['taxcalc'] > 0
                && ($d['priceitemsdiscountcart']['brutto'] != $d['priceitemsdiscountcart']['netto'])) {
			if ($displayPriceItems['netto'] == 1) {
                echo '<div class="ph-price-txt ph-price-netto-txt ph-price-discount">' . $d['priceitemsdiscountcart']['nettotxt'] . '</div>';
                echo '<div class="ph-price-netto ph-price-discount">' . $d['priceitemsdiscountcart']['nettoformat'] . '</div>';
            }
		}

		if ($d['priceitemsdiscountcart']['tax'] && $d['priceitemsdiscountcart']['taxcalc'] > 0) {
            if ($displayPriceItems['tax'] == 1) {
                echo '<div class="ph-tax-txt ph-price-discount">' . $d['priceitemsdiscountcart']['taxtxt'] . '</div>';
                echo '<div class="ph-tax ph-price-discount">' . $d['priceitemsdiscountcart']['taxformat'] . '</div>';
            }
        }

		if ($d['priceitemsdiscountcart']['brutto']) {
            if ($displayPriceItems['brutto'] == 1) {
                echo '<div class="ph-price-txt ph-price-brutto-txt ph-price-discount">' . $d['priceitemsdiscountcart']['bruttotxt'] . '</div>';
                echo '<div class="ph-price-brutto ph-price-discount ph-price-discount-cart">' . $d['priceitemsdiscountcart']['bruttoformat'] . '</div>';
            }
		}
	}




	if ($d['priceitems']['baseformat']) {
		echo '<div class="ph-price-txt">'. Text::_('COM_PHOCACART_UNIT_PRICE') .'</div>';
		echo '<div class="ph-price-base">'. $d['priceitems']['baseformat'] .'</div>';
	}



    echo '<div class="ph-cb"></div>';
    echo $subscriptionDateInfo;

    ?>
	</div>
</div>
<div class="ph-cb"></div>
