<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;

/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
/** @var object $order */
/** @var array $products */
$params = $displayData['params'];
$order = $displayData['order'];
$products = $displayData['products'];

if ($order->user_id) {
    $canDownload = true;
} else {
    $canDownload = !!$order->order_token && $params->get('download_guest_access', 0);
}
//$display_discount_price_product		= $params->get( 'display_discount_price_product', 1);

if ($products) {
    echo Text::_('COM_PHOCACART_QTY')."\t";
    echo Text::_('COM_PHOCACART_ITEM')."\t";
    echo Text::_('COM_PHOCACART_PRICE')."\t\n";

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

        echo $displayData['price']->getPriceFormat((int)$product->quantity * $product->brutto) . "\n";
	}
    echo "\n\n";
}
