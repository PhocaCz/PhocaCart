<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
$params = $displayData['params'];

// Corect subtotal in case the sales are deducted from prices with VAT (subtotal is without VAT so such needs to be added)
$recalcNetto = $params->get( 'tax_calculation_sales', 1) == 2 && $params->get( 'tax_calculation_sales_change_subtotal', 0) == 1;

if (!empty($displayData['total'])) {
    $netto = 0;
    if ($recalcNetto) {
        foreach($displayData['total'] as $total) {
            if ($total->type == 'brutto') {
                $netto = $netto + $total->amount;
            } else if (in_array($total->type, ['dbrutto', 'tax', 'rounding'])) {
                $netto = $netto - $total->amount;
            }
        }
    }

	foreach($displayData['total'] as $total) {
		// do not display shipping and payment methods with zero amount
        if ($total->amount == 0 && $total->amount_currency == 0 && $total->type != 'brutto') {
            continue;
		}

        $isStrong = in_array($total->type, ['netto', 'brutto']);

        echo PhocacartLanguage::renderTitle($total->title, $total->title_lang, array(0 => array($total->title_lang_suffix, ' '), 1 => array($total->title_lang_suffix2, ' ')));

        if ($total->type == 'netto') {
            echo $displayData['price']->getPriceFormat($recalcNetto ? $netto : $total->amount);
        } else if (in_array($total->type, ['brutto', 'rounding'])) {
            echo ($total->amount_currency > 0) ? $displayData['price']->getPriceFormat($total->amount_currency, 0, 1) : $displayData['price']->getPriceFormat($total->amount);
        } else {
            echo $displayData['price']->getPriceFormat($total->amount);
        }

        echo "\n";
    }
    echo "\n";
}
