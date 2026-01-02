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
$styles = $displayData['styles'];
$subscription = $displayData['subscription'];
$eventType = $displayData['eventType'];

$startDate = $subscription->start_date ? HTMLHelper::date($subscription->start_date, Text::_('DATE_FORMAT_LC2')) : '-';
$endDate = $subscription->end_date ? HTMLHelper::date($subscription->end_date, Text::_('DATE_FORMAT_LC2')) : '-';

// Calculate days remaining for expiring_soon event
$daysRemaining = 0;
if ($eventType === 'expiring_soon' && $subscription->end_date) {
    $now = new \DateTime();
    $end = new \DateTime($subscription->end_date);
    $diff = $now->diff($end);
    $daysRemaining = $diff->days;
}
?>

<table style="<?= $styles['w100'] ?> margin-top: 16px;">
    <tbody>
        <tr>
            <td style="width: 50%; padding: 12px; background-color: #f8f9fa; border-radius: 4px;">
                <div style="<?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_START_DATE') ?></div>
                <div style="<?= $styles['value'] ?>"><?= $startDate ?></div>
            </td>
            <td style="width: 8px;"></td>
            <td style="width: 50%; padding: 12px; background-color: #f8f9fa; border-radius: 4px;">
                <div style="<?= $styles['label'] ?>"><?= Text::_('COM_PHOCACART_END_DATE') ?></div>
                <div style="<?= $styles['value'] ?>"><?= $endDate ?></div>
            </td>
        </tr>
    </tbody>
</table>

<?php if ($eventType === 'expiring_soon' && $daysRemaining > 0): ?>
<div style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 16px; margin-top: 16px; text-align: center;">
    <strong style="color: #856404; font-size: 18px;">
        <?= Text::sprintf('COM_PHOCACART_SUBSCRIPTION_DAYS_REMAINING', $daysRemaining) ?>
    </strong>
</div>
<?php endif; ?>

<?php if ($subscription->renewal_count > 0): ?>
<div style="margin-top: 12px; color: #6c757d;">
    <?= Text::sprintf('COM_PHOCACART_SUBSCRIPTION_RENEWAL_COUNT', $subscription->renewal_count) ?>
</div>
<?php endif; ?>
