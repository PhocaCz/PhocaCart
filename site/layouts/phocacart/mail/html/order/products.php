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
    echo '<table style="width: 100%;"><thead>';
    echo '<tr style="vertical-align: bottom">';
    echo '<th style="text-align: right; width: 5%;" align="right" valign="bottom">'.Text::_('COM_PHOCACART_QTY').'</th>';
    echo '<th style="text-align: left" align="left" valign="bottom">'.Text::_('COM_PHOCACART_ITEM').'</th>';
    echo '<th style="text-align: right; width: 10%" align="right" valign="bottom" width="100">'.Text::_('COM_PHOCACART_PRICE').'</th>';
    echo '</tr></thead><tbody>';

	foreach($displayData['products'] as $product) {
        echo '<tr style="vertical-align: top">';
        echo '<td style="text-align: right" align="right" valign="top">' . $product->quantity . '</td>';
        echo '<td style="text-align: left" align="left" valign="top"><strong>'.$product->sku . '</strong> - ' . $product->title;
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

        /*
		$lastSaleNettoUnit 	= array();
		$lastSaleNetto 		= array();
		$lastSaleTax 		= array();
		$lastSaleBrutto 	= array();
		if (!empty($displayData['discounts'][$product->product_id_key])) {

			$lastSaleNettoUnit[$product->product_id_key] = $nettoUnit;
			$lastSaleNetto[$product->product_id_key]     = $netto;
			$lastSaleTax[$product->product_id_key]       = $tax;
			$lastSaleBrutto[$product->product_id_key]    = $brutto;


			foreach($displayData['discounts'][$product->product_id_key] as $discount) {

				$nettoUnit3 							= $discount->netto;
				$netto3									= (int)$product->quantity * $discount->netto;
				$tax3 									= (int)$product->quantity * $discount->tax;
				$brutto3 								= (int)$product->quantity * $discount->brutto;

				$saleNettoUnit							= $lastSaleNettoUnit[$product->product_id_key] 	- $nettoUnit3;
				$saleNetto								= $lastSaleNetto[$product->product_id_key] 		- $netto3;
				$saleTax								= $lastSaleTax[$product->product_id_key] 			- $tax3;
				$saleBrutto								= $lastSaleBrutto[$product->product_id_key] 		- $brutto3;

				$lastSaleNettoUnit[$product->product_id_key] = $nettoUnit3;
				$lastSaleNetto[$product->product_id_key]     = $netto3;
				$lastSaleTax[$product->product_id_key]       = $tax3;
				$lastSaleBrutto[$product->product_id_key]    = $brutto3;

				if ($display_discount_price_product == 2) {

					$p[] = '<tr '.$bProduct.'>';
					$p[] = '<td></td>';
					$p[] = '<td colspan="'.$cTitle.'">'.$discount->title.'</td>';
					$p[] = '<td style="text-align:center"></td>';
					if ($tax_calculation > 0) {
						$p[] = '<td style="text-align:right" colspan="2">'.$displayData['price']->getPriceFormat($saleNettoUnit, 1).'</td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$displayData['price']->getPriceFormat($saleNetto, 1).'</td>';
						$p[] = '<td style="text-align:right" colspan="1">'.$displayData['price']->getPriceFormat($saleTax, 1).'</td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$displayData['price']->getPriceFormat($saleBrutto, 1).'</td>';
					} else {
						$p[] = '<td style="text-align:right" colspan="2">'.$displayData['price']->getPriceFormat($saleNettoUnit, 1).'</td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$displayData['price']->getPriceFormat($saleBrutto, 1).'</td>';
					}

					$p[] = '</tr>';

				} else if ($display_discount_price_product == 1) {

					$p[] = '<tr '.$bProduct.'>';
					$p[] = '<td></td>';
					$p[] = '<td colspan="'.$cTitle.'">'.$discount->title.'</td>';
					$p[] = '<td style="text-align:center"></td>';
					if ($tax_calculation > 0) {
						$p[] = '<td style="text-align:right" colspan="2">'.$displayData['price']->getPriceFormat($nettoUnit3).'</td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$displayData['price']->getPriceFormat($netto3).'</td>';
						$p[] = '<td style="text-align:right" colspan="1">'.$displayData['price']->getPriceFormat($tax3).'</td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$displayData['price']->getPriceFormat($brutto3).'</td>';
					} else {
						$p[] = '<td style="text-align:right" colspan="2">'.$displayData['price']->getPriceFormat($nettoUnit3).'</td>';
						$p[] = '<td style="text-align:right" colspan="2">'.$displayData['price']->getPriceFormat($brutto3).'</td>';
					}

					$p[] = '</tr>';

				}

			}
		}
    */
	}
    echo '</tbody></table>';
}
