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
/** @var Joomla\Registry\Registry $params */
/** @var array $attachments */
$params      = $displayData['params'];
$attachments = &$displayData['attachments'];
$eventType   = $displayData['eventType'];
$user        = $displayData['user'];

// Store header (same as order emails)
if ($store_title = $params->get('store_title')) {
    echo '<h3>' . $store_title . '</h3>';
}

if ($store_logo = $params->get('store_logo')) {
    $attachments['store-logo'] = $store_logo;
    echo '<div class="ph__logo"><img src="cid:store-logo" alt="" style="max-width: 200px; max-height: 200px" /></div>';
}

// Personalized greeting
echo '<p style="font-size: 16px; margin-top: 20px;">';
echo Text::sprintf('COM_PHOCACART_MAIL_BOOKING_GREETING', htmlspecialchars($user->name));
echo '</p>';

// Event-specific headline
$headlines = [
    'created'        => 'COM_PHOCACART_MAIL_BOOKING_HEADLINE_CREATED',
    'confirmed'      => 'COM_PHOCACART_MAIL_BOOKING_HEADLINE_CONFIRMED',
    'cancelled'      => 'COM_PHOCACART_MAIL_BOOKING_HEADLINE_CANCELLED',
    'completed'      => 'COM_PHOCACART_MAIL_BOOKING_HEADLINE_COMPLETED',
    'reminder'       => 'COM_PHOCACART_MAIL_BOOKING_HEADLINE_REMINDER',
    'status_changed' => 'COM_PHOCACART_MAIL_BOOKING_HEADLINE_STATUS_CHANGED',
];

$headlineKey = $headlines[$eventType] ?? 'COM_PHOCACART_MAIL_BOOKING_HEADLINE_STATUS_CHANGED';
echo '<h2 style="color: #2e486b; margin: 16px 0;">' . Text::_($headlineKey) . '</h2>';
