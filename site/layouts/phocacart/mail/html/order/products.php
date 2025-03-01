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

if ($products) {
    echo '<table style="width: 100%;"><thead>';
    echo '<tr style="vertical-align: bottom">';
    echo '<th style="text-align: right; width: 5%;" align="right" valign="bottom">'.Text::_('COM_PHOCACART_QTY').'</th>';
    echo '<th style="text-align: left" align="left" valign="bottom">'.Text::_('COM_PHOCACART_ITEM').'</th>';
    echo '<th style="text-align: right; width: 10%" align="right" valign="bottom" width="100">'.Text::_('COM_PHOCACART_PRICE').'</th>';
    echo '</tr></thead><tbody>';

	foreach($displayData['products'] as $product) {
        echo '<tr style="vertical-align: top">';
        echo '<td style="text-align: right" align="right" valign="top">' . $product->quantity . '</td>';
        echo '<td style="text-align: left" align="left" valign="top">';
        if ($product->sku) {
            echo '<strong>' . $product->sku . '</strong> - ';
        }
        echo $product->title;
        if (!empty($product->attributes)) {
            echo '<p style="margin-top: 10px">';
            foreach ($product->attributes as $attribute) {
                echo '<small>' . $attribute->attribute_title . ' ' . $attribute->option_title .': ' . htmlspecialchars(urldecode($attribute->option_value), ENT_QUOTES, 'UTF-8') . '</small><br>';
            }
            echo '</p>';
        }

        // Download links
        $productDownloads = [];
        foreach ($product->downloads as $download) {
            if ($download->published && $download->download_file && $download->download_folder && $download->download_token) {
                $productDownloads[] = [
                    'title' => pathinfo($download->download_file, PATHINFO_BASENAME),
                    'token' => $download->download_token,
                ];
            }
        }

        foreach ($product->attributes as $attribute) {
            if ($attribute->download_published && $attribute->download_file && $attribute->download_folder) {
                $productDownloads[] = [
                    'title' => pathinfo($attribute->download_file, PATHINFO_BASENAME),
                    'token' => $attribute->download_token,
                ];
            }
        }

        if ($canDownload && $productDownloads) {
            echo '<h6 style="margin: 10px 0 0 0">' . Text::_('COM_PHOCACART_DOWNLOAD_LINKS') . '</h6>';
            echo '<ul style="margin: 0">';
            foreach ($productDownloads as $download) {
                if ($order->user_id) {
                    $downloadLink = PhocacartPath::getRightPathLink(PhocacartRoute::getDownloadRoute());
                } else {
                    $downloadLink = PhocacartPath::getRightPathLink(PhocacartRoute::getDownloadRoute() . '&o=' . $order->order_token . '&d=' . $download['token']);
                }
                echo '<li style="margin: 0"><a href="' . $downloadLink. '" target="_blank">' . $download['title'] . '</a></li>';
            }
            echo '</ul>';
        }

        echo '</td>';
        echo '<td style="text-align: right; width: 100px" align="right" valign="top" width="100">'.$displayData['price']->getPriceFormat((int)$product->quantity * $product->brutto).'</td>';
        echo '</tr>';
	}
    echo '</tbody></table>';
}
