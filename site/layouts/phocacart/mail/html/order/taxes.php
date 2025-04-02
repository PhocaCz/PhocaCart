<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Constants\EmailDocumentType;

defined('_JEXEC') or die();

/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
/** @var EmailDocumentType $documentType */
$params = $displayData['params'];
$documentType = $displayData['documentType'];

if ($params->get( 'display_tax_recapitulation_invoice') && $documentType == EmailDocumentType::Invoice && $displayData['taxrecapitulation']) {
    echo '<h3>'.Text::_('COM_PHOCACART_TAX_RECAPITULATION').'</h3>';

    echo '<table style="width: 100%;"><thead><tr>';
    echo '<th>' . Text::_('COM_PHOCACART_TITLE') . '</th>';
    echo '<th>' . Text::_('COM_PHOCACART_TAX_BASIS') . '</th>';
    echo '<th>' . Text::_('COM_PHOCACART_TAX') . '</th>';
    echo '<th>' . Text::_('COM_PHOCACART_TOTAL') . '</th>';
    echo '</tr></thead><tbody>';

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

        if ($amountBrutto) {
            echo '<tr>';
            echo '<td style="text-align: right" align="right" valign="top">' . $tax->title . '</td>';
            if ($tax->type == 'brutto') {
                echo '<td>&nbsp;</td>';
                echo '<td>&nbsp;</td>';
            } else {
                echo '<td style="text-align: right" align="right" valign="top">' . $amountNettoFormat . '</td>';
                echo '<td style="text-align: right" align="right" valign="top">' . $amountTaxFormat . '</td>';
            }
            echo '<td style="text-align: right" align="right" valign="top">' . $amountBruttoFormat . '</td>';
            echo '</tr>';

        }
    }
    echo '</tbody></table>';
}
