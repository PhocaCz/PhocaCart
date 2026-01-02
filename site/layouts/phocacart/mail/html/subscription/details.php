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
$styles = $displayData['styles'];
$subscription = $displayData['subscription'];
$product = $displayData['product'];
$eventType = $displayData['eventType'];

// Map status to style
/*
$statusStyles = [
    'activated' => 'status-active',
    'renewed' => 'status-active',
    'expiring_soon' => 'status-warning',
    'expired' => 'status-expired',
    'canceled' => 'status-expired',
];
$statusStyle = $styles[$statusStyles[$eventType] ?? 'status-active'] ?? '';
*/
$statusClass = \PhocacartSubscription::getStatusStyle($subscription->status);
$statusStyle = $styles[$statusClass ?? 'status-pending'] ?? '';
$statusText = Text::_(\PhocacartSubscription::getStatus($subscription->status));

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

    <?php if (!empty($product->sku)): ?>
    <div style="margin-top: 12px;">
        <span style="<?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_SKU') ?>:</span>
        <span><?= htmlspecialchars($product->sku) ?></span>
    </div>
    <?php endif; ?>
</div>
