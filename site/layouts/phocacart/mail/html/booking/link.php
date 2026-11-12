<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Mail\MailHelper;

/** @var array $displayData */
/** @var array $styles */
$styles    = $displayData['styles'];
$product   = $displayData['product'];
$eventType = $displayData['eventType'];

$accountUrl = MailHelper::link(PhocacartRoute::getAccountRoute());
?>

<div style="margin-top: 24px; text-align: center;">
    <a href="<?= $accountUrl ?>" style="<?= $styles['button'] ?>">
        <?= Text::_('COM_PHOCACART_MAIL_BOOKING_VIEW_BOOKING') ?>
    </a>
    <p style="margin-top: 12px; color: #6c757d; font-size: 13px;">
        <?= Text::_('COM_PHOCACART_MAIL_BOOKING_ACCOUNT_HINT') ?>
    </p>
</div>

<hr style="border: none; border-top: 1px solid #e9ecef; margin: 24px 0;" />

<p style="color: #6c757d; font-size: 12px; text-align: center;">
    <?= Text::_('COM_PHOCACART_BOOKING_EMAIL_FOOTER') ?>
</p>

