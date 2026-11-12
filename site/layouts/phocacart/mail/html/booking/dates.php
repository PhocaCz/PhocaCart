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
/** @var array $styles */
$styles    = $displayData['styles'];
$booking   = $displayData['booking'];
$eventType = $displayData['eventType'];

$dateStart = !empty($booking->booking_date_start) ? HTMLHelper::date($booking->booking_date_start, Text::_('DATE_FORMAT_LC2')) : '-';
$dateEnd   = !empty($booking->booking_date_end) ? HTMLHelper::date($booking->booking_date_end, Text::_('DATE_FORMAT_LC2')) : '';

$hasTime = !empty($booking->booking_time_start);
$timeStart = $hasTime ? substr($booking->booking_time_start, 0, 5) : '';
$timeEnd   = !empty($booking->booking_time_end) ? substr($booking->booking_time_end, 0, 5) : '';

$nights = 0;
if (!empty($booking->booking_date_start) && !empty($booking->booking_date_end)) {
    $start  = new \DateTime($booking->booking_date_start);
    $end    = new \DateTime($booking->booking_date_end);
    $diff   = $start->diff($end);
    $nights = $diff->days;
}
?>

<table style="<?= $styles['w100'] ?> margin-top: 16px;">
    <tbody>
        <tr>
            <td style="width: <?= !empty($dateEnd) ? '50%' : '100%' ?>; padding: 12px; background-color: #f8f9fa; border-radius: 4px; vertical-align: top;">
                <div style="<?= $styles['label'] ?>"><?= !empty($dateEnd) ? Text::_('COM_PHOCACART_BOOKING_DATE_START') : Text::_('COM_PHOCACART_BOOKING_DATE') ?></div>
                <div style="<?= $styles['value'] ?>"><?= $dateStart ?></div>
                <?php if ($hasTime): ?>
                    <div style="margin-top: 6px; font-size: 14px; color: #495057;">
                        <span style="<?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_TIME') ?>:</span>
                        <strong><?= htmlspecialchars($timeStart . (!empty($timeEnd) ? ' - ' . $timeEnd : '')) ?></strong>
                    </div>
                <?php endif; ?>
            </td>
            <?php if (!empty($dateEnd)): ?>
            <td style="width: 8px;"></td>
            <td style="width: 50%; padding: 12px; background-color: #f8f9fa; border-radius: 4px; vertical-align: top;">
                <div style="<?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_BOOKING_DATE_END') ?></div>
                <div style="<?= $styles['value'] ?>"><?= $dateEnd ?></div>
                <?php if ($nights > 0): ?>
                    <div style="margin-top: 6px; font-size: 14px; color: #2e486b;">
                        <strong><?= $nights . ' ' . Text::_($nights > 1 ? 'COM_PHOCACART_BOOKING_NIGHTS' : 'COM_PHOCACART_BOOKING_NIGHT') ?></strong>
                    </div>
                <?php endif; ?>
            </td>
            <?php endif; ?>
        </tr>
    </tbody>
</table>

<?php if (!empty($booking->quantity) && $booking->quantity > 1 && $nights == 0): ?>
<div style="margin-top: 12px; color: #6c757d;">
    <?= Text::_('COM_PHOCACART_QUANTITY') ?>: <strong><?= (int)$booking->quantity ?></strong>
</div>
<?php endif; ?>

