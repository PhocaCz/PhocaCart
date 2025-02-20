<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Phoca\PhocaCart\Mail\MailHelper;

/** @var \Joomla\CMS\Layout\FileLayout $this */
/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
$params = $displayData['params'];

/* Blocks to use in MailTemplate */
$blocks = [
  'text_header' => $this->sublayout('header', $displayData),
  'info' => $this->sublayout('info', $displayData),
  'billing' => $this->sublayout('billing', $displayData),
  'shipping' => $this->sublayout('shipping', $displayData),
  'products' => $this->sublayout('products', $displayData),
  'totals' => $this->sublayout('totals', $displayData),
  'link' => $this->sublayout('link', $displayData),
  'downloads' => $this->sublayout('downloads', $displayData),
];
$displayData['blocks'] = &$blocks;
?>

<?= $blocks['text_header']; ?>

<?= $blocks['text_info']; ?>

<?= $blocks['text_link']; ?>

<?= $blocks['text_billing']; ?>

<?= $blocks['text_shipping']; ?>

<?= MailHelper::renderArticle((int)$params->get( 'order_global_top_desc', 0 ), $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false); ?>

<?= $blocks['text_products']; ?>

<?= $blocks['text_totals']; ?>

<?= MailHelper::renderArticle((int)$params->get( 'order_global_middle_desc', 0 ), $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false); ?>

<?= $blocks['text_downloads']; ?>

<?= MailHelper::renderArticle((int)$params->get( 'order_global_bottom_desc', 0 ), $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false); ?>
