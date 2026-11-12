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
$booking   = $displayData['booking'];
$eventType = $displayData['eventType'];

$dateStart = !empty($booking->booking_date_start) ? HTMLHelper::date($booking->booking_date_start, Text::_('DATE_FORMAT_LC2')) : '-';
$dateEnd   = !empty($booking->booking_date_end) ? HTMLHelper::date($booking->booking_date_end, Text::_('DATE_FORMAT_LC2')) : '';

if (!empty($dateEnd)) {
    echo Text::_('COM_PHOCACART_BOOKING_DATE_START') . ": " . $dateStart . "\n";
    echo Text::_('COM_PHOCACART_BOOKING_DATE_END') . ": " . $dateEnd . "\n";
} else {
    echo Text::_('COM_PHOCACART_BOOKING_DATE') . ": " . $dateStart . "\n";
}

if (!empty($booking->booking_time_start)) {
    $timeStart = substr($booking->booking_time_start, 0, 5);
    $timeEnd   = !empty($booking->booking_time_end) ? substr($booking->booking_time_end, 0, 5) : '';
    echo Text::_('COM_PHOCACART_TIME') . ": " . $timeStart . (!empty($timeEnd) ? ' - ' . $timeEnd : '') . "\n";
}

if (!empty($booking->booking_date_start) && !empty($booking->booking_date_end)) {
    $start  = new \DateTime($booking->booking_date_start);
    $end    = new \DateTime($booking->booking_date_end);
    $diff   = $start->diff($end);
    $nights = $diff->days;
    if ($nights > 0) {
        echo $nights . ' ' . Text::_($nights > 1 ? 'COM_PHOCACART_BOOKING_NIGHTS' : 'COM_PHOCACART_BOOKING_NIGHT') . "\n";
    }
}

if (!empty($booking->quantity) && $booking->quantity > 1) {
    echo Text::_('COM_PHOCACART_QUANTITY') . ": " . (int)$booking->quantity . "\n";
}

echo "\n";

