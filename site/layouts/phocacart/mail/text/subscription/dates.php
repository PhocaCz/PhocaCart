<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/** @var array $displayData */
$subscription = $displayData['subscription'];
$eventType = $displayData['eventType'];

$startDate = $subscription->start_date ? HTMLHelper::date($subscription->start_date, Text::_('DATE_FORMAT_LC2')) : '-';
$endDate = $subscription->end_date ? HTMLHelper::date($subscription->end_date, Text::_('DATE_FORMAT_LC2')) : '-';

echo Text::_('COM_PHOCACART_START_DATE') . ": " . $startDate . "\n";
echo Text::_('COM_PHOCACART_END_DATE') . ": " . $endDate . "\n";

// Calculate days remaining for expiring_soon event
if ($eventType === 'expiring_soon' && $subscription->end_date) {
    $now = new \DateTime();
    $end = new \DateTime($subscription->end_date);
    $diff = $now->diff($end);
    $daysRemaining = $diff->days;
    
    if ($daysRemaining > 0) {
        echo "\n" . Text::sprintf('COM_PHOCACART_SUBSCRIPTION_DAYS_REMAINING', $daysRemaining) . "\n";
    }
}

if ($subscription->renewal_count > 0) {
    echo Text::sprintf('COM_PHOCACART_SUBSCRIPTION_RENEWAL_COUNT', $subscription->renewal_count) . "\n";
}

echo "\n";
