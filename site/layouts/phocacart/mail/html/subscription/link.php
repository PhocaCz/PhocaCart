<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Phoca\PhocaCart\Mail\MailHelper;

/** @var array $displayData */
/** @var array $styles */
$styles = $displayData['styles'];
$product = $displayData['product'];
$eventType = $displayData['eventType'];

// Determine CTA based on event type
$showRenewButton = in_array($eventType, ['expiring_soon', 'expired', 'canceled']);
$showAccountButton = in_array($eventType, ['activated', 'renewed', 'status_changed']);

$siteUrl = Uri::root();

$renewalUrl = MailHelper::link(PhocacartRoute::getItemRoute($product->id, $product->catid, $product->alias, $product->catalias));
$accountUrl = MailHelper::link(PhocacartRoute::getAccountRoute($product->id, $product->catid));
?>

<div style="margin-top: 24px; text-align: center;">
    <?php if ($showRenewButton): ?>
        <a href="<?= $renewalUrl ?>"
           style="<?= $styles['button'] ?>">
            <?= Text::_('COM_PHOCACART_RENEW_SUBSCRIPTION') ?>
        </a>
        <p style="margin-top: 12px; color: #6c757d; font-size: 13px;">
            <?= Text::_('COM_PHOCACART_SUBSCRIPTION_RENEW_HINT') ?>
        </p>
    <?php elseif ($showAccountButton): ?>
        <a href="<?= $accountUrl ?>"
           style="<?= $styles['button'] ?>">
            <?= Text::_('COM_PHOCACART_VIEW_MY_ACCOUNT') ?>
        </a>
    <?php endif; ?>
</div>

<hr style="border: none; border-top: 1px solid #e9ecef; margin: 24px 0;" />

<p style="color: #6c757d; font-size: 12px; text-align: center;">
    <?= Text::_('COM_PHOCACART_SUBSCRIPTION_EMAIL_FOOTER') ?>
</p>
