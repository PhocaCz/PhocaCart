<?php
/**
 * @package    phocacart
 * @subpackage Library
 * @copyright  Copyright (C) Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

class PhocacartBooking
{
    // Status constants
    const STATUS_CONFIRMED       = 1;
    const STATUS_PENDING         = 2;
    const STATUS_CANCELLED       = 3;
    const STATUS_COMPLETED       = 4;
    const STATUS_NO_SHOW         = 5;
    const STATUS_PENDING_PAYMENT = 6;
    const STATUS_REFUNDED        = 7;

    // Mode constants
    const MODE_TIME_SLOT  = 1;
    const MODE_DATE_RANGE = 2;

    // Behavior constants (for ACL / Active status)
    const BEHAVIOR_NEUTRAL  = 0;
    const BEHAVIOR_ACTIVE   = 1;
    const BEHAVIOR_INACTIVE = 2;

    // Event constants
    const EVENT_CREATED        = 'created';
    const EVENT_CONFIRMED      = 'confirmed';
    const EVENT_CANCELLED      = 'cancelled';
    const EVENT_COMPLETED      = 'completed';
    const EVENT_STATUS_CHANGED = 'status_changed';
    const EVENT_REMINDER       = 'reminder';

    /**
     * Get booking events
     *
     * @return array
     */
    public static function getEvents(): array
    {
        return array(
            self::EVENT_CREATED        => 'created',
            self::EVENT_CONFIRMED      => 'confirmed',
            self::EVENT_CANCELLED      => 'cancelled',
            self::EVENT_COMPLETED      => 'completed',
            self::EVENT_STATUS_CHANGED => 'status_changed',
            self::EVENT_REMINDER       => 'reminder'
        );
    }

    /**
     * Get booking statuses (ID => Language key)
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return array(
            self::STATUS_CONFIRMED       => 'COM_PHOCACART_BOOKING_STATUS_CONFIRMED',
            self::STATUS_PENDING         => 'COM_PHOCACART_BOOKING_STATUS_PENDING',
            self::STATUS_CANCELLED       => 'COM_PHOCACART_BOOKING_STATUS_CANCELLED',
            self::STATUS_COMPLETED       => 'COM_PHOCACART_BOOKING_STATUS_COMPLETED',
            self::STATUS_NO_SHOW         => 'COM_PHOCACART_BOOKING_STATUS_NO_SHOW',
            self::STATUS_PENDING_PAYMENT => 'COM_PHOCACART_BOOKING_STATUS_PENDING_PAYMENT',
            self::STATUS_REFUNDED        => 'COM_PHOCACART_BOOKING_STATUS_REFUNDED'
        );
    }

    /**
     * Get CSS styles for statuses
     *
     * @return array
     */
    public static function getStatusStyles(): array
    {
        return array(
            self::STATUS_CONFIRMED       => 'status-active',
            self::STATUS_PENDING         => 'status-pending',
            self::STATUS_CANCELLED       => 'status-canceled',
            self::STATUS_COMPLETED       => 'status-completed',
            self::STATUS_NO_SHOW         => 'status-failed',
            self::STATUS_PENDING_PAYMENT => 'status-on-hold',
            self::STATUS_REFUNDED        => 'status-refunded'
        );
    }

    /**
     * Get language label for a status
     *
     * @param int $id
     * @return string
     */
    public static function getStatus(int $id): string
    {
        $statuses = self::getStatuses();
        return $statuses[$id] ?? '';
    }

    /**
     * Get CSS class for a status
     *
     * @param int $id
     * @return string
     */
    public static function getStatusStyle(int $id): string
    {
        $styles = self::getStatusStyles();
        return $styles[$id] ?? '';
    }

    /**
     * Get booking modes
     *
     * @return array
     */
    public static function getModes(): array
    {
        return array(
            self::MODE_TIME_SLOT  => 'COM_PHOCACART_BOOKING_MODE_TIME_SLOT',
            self::MODE_DATE_RANGE => 'COM_PHOCACART_BOOKING_MODE_DATE_RANGE'
        );
    }

    /**
     * Get language label for a mode
     *
     * @param int $mode
     * @return string
     */
    public static function getMode(int $mode): string
    {
        $modes = self::getModes();
        return $modes[$mode] ?? '';
    }

    /**
     * Get systemic behavior property for a status
     *
     * @param int $id
     * @return array
     */
    public static function getStatusProperties(int $id): array
    {
        $p = array('behavior' => self::BEHAVIOR_NEUTRAL);

        switch ($id) {
            case self::STATUS_CONFIRMED:
                $p['behavior'] = self::BEHAVIOR_ACTIVE;
                break;

            case self::STATUS_CANCELLED:
            case self::STATUS_REFUNDED:
            case self::STATUS_NO_SHOW:
                $p['behavior'] = self::BEHAVIOR_INACTIVE;
                break;

            case self::STATUS_PENDING:
            case self::STATUS_PENDING_PAYMENT:
            case self::STATUS_COMPLETED:
            default:
                $p['behavior'] = self::BEHAVIOR_NEUTRAL;
                break;
        }

        return $p;
    }

    /**
     * Log history event for a booking
     *
     * @param int         $bookingId
     * @param int         $statusNew
     * @param string      $eventType
     * @param string      $triggeredBy
     * @param int|null    $statusOld
     * @param int         $notifyUser
     * @param string|null $comment
     * @param string|null $metadata
     * @return bool
     */
    public static function logHistory(
        int $bookingId,
        int $statusNew,
        string $eventType,
        string $triggeredBy = 'system',
        ?int $statusOld = null,
        int $notifyUser = 0,
        ?string $comment = null,
        ?string $metadata = null
    ): bool {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $history = new \stdClass();
        $history->booking_id   = $bookingId;
        $history->status_from  = $statusOld;
        $history->status_to    = $statusNew;
        $history->event_type   = $eventType;
        $history->event_date   = Factory::getDate()->toSql();
        $history->triggered_by = $triggeredBy;
        $history->notify_user  = $notifyUser;
        $history->comment      = $comment;
        $history->metadata     = $metadata;

        try {
            return $db->insertObject('#__phocacart_booking_history', $history);
        } catch (\Exception $e) {
            if (class_exists('PhocacartLog')) {
                PhocacartLog::add(2, 'Booking Log History - Failed', $bookingId, 'Failed to log booking history: ID ' . $bookingId . ', error: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Check slot availability in Time Slot Mode
     *
     * @param int $productId
     * @param int $slotId
     * @param int $quantity
     * @return array ['available' => bool, 'remaining' => int, 'slot' => object|null]
     */
    public static function checkSlotAvailability(int $productId, int $slotId, int $quantity = 1): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__phocacart_booking_slots'))
            ->where($db->quoteName('id') . ' = ' . (int)$slotId)
            ->where($db->quoteName('product_id') . ' = ' . (int)$productId)
            ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);
        $slot = $db->loadObject();

        if (!$slot) {
            return ['available' => false, 'remaining' => 0, 'slot' => null, 'reason' => 'slot_not_found'];
        }

        $remaining = max(0, (int)$slot->capacity - (int)$slot->booked);
        $available = ($remaining >= $quantity);

        return [
            'available' => $available,
            'remaining' => $remaining,
            'slot'      => $slot,
            'reason'    => $available ? '' : 'capacity_exceeded'
        ];
    }

    /**
     * Check availability in Date Range Mode (rentals / stays)
     * Validates capacity day-by-day across the requested range.
     *
     * @param int    $productId
     * @param string $startDate (YYYY-MM-DD Check-in)
     * @param string $endDate   (YYYY-MM-DD Check-out)
     * @param int    $quantity
     * @param int|null $excludeBookingId
     * @return array ['available' => bool, 'max_booked_day' => int, 'capacity' => int, 'reason' => string]
     */
    public static function checkDateRangeAvailability(
        int $productId,
        string $startDate,
        string $endDate,
        int $quantity = 1,
        ?int $excludeBookingId = null
    ): array {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Fetch product booking configuration
        $query = $db->getQuery(true)
            ->select('id, booking_mode, booking_capacity, booking_min_advance_hours, booking_max_advance_days, booking_button_text')
            ->from($db->quoteName('#__phocacart_products'))
            ->where($db->quoteName('id') . ' = ' . (int)$productId);
        $db->setQuery($query);
        $product = $db->loadObject();

        if (!$product) {
            return ['available' => false, 'capacity' => 0, 'reason' => 'product_not_found'];
        }

        $start = new \DateTime($startDate);
        $end   = new \DateTime($endDate);

        if ($start >= $end) {
            return ['available' => false, 'capacity' => (int)$product->booking_capacity, 'reason' => 'invalid_date_range'];
        }

        // Active booking statuses that occupy capacity
        $activeStatuses = [
            self::STATUS_CONFIRMED,
            self::STATUS_PENDING,
            self::STATUS_PENDING_PAYMENT
        ];

        // Fetch all overlapping bookings for this product
        $query = $db->getQuery(true)
            ->select('id, booking_date_start, booking_date_end, quantity')
            ->from($db->quoteName('#__phocacart_bookings'))
            ->where($db->quoteName('product_id') . ' = ' . (int)$productId)
            ->where($db->quoteName('status') . ' IN (' . implode(',', $activeStatuses) . ')')
            ->where($db->quoteName('booking_date_start') . ' < ' . $db->quote($endDate))
            ->where($db->quoteName('booking_date_end') . ' > ' . $db->quote($startDate));

        if ($excludeBookingId !== null && (int)$excludeBookingId > 0) {
            $query->where($db->quoteName('id') . ' != ' . (int)$excludeBookingId);
        }

        $db->setQuery($query);
        $existingBookings = $db->loadObjectList();

        $capacity = (int)$product->booking_capacity;
        if ($capacity <= 0) {
            $capacity = 1;
        }

        // Iterate day-by-day (each night of the stay)
        $current = clone $start;
        $maxBooked = 0;

        while ($current < $end) {
            $currentDateStr = $current->format('Y-m-d');
            $dayBooked = 0;

            foreach ($existingBookings as $b) {
                // Booking covers this night if start <= current and end > current
                if ($b->booking_date_start <= $currentDateStr && $b->booking_date_end > $currentDateStr) {
                    $dayBooked += (int)$b->quantity;
                }
            }

            if ($dayBooked > $maxBooked) {
                $maxBooked = $dayBooked;
            }

            if (($dayBooked + $quantity) > $capacity) {
                return [
                    'available'      => false,
                    'conflict_date'  => $currentDateStr,
                    'day_booked'     => $dayBooked,
                    'capacity'       => $capacity,
                    'reason'         => 'capacity_exceeded'
                ];
            }

            $current->modify('+1 day');
        }

        return [
            'available'      => true,
            'max_booked_day' => $maxBooked,
            'capacity'       => $capacity,
            'reason'         => ''
        ];
    }

    /**
     * Calculate number of nights between two dates
     *
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public static function calculateNights(string $startDate, string $endDate): int
    {
        $start = new \DateTime($startDate);
        $end   = new \DateTime($endDate);
        if ($start >= $end) {
            return 0;
        }
        $diff = $start->diff($end);
        return (int)$diff->days;
    }

    /**
     * Get all available future slots for a product (Time Slot Mode)
     * Returns published slots with remaining capacity, from today onward.
     *
     * @param int $productId
     * @param int $daysAhead  How many days into the future to look (0 = unlimited)
     * @return array  Each item: id, slot_date, slot_time_start, slot_time_end, capacity, booked, remaining
     */
    public static function getAvailableSlotsForProduct(int $productId, int $daysAhead = 90): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $today = date('Y-m-d');

        $query = $db->getQuery(true)
            ->select('id, slot_date, slot_time_start, slot_time_end, capacity, booked,
                      (capacity - booked) AS remaining')
            ->from($db->quoteName('#__phocacart_booking_slots'))
            ->where($db->quoteName('product_id') . ' = ' . (int)$productId)
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('slot_date') . ' >= ' . $db->quote($today))
            ->where('(capacity - booked) > 0')
            ->order('slot_date ASC, slot_time_start ASC');

        if ($daysAhead > 0) {
            $maxDate = date('Y-m-d', strtotime('+' . $daysAhead . ' days'));
            $query->where($db->quoteName('slot_date') . ' <= ' . $db->quote($maxDate));
        }

        $db->setQuery($query);
        return (array)$db->loadObjectList();
    }

    /**
     * Get fully-booked dates for a product (Date Range Mode)
     * A date is fully booked when the sum of confirmed/pending bookings on that day >= capacity.
     * Used by the frontend calendar to disable unavailable dates.
     *
     * @param int $productId
     * @param int $daysAhead
     * @return array  Array of 'Y-m-d' strings that are fully booked
     */
    public static function getBookedDatesForProduct(int $productId, int $daysAhead = 180): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Fetch product capacity
        $query = $db->getQuery(true)
            ->select('booking_capacity')
            ->from($db->quoteName('#__phocacart_products'))
            ->where($db->quoteName('id') . ' = ' . (int)$productId);
        $db->setQuery($query);
        $product = $db->loadObject();

        if (!$product) {
            return [];
        }

        $capacity = max(1, (int)$product->booking_capacity);
        $today    = date('Y-m-d');
        $maxDate  = date('Y-m-d', strtotime('+' . $daysAhead . ' days'));

        $activeStatuses = [self::STATUS_CONFIRMED, self::STATUS_PENDING, self::STATUS_PENDING_PAYMENT];

        $query = $db->getQuery(true)
            ->select('booking_date_start, booking_date_end, quantity')
            ->from($db->quoteName('#__phocacart_bookings'))
            ->where($db->quoteName('product_id') . ' = ' . (int)$productId)
            ->where($db->quoteName('status') . ' IN (' . implode(',', $activeStatuses) . ')')
            ->where($db->quoteName('booking_date_start') . ' < ' . $db->quote($maxDate))
            ->where($db->quoteName('booking_date_end') . ' > ' . $db->quote($today));
        $db->setQuery($query);
        $bookings = (array)$db->loadObjectList();

        // Walk each day in the range, tally bookings
        $fullyBooked = [];
        $current = new \DateTime($today);
        $end     = new \DateTime($maxDate);

        while ($current < $end) {
            $dateStr = $current->format('Y-m-d');
            $dayBooked = 0;
            foreach ($bookings as $b) {
                if ($b->booking_date_start <= $dateStr && $b->booking_date_end > $dateStr) {
                    $dayBooked += (int)$b->quantity;
                }
            }
            if ($dayBooked >= $capacity) {
                $fullyBooked[] = $dateStr;
            }
            $current->modify('+1 day');
        }

        return $fullyBooked;
    }

    /**
     * Get bookings for a user (Frontend account view)
     *
     * @param int $userId
     * @return array
     */
    public static function getBookings(int $userId): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('a.*, p.title AS product_title, p.alias AS product_alias, p.catid, c.alias AS category_alias')
            ->from($db->quoteName('#__phocacart_bookings', 'a'))
            ->join('LEFT', $db->quoteName('#__phocacart_products', 'p') . ' ON a.product_id = p.id')
            ->join('LEFT', $db->quoteName('#__phocacart_categories', 'c') . ' ON p.catid = c.id')
            ->where('a.user_id = ' . (int)$userId)
            ->order('a.booking_date_start DESC, a.id DESC');

        $db->setQuery($query);
        return (array)$db->loadObjectList();
    }

    /**
     * Check if a proposed booking conflicts with items already in the user's cart
     * or exceeds product / slot capacity when combined with existing bookings and cart items.
     *
     * @param int   $productId
     * @param array $booking   ['booking_date_start', 'booking_date_end', 'slot_id', 'booking_mode']
     * @param array $cartItems Associative array of cart items from PhocacartCart ($this->items)
     * @param int   $quantity  Quantity to add
     * @return array ['conflict' => bool, 'reason' => string]
     */
    public static function checkCartConflict(int $productId, array $booking, array $cartItems, int $quantity = 1): array
    {
        $mode = (int)($booking['booking_mode'] ?? self::MODE_TIME_SLOT);

        // 1. Check exact duplicate booking already in cart
        foreach ($cartItems as $k => $item) {
            $pId = (int)explode(':', $k)[0];
            if ($pId !== $productId || empty($item['booking'])) {
                continue;
            }
            $cBooking = $item['booking'];
            if ($mode === self::MODE_TIME_SLOT) {
                if (!empty($booking['slot_id']) && !empty($cBooking['slot_id']) && (int)$booking['slot_id'] === (int)$cBooking['slot_id']) {
                    return ['conflict' => true, 'reason' => 'exact_duplicate'];
                }
            } else {
                if ($cBooking['booking_date_start'] === $booking['booking_date_start'] &&
                    $cBooking['booking_date_end'] === $booking['booking_date_end']) {
                    return ['conflict' => true, 'reason' => 'exact_duplicate'];
                }
            }
        }

        // 2. Mode-specific capacity & overlap checks
        if ($mode === self::MODE_TIME_SLOT) {
            $slotId = (int)($booking['slot_id'] ?? 0);
            if ($slotId > 0) {
                $cartSlotQty = 0;
                foreach ($cartItems as $k => $item) {
                    $pId = (int)explode(':', $k)[0];
                    if ($pId === $productId && !empty($item['booking']['slot_id']) && (int)$item['booking']['slot_id'] === $slotId) {
                        $cartSlotQty += (int)($item['quantity'] ?? 1);
                    }
                }

                $slotCheck = self::checkSlotAvailability($productId, $slotId, $cartSlotQty + $quantity);
                if (!$slotCheck['available']) {
                    return ['conflict' => true, 'reason' => 'slot_capacity_exceeded'];
                }
            }
        } else {
            // Date Range Mode
            $startDate = $booking['booking_date_start'] ?? '';
            $endDate   = $booking['booking_date_end'] ?? '';

            if (empty($startDate) || empty($endDate) || $startDate >= $endDate) {
                return ['conflict' => true, 'reason' => 'invalid_dates'];
            }

            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select('booking_capacity')
                ->from($db->quoteName('#__phocacart_products'))
                ->where($db->quoteName('id') . ' = ' . (int)$productId);
            $db->setQuery($query);
            $capacity = max(1, (int)$db->loadResult());

            $start = new \DateTime($startDate);
            $end   = new \DateTime($endDate);

            // Fetch DB active bookings covering this range
            $activeStatuses = [self::STATUS_CONFIRMED, self::STATUS_PENDING, self::STATUS_PENDING_PAYMENT, self::STATUS_COMPLETED];
            $query = $db->getQuery(true)
                ->select('booking_date_start, booking_date_end, quantity')
                ->from($db->quoteName('#__phocacart_bookings'))
                ->where($db->quoteName('product_id') . ' = ' . (int)$productId)
                ->where($db->quoteName('status') . ' IN (' . implode(',', $activeStatuses) . ')')
                ->where($db->quoteName('booking_date_start') . ' < ' . $db->quote($endDate))
                ->where($db->quoteName('booking_date_end') . ' > ' . $db->quote($startDate));
            $db->setQuery($query);
            $dbBookings = (array)$db->loadObjectList();

            // Extract cart date ranges for this product
            $cartRanges = [];
            foreach ($cartItems as $k => $item) {
                $pId = (int)explode(':', $k)[0];
                if ($pId === $productId && !empty($item['booking'])) {
                    $cartRanges[] = [
                        'start' => $item['booking']['booking_date_start'],
                        'end'   => $item['booking']['booking_date_end'],
                    ];
                }
            }

            // Walk day by day (each night of stay)
            $curr = clone $start;
            while ($curr < $end) {
                $dayStr = $curr->format('Y-m-d');
                $bookedOnDay = 0;

                // Tally DB bookings
                foreach ($dbBookings as $b) {
                    if ($b->booking_date_start <= $dayStr && $b->booking_date_end > $dayStr) {
                        $bookedOnDay += (int)$b->quantity;
                    }
                }

                // Tally Cart bookings (each booking in cart occupies 1 unit of capacity per night)
                foreach ($cartRanges as $cr) {
                    if ($cr['start'] <= $dayStr && $cr['end'] > $dayStr) {
                        $bookedOnDay += 1;
                    }
                }

                // Adding new booking (occupies 1 unit of capacity)
                if (($bookedOnDay + 1) > $capacity) {
                    return [
                        'conflict'      => true,
                        'reason'        => 'range_conflict',
                        'conflict_date' => $dayStr,
                        'capacity'      => $capacity,
                        'booked'        => $bookedOnDay,
                    ];
                }

                $curr->modify('+1 day');
            }
        }

        return ['conflict' => false, 'reason' => ''];
    }
}

