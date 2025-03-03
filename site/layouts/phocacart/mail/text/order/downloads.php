<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Utils\TextUtils;

defined('_JEXEC') or die();

/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
/** @var object $order */
/** @var array $products */
/** @var array $mailData */
$params = $displayData['params'];
$order = $displayData['order'];
$products = $displayData['products'];
$mailData = &$displayData['mailData'];

if (!$order->user_id) {
    $canDownload = !!$order->order_token && $params->get('download_guest_access', 0);
    if (!$canDownload) {
      return;
    }
}

$downloads = [];
foreach ($products as $product) {
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

    if ($productDownloads) {
      $downloads[] = [
        'title' => $product->title,
        'downloads' => $productDownloads,
      ];
    }
}

if ($downloads) {
    $mailData['HAS_DOWNLOADS'] = true;
?>
<?= TextUtils::underline(Text::_('COM_PHOCACART_DOWNLOAD_LINKS')) . "\n\n"  ?>
<?php foreach ($downloads as $productDownloads) { ?>
<?= $productDownloads['title'] . "\n" ?>
<?php foreach ($productDownloads['downloads'] as $download) { ?>
<?php
  if ($order->user_id) {
    $downloadLink = PhocacartPath::getRightPathLink(PhocacartRoute::getDownloadRoute());
  } else {
    $downloadLink = PhocacartPath::getRightPathLink(PhocacartRoute::getDownloadRoute() . '&o=' . $order->order_token . '&d=' . $download['token']);
  }
?><?= $download['title'] ?>: <?= $downloadLink ?>
<?php } ?>
<?php } ?>
<?php
}
