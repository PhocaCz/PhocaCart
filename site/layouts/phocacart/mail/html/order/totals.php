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

// Corect subtotal in case the sales are deducted from prices with VAT (subtotal is without VAT so such needs to be added)
$recalcNetto = $params->get( 'tax_calculation_sales', 1) == 2 && $params->get( 'tax_calculation_sales_change_subtotal', 0) == 1;

if (!empty($displayData['total']) && $documentType != EmailDocumentType::DeliveryNote) {
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

    $paymentAmount = null;
    echo '<table style="width: 100%;">';
	foreach($displayData['total'] as $total) {
		// do not display shipping and payment methods with zero amount
        if ($total->amount == 0 && $total->amount_currency == 0 && $total->type != 'brutto') {
            continue;
		}

        $isStrong = in_array($total->type, ['netto', 'brutto']);
        echo '<tr>';

        echo '<td style="text-align: right" align="right" valign="top">' . ($isStrong ? '<strong>' : '');
        echo PhocacartLanguage::renderTitle($total->title, $total->title_lang, [0 => [$total->title_lang_suffix, ' '], 1 => [$total->title_lang_suffix2, ' ']]);
        echo ($isStrong ? '</strong>' : '') . '</td>';

        echo '<td style="text-align: right; width: 100px" align="right" valign="top" width="100">' . ($isStrong ? '<strong>' : '');
        if ($total->type == 'netto') {
            echo $displayData['price']->getPriceFormat($recalcNetto ? $netto : $total->amount);
        } else if ($total->type == 'brutto') {
            $paymentAmount = ($total->amount_currency > 0) ? $displayData['price']->getPriceFormat($total->amount_currency, 0, 1) : $displayData['price']->getPriceFormat($total->amount);
            echo $paymentAmount;
        } else if ($total->type == 'rounding') {
            echo ($total->amount_currency > 0) ? $displayData['price']->getPriceFormat($total->amount_currency, 0, 1) : $displayData['price']->getPriceFormat($total->amount);
        } else {
            echo $displayData['price']->getPriceFormat($total->amount);
        }
        echo ($isStrong ? '</strong>' : '') . '</td>';

        echo '</tr>';
    }

    if ($documentType == EmailDocumentType::Invoice && $paymentAmount) {
        echo '<tr><td colspan="2">&nbsp;</td></tr>';
        echo '<tr><td style="text-align: right" align="right" valign="top"><strong>' . Text::_('COM_PHOCACART_TO_PAY') . '</strong></td>';
        echo '<td style="text-align: right; width: 100px" align="right" valign="top" width="100"><strong>';
        echo $paymentAmount;
        echo '</strong></td>';
        echo '</tr>';
    }
    echo '</table>';
}
