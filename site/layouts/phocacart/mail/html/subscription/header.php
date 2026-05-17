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
$params = $displayData['params'];
$attachments = &$displayData['attachments'];
$eventType = $displayData['eventType'];
$user = $displayData['user'];

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
echo Text::sprintf('COM_PHOCACART_MAIL_SUBSCRIPTION_GREETING', htmlspecialchars($user->name));
echo '</p>';

// Event-specific headline
$headlines = [
    'activated' => 'COM_PHOCACART_MAIL_SUBSCRIPTION_HEADLINE_ACTIVATED',
    'renewed' => 'COM_PHOCACART_MAIL_SUBSCRIPTION_HEADLINE_RENEWED',
    'expiring_soon' => 'COM_PHOCACART_MAIL_SUBSCRIPTION_HEADLINE_EXPIRING_SOON',
    'expired' => 'COM_PHOCACART_MAIL_SUBSCRIPTION_HEADLINE_EXPIRED',
    'canceled' => 'COM_PHOCACART_MAIL_SUBSCRIPTION_HEADLINE_CANCELED',
    'status_changed' => 'COM_PHOCACART_MAIL_SUBSCRIPTION_HEADLINE_STATUS_CHANGED',
];

$headlineKey = $headlines[$eventType] ?? 'COM_PHOCACART_MAIL_SUBSCRIPTION_HEADLINE_STATUS_CHANGED';
echo '<h2 style="color: #2e486b; margin: 16px 0;">' . Text::_($headlineKey) . '</h2>';
