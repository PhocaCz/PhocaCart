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

$d            = $displayData;
$product      = $d['product']      ?? null;
$bookedDates  = $d['booked_dates'] ?? [];  // Array of fully-booked 'Y-m-d' strings
$minAdvHours  = isset($product->booking_min_advance_hours) ? (int)$product->booking_min_advance_hours : 0;
$maxAdvDays   = isset($product->booking_max_advance_days)  && $product->booking_max_advance_days !== null
                ? (int)$product->booking_max_advance_days : 365;
$checkInTime  = isset($product->booking_checkin_time)  && $product->booking_checkin_time  ? substr($product->booking_checkin_time, 0, 5)  : '';
$checkOutTime = isset($product->booking_checkout_time) && $product->booking_checkout_time ? substr($product->booking_checkout_time, 0, 5) : '';
$pid          = (int)($product->id ?? 0);

// Minimum check-in date based on advance hours
$minDate = date('Y-m-d', strtotime('+' . $minAdvHours . ' hours'));
$maxDate = date('Y-m-d', strtotime('+' . $maxAdvDays . ' days'));

// Encode booked dates for JS
$bookedDatesJson = json_encode(array_values($bookedDates));

// Register booking JS from media/com_phocacart/js/phoca/
//Factory::getApplication()->getDocument()->getWebAssetManager()
//    ->registerAndUseScript('com_phocacart.booking', 'media/com_phocacart/js/phoca/phocacartbooking.js', ['version' => 'auto'], ['defer' => true]);
$media = PhocacartRenderMedia::getInstance('main');
$media->loadBooking();
?>
<div class="ph-booking-daterange-picker"
     id="phBookingDateRange<?php echo $pid; ?>"
     data-product-id="<?php echo $pid; ?>"
     data-min-date="<?php echo htmlspecialchars($minDate); ?>"
     data-max-date="<?php echo htmlspecialchars($maxDate); ?>"
     data-booked-dates="<?php echo htmlspecialchars($bookedDatesJson, ENT_QUOTES, 'UTF-8'); ?>"
     data-text-night="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_BOOKING_NIGHT'), ENT_QUOTES, 'UTF-8'); ?>"
     data-text-nights="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_BOOKING_NIGHTS'), ENT_QUOTES, 'UTF-8'); ?>"
     data-text-blocked="<?php echo htmlspecialchars(Text::_('COM_PHOCACART_ERROR_BOOKING_DATE_RANGE_FULL'), ENT_QUOTES, 'UTF-8'); ?>">

    <div class="ph-booking-section-title">
        <strong><?php echo Text::_('COM_PHOCACART_BOOKING_SELECT_DATES'); ?></strong>
    </div>

    <!-- Visual Interactive Calendar -->
    <div class="ph-booking-calendar-wrapper" id="phBookingCalendarWrapper<?php echo $pid; ?>">
        <div class="ph-booking-calendar-legend">
            <span class="ph-legend-item"><span class="ph-legend-dot ph-legend-available"></span> <?php echo Text::_('COM_PHOCACART_BOOKING_AVAILABLE'); ?></span>
            <span class="ph-legend-item"><span class="ph-legend-dot ph-legend-booked"></span> <?php echo Text::_('COM_PHOCACART_BOOKING_OCCUPIED'); ?></span>
            <span class="ph-legend-item"><span class="ph-legend-dot ph-legend-selected"></span> <?php echo Text::_('COM_PHOCACART_BOOKING_SELECTED'); ?></span>
        </div>
        <div class="ph-booking-calendar-box" id="phBookingCalendarBox<?php echo $pid; ?>"></div>
    </div>

    <div class="ph-booking-daterange-fields">
        <div class="ph-booking-field-group">
            <label for="phDateStart<?php echo $pid; ?>"><?php echo Text::_('COM_PHOCACART_BOOKING_CHECKIN_DATE'); ?></label>
            <input type="date"
                   id="phDateStart<?php echo $pid; ?>"
                   name="booking_date_start"
                   class="ph-booking-date-input form-control"
                   min="<?php echo htmlspecialchars($minDate); ?>"
                   max="<?php echo htmlspecialchars($maxDate); ?>"
                   required
                   placeholder="YYYY-MM-DD">
            <?php if ($checkInTime) : ?>
                <small class="ph-booking-time-info"><?php echo Text::_('COM_PHOCACART_BOOKING_CHECKIN_TIME'); ?>: <?php echo htmlspecialchars($checkInTime); ?></small>
            <?php endif; ?>
        </div>

        <div class="ph-booking-field-separator">→</div>

        <div class="ph-booking-field-group">
            <label for="phDateEnd<?php echo $pid; ?>"><?php echo Text::_('COM_PHOCACART_BOOKING_CHECKOUT_DATE'); ?></label>
            <input type="date"
                   id="phDateEnd<?php echo $pid; ?>"
                   name="booking_date_end"
                   class="ph-booking-date-input form-control"
                   min="<?php echo htmlspecialchars($minDate); ?>"
                   max="<?php echo htmlspecialchars($maxDate); ?>"
                   required
                   placeholder="YYYY-MM-DD">
            <?php if ($checkOutTime) : ?>
                <small class="ph-booking-time-info"><?php echo Text::_('COM_PHOCACART_BOOKING_CHECKOUT_TIME'); ?>: <?php echo htmlspecialchars($checkOutTime); ?></small>
            <?php endif; ?>
        </div>
    </div>

    <!-- Summary line: shows number of nights after selection -->
    <div class="ph-booking-nights-summary" id="phBookingNights<?php echo $pid; ?>" style="display:none">
        <span class="ph-booking-nights-label"></span>
    </div>

    <!-- Unavailability notice shown when dates overlap booked range -->
    <div class="ph-booking-unavailable-notice" id="phBookingUnavailable<?php echo $pid; ?>" style="display:none">
        <span class="alert alert-danger"><?php echo Text::_('COM_PHOCACART_BOOKING_DATES_UNAVAILABLE'); ?></span>
    </div>

    <input type="hidden" name="booking_mode" value="2">
    <?php if ($checkInTime)  : ?><input type="hidden" name="booking_time_start" value="<?php echo htmlspecialchars($checkInTime); ?>"><?php endif; ?>
    <?php if ($checkOutTime) : ?><input type="hidden" name="booking_time_end"   value="<?php echo htmlspecialchars($checkOutTime); ?>"><?php endif; ?>
</div>
