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
/** @var array $styles */
$styles    = $displayData['styles'];
$booking   = $displayData['booking'];
$product   = $displayData['product'];
$eventType = $displayData['eventType'];

$statusClass = \PhocacartBookingEmail::getBookingStatusStyle((int)($booking->status ?? 0));
$statusStyle = $styles[$statusClass] ?? ($styles['status-pending'] ?? '');
$statusText  = Text::_(\PhocacartBooking::getStatus((int)($booking->status ?? 0)));

?>
<div style="<?= $styles['card'] ?>">
    <table style="<?= $styles['w100'] ?>">
        <tbody>
            <tr>
                <td style="width: 60%; vertical-align: top;">
                    <div style="<?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_PRODUCT') ?></div>
                    <div style="<?= $styles['value'] ?>"><?= htmlspecialchars($product->title) ?></div>
                </td>
                <td style="width: 40%; vertical-align: top; text-align: right;">
                    <div style="<?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_STATUS') ?></div>
                    <span style="<?= $statusStyle ?>"><?= $statusText ?></span>
                </td>
            </tr>
        </tbody>
    </table>

    <table style="<?= $styles['w100'] ?> margin-top: 12px;">
        <tbody>
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <span style="<?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_BOOKING') ?> #:</span>
                    <span style="font-weight: 600;">#<?= (int)$booking->id ?></span>
                </td>
                <?php if (!empty($product->sku)): ?>
                <td style="width: 50%; vertical-align: top;">
                    <span style="<?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_SKU') ?>:</span>
                    <span><?= htmlspecialchars($product->sku) ?></span>
                </td>
                <?php endif; ?>
            </tr>
        </tbody>
    </table>
</div>

