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

/* Styles are defined in styles sublayout */
$styles= [];
$displayData['styles'] = &$styles;

/* Blocks to use in MailTemplate */
$blocks = [
  'styles' => $this->sublayout('styles', $displayData),
  'header' => $this->sublayout('header', $displayData),
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

<div style="<?= $styles['fs-normal'] . $styles['w100'] ?>">
  <table style="<?= $styles['w100'] ?>">
    <tbody>
      <tr>
        <td style="width: 50%;">
            <?= $blocks['header']; ?>
        </td>
        <td style="width: 20px;"></td>
        <td style="width: 50%;">
            <?= $blocks['info']; ?>
        </td>
      </tr>
    </tbody>
  </table>

  <?= $blocks['link']; ?>

  <table style="<?= $styles['w100'] ?>">
    <tbody>
      <tr>
        <td style="width: 50%;">
            <?= $blocks['billing']; ?>
        </td>
        <td style="width: 20px;"></td>
        <td style="width: 50%;">
            <?= $blocks['shipping']; ?>
        </td>
      </tr>
    </tbody>
  </table>

  <?= MailHelper::renderArticle((int)$params->get( 'order_global_top_desc', 0 ), $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s']); ?>

  <?= $blocks['products']; ?>
  <?= $blocks['totals']; ?>

  <?= MailHelper::renderArticle((int)$params->get( 'order_global_middle_desc', 0 ), $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s']); ?>

  <?= $blocks['downloads']; ?>

  <?= MailHelper::renderArticle((int)$params->get( 'order_global_bottom_desc', 0 ), $displayData['preparereplace'], $displayData['bas']['b'], $displayData['bas']['s']); ?>
</div>
