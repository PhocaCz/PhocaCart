<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$d       = $displayData;
$slots   = $d['slots']   ?? [];
$product = $d['product'] ?? null;
$s       = $d['s']       ?? [];
$pid     = (int)($product->id ?? 0);

if (empty($slots)) {
    echo '<div class="ph-booking-no-slots alert alert-warning">' . Text::_('COM_PHOCACART_BOOKING_NO_SLOTS_AVAILABLE') . '</div>';
    return;
}

// Register booking JS from media/com_phocacart/js/phoca/
//Factory::getApplication()->getDocument()->getWebAssetManager()
//    ->registerAndUseScript('com_phocacart.booking', 'media/com_phocacart/js/phoca/phocacartbooking.js', ['version' => 'auto'], ['defer' => true]);
$media = PhocacartRenderMedia::getInstance('main');
$media->loadBooking();

// Group slots by date
$byDate = [];
foreach ($slots as $slot) {
    $byDate[$slot->slot_date][] = $slot;
}
?>
<div class="ph-booking-slot-picker" id="phBookingSlotPicker<?php echo $pid; ?>" data-product-id="<?php echo $pid; ?>">
    <div class="ph-booking-section-title">
        <strong><?php echo Text::_('COM_PHOCACART_BOOKING_SELECT_SLOT'); ?></strong>
    </div>

    <?php foreach ($byDate as $date => $daySlots) : ?>
        <?php
        $dateObj   = new DateTime($date);
        $dateLabel = $dateObj->format('l, d F Y');
        ?>
        <div class="ph-booking-date-group">
            <div class="ph-booking-date-header"><?php echo htmlspecialchars($dateLabel); ?></div>
            <div class="ph-booking-slots-row">
                <?php foreach ($daySlots as $slot) : ?>
                    <?php
                    $timeStart = substr($slot->slot_time_start, 0, 5);
                    $timeEnd   = substr($slot->slot_time_end, 0, 5);
                    $remaining = (int)$slot->remaining;
                    $slotId    = (int)$slot->id;
                    ?>
                    <label class="ph-booking-slot-option" for="phBookingSlot<?php echo $slotId; ?>">
                        <input type="radio"
                               id="phBookingSlot<?php echo $slotId; ?>"
                               name="booking_slot_id"
                               value="<?php echo $slotId; ?>"
                               data-date="<?php echo htmlspecialchars($date); ?>"
                               data-time-start="<?php echo htmlspecialchars($timeStart); ?>"
                               data-time-end="<?php echo htmlspecialchars($timeEnd); ?>"
                               data-remaining="<?php echo $remaining; ?>"
                               required>
                        <span class="ph-booking-slot-time"><?php echo $timeStart; ?> – <?php echo $timeEnd; ?></span>
                        <span class="ph-booking-slot-remaining">
                            <?php echo Text::sprintf('COM_PHOCACART_BOOKING_SLOTS_REMAINING', $remaining); ?>
                        </span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Hidden fields passed to cart -->
    <input type="hidden" name="booking_date_start" id="phBookingDateStart<?php echo $pid; ?>" value="">
    <input type="hidden" name="booking_date_end"   id="phBookingDateEnd<?php echo $pid; ?>"   value="">
    <input type="hidden" name="booking_time_start" id="phBookingTimeStart<?php echo $pid; ?>" value="">
    <input type="hidden" name="booking_time_end"   id="phBookingTimeEnd<?php echo $pid; ?>"   value="">
    <input type="hidden" name="booking_mode"       value="1">
</div>
