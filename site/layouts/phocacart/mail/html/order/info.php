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
/** @var EmailDocumentType $documentType */
$params = $displayData['params'];
$documentType = $displayData['documentType'];

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Phoca\PhocaCart\Constants\EmailDocumentType;

switch ($documentType) {
    case EmailDocumentType::Order:
        echo '<div><h1>'.Text::_('COM_PHOCACART_ORDER').'</h1></div>';
        break;
    case EmailDocumentType::Invoice:
        echo '<div><h1>'.Text::_('COM_PHOCACART_INVOICE').'</h1></div>';
        echo '<div><b>'.Text::_('COM_PHOCACART_INVOICE_NR').'</b>: '.PhocacartOrder::getInvoiceNumber($displayData['order']->id, $displayData['order']->date, $displayData['order']->invoice_number).'</div>';
        echo '<div><b>'.Text::_('COM_PHOCACART_INVOICE_DATE').'</b>: '.HTMLHelper::date($displayData['order']->invoice_date, 'DATE_FORMAT_LC4').'</div>';
        if ($params->get( 'display_time_of_supply_invoice', 0 ) && $displayData['order']->invoice_time_of_supply && $displayData['order']->invoice_time_of_supply != '0000-00-00 00:00:00') {
            echo '<div><b>' . Text::_('COM_PHOCACART_DATE_OF_TAXABLE_SUPPLY') . '</b>: ' . HTMLHelper::date($displayData['order']->invoice_time_of_supply, 'DATE_FORMAT_LC4') . '</div>';
        }
        echo '<div><b>'.Text::_('COM_PHOCACART_INVOICE_DUE_DATE').'</b>: '.PhocacartOrder::getInvoiceDueDate($displayData['order']->id, $displayData['order']->date, $displayData['order']->invoice_due_date, 'DATE_FORMAT_LC4').'</div>';
        echo '<div><b>'.Text::_('COM_PHOCACART_PAYMENT_REFERENCE_NUMBER').'</b>: '.PhocacartOrder::getPaymentReferenceNumber($displayData['order']->id, $displayData['order']->date, $displayData['order']->invoice_prn, 'DATE_FORMAT_LC4').'</div>';
        break;
    case EmailDocumentType::DeliveryNote:
        echo '<div><h1>'.Text::_('COM_PHOCACART_DELIVERY_NOTE').'</h1></div>';
        break;
}

echo '<div>&nbsp;</div>';
echo '<div><b>'.Text::_('COM_PHOCACART_ORDER_NR').'</b>: '.PhocacartOrder::getOrderNumber($displayData['order']->id, $displayData['order']->date, $displayData['order']->order_number).'</div>';
echo '<div><b>'.Text::_('COM_PHOCACART_ORDER_DATE').'</b>: '.HTMLHelper::date($displayData['order']->date, 'DATE_FORMAT_LC4').'</div>';
echo '<div>&nbsp;</div>';

if (isset($displayData['order']->paymenttitle) && $displayData['order']->paymenttitle != '') {
    echo '<div><b>'.Text::_('COM_PHOCACART_PAYMENT').'</b>: '.$displayData['order']->paymenttitle.'</div>';
}

if ($documentType == EmailDocumentType::Invoice && $params->get( 'invoice_terms_payment')) {
    echo '<div><strong>' . Text::_('COM_PHOCACART_TERMS_OF_PAYMENT').'</strong>: ' . $params->get( 'invoice_terms_payment') . '</div>';
}

if (isset($displayData['order']->shippingtitle) && $displayData['order']->shippingtitle != '') {
    echo '<div><b>'.Text::_('COM_PHOCACART_SHIPPING').'</b>: '.$displayData['order']->shippingtitle.'</div>';
}

