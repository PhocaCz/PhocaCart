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
$displayData['blocks'] = [
  'header' => $this->sublayout('header', $displayData),
  'info' => $this->sublayout('info', $displayData),
  'billing' => $this->sublayout('billing', $displayData),
  'shipping' => $this->sublayout('shipping', $displayData),
  'products' => $this->sublayout('products', $displayData),
  'totals' => $this->sublayout('totals', $displayData),
  'link' => $this->sublayout('link', $displayData),
  'downloads' => $this->sublayout('downloads', $displayData),
];
?>

<?= $displayData['blocks']['header']; ?>

<?= $displayData['blocks']['info']; ?>

<?= $displayData['blocks']['link']; ?>

<?= $displayData['blocks']['billing']; ?>

<?= $displayData['blocks']['shipping']; ?>

<?= MailHelper::renderArticle((int)$params->get( 'order_global_top_desc', 0 ), $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false); ?>

<?= $displayData['blocks']['products']; ?>

<?= $displayData['blocks']['totals']; ?>

<?= MailHelper::renderArticle((int)$params->get( 'order_global_middle_desc', 0 ), $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false); ?>

<?= $displayData['blocks']['downloads']; ?>

<?= MailHelper::renderArticle((int)$params->get( 'order_global_bottom_desc', 0 ), $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s'], false); ?>
