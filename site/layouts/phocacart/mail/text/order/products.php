<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Constants\EmailDocumentType;

/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
/** @var object $order */
/** @var array $products */
/** @var EmailDocumentType $documentType */
$params = $displayData['params'];
$documentType = $displayData['documentType'];
$order = $displayData['order'];
$products = $displayData['products'];

if ($order->user_id) {
    $canDownload = true;
} else {
    $canDownload = !!$order->order_token && $params->get('download_guest_access', 0);
}

if ($products) {
    echo Text::_('COM_PHOCACART_QTY')."\t";
    echo Text::_('COM_PHOCACART_ITEM')."\t";
    if ($documentType != EmailDocumentType::DeliveryNote) {
        echo Text::_('COM_PHOCACART_PRICE') . "\t";
    }
    echo "\n";

	foreach($displayData['products'] as $product) {
        echo $product->quantity . "\t";
        echo $product->sku . ' - ' . $product->title . "\t";
        if (!empty($product->attributes)) {
            $printAttributes = [];
            foreach ($product->attributes as $attribute) {
                $printAttributes[] = $attribute->attribute_title . ' ' . $attribute->option_title .': ' . htmlspecialchars(urldecode($attribute->option_value), ENT_QUOTES, 'UTF-8');
            }
            echo '(' . implode(', ', $printAttributes) . ')';
        }

        if ($documentType != EmailDocumentType::DeliveryNote) {
            echo $displayData['price']->getPriceFormat((int) $product->quantity * $product->brutto);
        }
        echo "\n";
	}
    echo "\n\n";
}
