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
$displayData['blocks'] = [
  'styles' => $this->sublayout('styles', $displayData),
  'voucher' => $this->sublayout('voucher', $displayData),
  'info' => $this->sublayout('info', $displayData),
  'billing' => $this->sublayout('billing', $displayData),
  'shipping' => $this->sublayout('shipping', $displayData),
  'products' => $this->sublayout('products', $displayData),
  'totals' => $this->sublayout('totals', $displayData),
  'link' => $this->sublayout('link', $displayData),
  'downloads' => $this->sublayout('downloads', $displayData),
];
?>

<?= $displayData['blocks']['styles']; ?>

<div style="<?= $styles['fs-normal'] . $styles['w100'] ?>">
    <?= $displayData['blocks']['voucher']; ?>
</div>
