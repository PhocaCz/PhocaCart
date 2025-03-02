<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Constants\EmailDocumentType;
use Phoca\PhocaCart\Utils\TextUtils;

defined('_JEXEC') or die();

/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
/** @var EmailDocumentType $documentType */
$params = $displayData['params'];
$documentType = $displayData['documentType'];

if ($params->get( 'display_tax_recapitulation_invoice') && $documentType == EmailDocumentType::Invoice && $displayData['taxrecapitulation']) {
    echo TextUtils::underline(Text::_('COM_PHOCACART_TAX_RECAPITULATION'), '=')  . "\n\n";

    echo TextUtils::underline(
            Text::_('COM_PHOCACART_TITLE') . "\t" .
            Text::_('COM_PHOCACART_TAX_BASIS') . "\t" .
            Text::_('COM_PHOCACART_TAX') . "\t" .
            Text::_('COM_PHOCACART_TOTAL') . "\t"
        )  . "\n";

	foreach($displayData['taxrecapitulation'] as $tax) {
        if ($tax->type == 'rounding') {
            // Don't display rounding here, only trcrounding (calculation rounding + tax recapitulation rounding)
            continue;
        }

        if ($tax->amount_brutto_currency ?? 0) {
            $amountBrutto		= $tax->amount_brutto_currency;
            $amountBruttoFormat = $displayData['price']->getPriceFormat($tax->amount_brutto_currency, 0, 1);
        } else {
            $amountBrutto		= $tax->amount_brutto;
            $amountBruttoFormat = $displayData['price']->getPriceFormat($tax->amount_brutto);
        }

        $amountNettoFormat 	= $tax->amount_netto > 0 ? $displayData['price']->getPriceFormat($tax->amount_netto) : '';
        $amountTaxFormat 	= $tax->amount_tax > 0 ? $displayData['price']->getPriceFormat($tax->amount_tax) : '';

        // Don't display rounding here, only trcrounding (calculation rounding + tax recapitulation rounding)
        if ($amountBrutto) {
            if ($tax->type == 'brutto') {
                echo $tax->title . "\t\t\t";
                echo $amountBruttoFormat . "\t\n";
            } else {
                echo $tax->title . "\t";
                echo $amountNettoFormat . "\t";
                echo $amountTaxFormat . "\t";
                echo $amountBruttoFormat . "\t\n";
            }
        }
    }
    echo "\n";
}
