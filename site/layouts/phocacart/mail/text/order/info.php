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
/** @var EmailDocumentType $documentType
 */
$params = $displayData['params'];
$documentType = $displayData['documentType'];

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Phoca\PhocaCart\Constants\EmailDocumentType;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Mail\MailHelper;
use Phoca\PhocaCart\Utils\TextUtils;

switch ($documentType) {
    case EmailDocumentType::Order:
        echo TextUtils::underline(Text::_('COM_PHOCACART_ORDER')) . "\n\n";
        break;
    case EmailDocumentType::Invoice:
        echo TextUtils::underline(Text::_('COM_PHOCACART_INVOICE')) . "\n\n";
        echo Text::_('COM_PHOCACART_INVOICE_NR').': '.PhocacartOrder::getInvoiceNumber($displayData['order']->id, $displayData['order']->date, $displayData['order']->invoice_number)."\n";
        echo Text::_('COM_PHOCACART_INVOICE_DATE').': '.HTMLHelper::date($displayData['order']->invoice_date, 'DATE_FORMAT_LC4')."\n";
        if ($params->get( 'display_time_of_supply_invoice', 0 ) && $displayData['order']->invoice_time_of_supply && $displayData['order']->invoice_time_of_supply != '0000-00-00 00:00:00') {
            echo Text::_('COM_PHOCACART_DATE_OF_TAXABLE_SUPPLY') . ': ' . HTMLHelper::date($displayData['order']->invoice_time_of_supply, 'DATE_FORMAT_LC4') . "\n";
        }
        echo Text::_('COM_PHOCACART_INVOICE_DUE_DATE').': '.PhocacartOrder::getInvoiceDueDate($displayData['order']->id, $displayData['order']->date, $displayData['order']->invoice_due_date, 'DATE_FORMAT_LC4')."\n";
        echo Text::_('COM_PHOCACART_PAYMENT_REFERENCE_NUMBER').': '.PhocacartOrder::getPaymentReferenceNumber($displayData['order']->id, $displayData['order']->date, $displayData['order']->invoice_prn, 'DATE_FORMAT_LC4')."\n";
        break;
    case EmailDocumentType::DeliveryNote:
        echo TextUtils::underline(Text::_('COM_PHOCACART_DELIVERY_NOTE')) . "\n\n";
        break;
}

echo "\n";
echo Text::_('COM_PHOCACART_ORDER_NR').': '.PhocacartOrder::getOrderNumber($displayData['order']->id, $displayData['order']->date, $displayData['order']->order_number)."\n";
echo Text::_('COM_PHOCACART_ORDER_DATE').': '.HTMLHelper::date($displayData['order']->date, 'DATE_FORMAT_LC4')."\n";
echo "\n";

if (isset($displayData['order']->paymenttitle) && $displayData['order']->paymenttitle != '') {
    echo Text::_('COM_PHOCACART_PAYMENT').': '.$displayData['order']->paymenttitle."\n";
}

if ($documentType == EmailDocumentType::Invoice && $params->get( 'invoice_terms_payment')) {
    echo Text::_('COM_PHOCACART_TERMS_OF_PAYMENT').': ' . $params->get( 'invoice_terms_payment') . "\n";
}

if (isset($displayData['order']->shippingtitle) && $displayData['order']->shippingtitle != '') {
    echo Text::_('COM_PHOCACART_SHIPPING').': '.$displayData['order']->shippingtitle;
}
