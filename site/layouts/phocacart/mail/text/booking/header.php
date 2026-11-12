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
/** @var Joomla\Registry\Registry $params */
$params    = $displayData['params'];
$eventType = $displayData['eventType'];
$user      = $displayData['user'];

// Store header
if ($store_title = $params->get('store_title')) {
    echo $store_title . "\n";
    echo str_repeat('=', strlen($store_title)) . "\n\n";
}

// Personalized greeting
echo Text::sprintf('COM_PHOCACART_MAIL_BOOKING_GREETING', $user->name) . "\n\n";

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
echo Text::_($headlineKey) . "\n\n";

