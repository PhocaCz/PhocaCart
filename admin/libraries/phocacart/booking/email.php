<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper as JoomlaMailHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Mail\MailHelper;
use Phoca\PhocaCart\Mail\MailTemplate;
use Phoca\PhocaCart\Layout\SiteLayout;

class PhocacartBookingEmail
{
    /**
     * Prepare booking-specific mail data
     *
     * @param   object  $booking    The booking object
     * @param   object  $product    The product object
     * @param   object  $user       The user object
     * @param   string  $eventType  The event type
     *
     * @return  array   Mail data for template tags
     */
    public static function prepareBookingMailData(object $booking, object $product, object $user, string $eventType = ''): array
    {
        $userLang = $user->getParam('language', Factory::getApplication()->get('language'));
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_phocacart', JPATH_ADMINISTRATOR, $userLang, true);
        $language->load('com_phocacart', JPATH_SITE, $userLang, true);

        // Format dates
        $dateStart = !empty($booking->booking_date_start)
            ? HTMLHelper::date($booking->booking_date_start, Text::_('DATE_FORMAT_LC2'))
            : '';
        $dateEnd = !empty($booking->booking_date_end)
            ? HTMLHelper::date($booking->booking_date_end, Text::_('DATE_FORMAT_LC2'))
            : '';

        // Get status text
        $statusText = Text::_(\PhocacartBooking::getStatus((int)($booking->status ?? 0)));

        // Calculate nights
        $nights = 0;
        if (!empty($booking->booking_date_start) && !empty($booking->booking_date_end)) {
            $start  = new \DateTime($booking->booking_date_start);
            $end    = new \DateTime($booking->booking_date_end);
            $diff   = $start->diff($end);
            $nights = $diff->days;
        }

        // Order number
        $orderNumber = '';
        if (!empty($booking->order_id)) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select($db->quoteName('order_number'))
                ->from($db->quoteName('#__phocacart_orders'))
                ->where($db->quoteName('id') . ' = ' . (int)$booking->order_id);
            $db->setQuery($query);
            $orderNumber = (string)($db->loadResult() ?? '');
        }

        $mailData = MailHelper::prepareMailData([
            // User data
            'user_name'           => $user->name,
            'user_email'          => $user->email,

            // Product data
            'product_id'          => $product->id,
            'product_name'        => $product->title,
            'product_title'       => $product->title,
            'product_sku'         => $product->sku ?? '',
            'product_link'        => MailHelper::link(PhocacartRoute::getItemRoute($product->id, $product->catid, $product->alias, $product->catalias)),

            // Booking data
            'booking_id'          => $booking->id,
            'booking_status'      => $statusText,
            'booking_date_start'  => $dateStart,
            'booking_date_end'    => $dateEnd,
            'booking_time_start'  => $booking->booking_time_start ?? '',
            'booking_time_end'    => $booking->booking_time_end ?? '',
            'booking_mode'        => $booking->booking_mode ?? 0,
            'booking_nights'      => $nights,

            // Order data
            'order_id'            => $booking->order_id ?? '',
            'order_number'        => $orderNumber,

            // Event-specific
            'event_type'          => $eventType,
            'cancellation_date'   => HTMLHelper::date('now', Text::_('DATE_FORMAT_LC2')),

            // Links
            'account_url'         => MailHelper::link(PhocacartRoute::getAccountRoute()),
        ]);

        return $mailData;
    }

    /**
     * Get the mail template key for an event type
     *
     * @param   string  $eventType  The event type
     *
     * @return  string  The mail template key
     */
    public static function getTemplateKey(string $eventType): string
    {
        $templateMap = [
            \PhocacartBooking::EVENT_CREATED        => 'com_phocacart.booking.created',
            \PhocacartBooking::EVENT_CONFIRMED      => 'com_phocacart.booking.confirmed',
            \PhocacartBooking::EVENT_CANCELLED      => 'com_phocacart.booking.cancelled',
            \PhocacartBooking::EVENT_COMPLETED      => 'com_phocacart.booking.completed',
            \PhocacartBooking::EVENT_REMINDER       => 'com_phocacart.booking.reminder',
            \PhocacartBooking::EVENT_STATUS_CHANGED => 'com_phocacart.booking.status_changed',
        ];

        return $templateMap[$eventType] ?? 'com_phocacart.booking.status_changed';
    }

    /**
     * Check if email is enabled for a specific event type
     *
     * @param   string  $eventType  The event type
     *
     * @return  bool  True if email is enabled
     */
    public static function isEmailEnabled(string $eventType): bool
    {
        $plugin = PluginHelper::getPlugin('system', 'phocacartbooking');

        if (!$plugin) {
            return true;
        }

        $params = new Registry($plugin->params);

        $paramMap = [
            \PhocacartBooking::EVENT_CREATED        => 'email_created',
            \PhocacartBooking::EVENT_CONFIRMED      => 'email_confirmed',
            \PhocacartBooking::EVENT_CANCELLED      => 'email_cancelled',
            \PhocacartBooking::EVENT_COMPLETED      => 'email_completed',
            \PhocacartBooking::EVENT_REMINDER       => 'email_reminder',
            \PhocacartBooking::EVENT_STATUS_CHANGED => 'email_status_changed',
        ];

        $paramName = $paramMap[$eventType] ?? null;

        if (!$paramName) {
            return true;
        }

        $offByDefault = [\PhocacartBooking::EVENT_REMINDER, \PhocacartBooking::EVENT_STATUS_CHANGED];
        $default = in_array($eventType, $offByDefault) ? 0 : 1;

        return (bool)$params->get($paramName, $default);
    }

    /**
     * Get CSS style class for booking status
     *
     * @param   int  $status  Booking status constant
     *
     * @return  string  CSS style key
     */
    public static function getBookingStatusStyle(int $status): string
    {
        $map = [
            \PhocacartBooking::STATUS_CONFIRMED  => 'status-active',
            \PhocacartBooking::STATUS_PENDING    => 'status-pending',
            \PhocacartBooking::STATUS_CANCELLED  => 'status-expired',
            \PhocacartBooking::STATUS_COMPLETED  => 'status-active',
        ];

        return $map[$status] ?? 'status-pending';
    }

    /**
     * Send booking email notification
     *
     * @param   object   $booking      The booking object
     * @param   ?int     $statusOld    Previous status (null if new)
     * @param   int      $statusNew    New status
     * @param   string   $eventType    The event type
     * @param   bool     $notifyUser   Whether to notify the user
     * @param   bool     $notifyAdmin  Whether to notify the admin
     * @param   string   $triggeredBy  What triggered this email
     *
     * @return  int  1 on success, 0 if disabled/skipped, -1 on error
     */
    public static function sendEmail(object $booking, ?int $statusOld, int $statusNew, string $eventType, bool $notifyUser = true, bool $notifyAdmin = false, string $triggeredBy = 'email'): int
    {
        if ($triggeredBy !== 'email') {
            $triggeredBy = $triggeredBy . '_email';
        }

        if (!self::isEmailEnabled($eventType)) {
            PhocacartLog::add(3, 'Booking Email - Disabled', $booking->id, 'Email disabled for event: ' . $eventType);
            return 0;
        }

        // Load user
        $userFactory = Factory::getContainer()->get(UserFactoryInterface::class);
        $user        = $userFactory->loadUserById($booking->user_id);

        if (!$user->id) {
            PhocacartLog::add(2, 'Booking Email - ERROR', $booking->id, 'User not found: ' . $booking->user_id);
            return -1;
        }

        // Load product
        $product = \PhocacartProduct::getProduct((int)$booking->product_id);

        if (!$product) {
            PhocacartLog::add(2, 'Booking Email - ERROR', $booking->id, 'Product not found: ' . $booking->product_id);
            return -1;
        }

        if (!$notifyUser || !JoomlaMailHelper::isEmailAddress($user->email)) {
            PhocacartLog::add(3, 'Booking Email - Skipped', $booking->id, 'No valid user email for notification');
            return 0;
        }

        $userLang = $user->getParam('language', Factory::getApplication()->get('language'));

        // Prepare mail data
        $mailData = self::prepareBookingMailData($booking, $product, $user, $eventType);

        // Render body from layout
        $mailData['html.document'] = self::renderBookingBody($booking, $product, $user, $eventType, 'html', $mailData);
        $mailData['text.document'] = self::renderBookingBody($booking, $product, $user, $eventType, 'text', $mailData);

        // Ensure template exists
        self::checkBookingMailTemplate($eventType);

        $templateKey = self::getTemplateKey($eventType);

        try {
            $mailer = new MailTemplate($templateKey, $userLang);
            $mailer->addTemplateData($mailData);
            $mailer->addRecipient($user->email, $user->name);

            // BCC admin if configured
            $plugin = PluginHelper::getPlugin('system', 'phocacartbooking');

            if ($plugin) {
                $params   = new Registry($plugin->params);
                $bccEmail = trim($params->get('bcc_email', ''));

                if (!empty($bccEmail) && JoomlaMailHelper::isEmailAddress($bccEmail)) {
                    $mailer->addRecipient($bccEmail, Text::_('PLG_SYSTEM_PHOCACARTBOOKING_EMAIL_STORE_MANAGER'), 'bcc');
                }
            }

            if ($mailer->send()) {
                PhocacartLog::add(1, 'Booking Email - Sent', $booking->id, 'Email sent to: ' . $user->email . ' (Event: ' . $eventType . ')');
                \PhocacartBooking::logHistory($booking->id, $statusNew, $eventType, $triggeredBy, $statusOld, (int)$notifyUser);
                return 1;
            } else {
                PhocacartLog::add(2, 'Booking Email - Failed', $booking->id, 'Failed to send email to: ' . $user->email);
                return -1;
            }
        } catch (\Exception $e) {
            PhocacartLog::add(2, 'Booking Email - Exception', $booking->id, $e->getMessage());
            Factory::getApplication()->enqueueMessage(Text::_($e->getMessage()), 'warning');
            return -1;
        }
    }

    /**
     * Render booking email body from layout
     *
     * @param   object  $booking    The booking object
     * @param   object  $product    The product object
     * @param   object  $user       The user object
     * @param   string  $eventType  The event type
     * @param   string  $format     'html' or 'text'
     * @param   array   $mailData   Reference to mail data array
     *
     * @return  string  Rendered body
     */
    public static function renderBookingBody(object $booking, object $product, object $user, string $eventType, string $format, array &$mailData): string
    {
        $displayData                 = [];
        $displayData['params']       = \PhocacartUtils::getComponentParameters();
        $displayData['booking']      = $booking;
        $displayData['product']      = $product;
        $displayData['user']         = $user;
        $displayData['eventType']    = $eventType;

        $blocks                   = [];
        $displayData['blocks']    = &$blocks;

        $attachments              = [];
        $displayData['attachments'] = &$attachments;

        $displayData['mailData']  = &$mailData;

        $layout = new SiteLayout('phocacart.mail.' . $format . '.booking');
        $result = $layout->render($displayData);

        foreach ($blocks as $name => $block) {
            $mailData[$format . '.' . $name] = $block;
        }

        if (isset($mailData['attachments'])) {
            $mailData['attachments'] = array_merge($mailData['attachments'], $displayData['attachments']);
        } else {
            $mailData['attachments'] = $attachments;
        }

        return $result;
    }

    /**
     * Check if booking mail template exists; create it if not
     *
     * @param   string  $eventType  The event type
     *
     * @return  void
     */
    public static function checkBookingMailTemplate(string $eventType): void
    {
        $templateKey = self::getTemplateKey($eventType);

        $tags = [
            'html.document', 'text.document',
            'user_name', 'product_name', 'product_title',
            'booking_id', 'booking_status',
            'booking_date_start', 'booking_date_end',
            'booking_time_start', 'booking_time_end',
            'booking_nights', 'order_number',
            'account_url', 'site_name', 'site_link',
        ];

        $subjectKey  = 'COM_PHOCACART_MAIL_BOOKING_' . strtoupper($eventType) . '_SUBJECT';
        $bodyKey     = 'COM_PHOCACART_MAIL_BOOKING_' . strtoupper($eventType) . '_BODY';
        $htmlBodyKey = 'COM_PHOCACART_MAIL_BOOKING_' . strtoupper($eventType) . '_HTMLBODY';

        MailTemplate::checkTemplate($templateKey, $subjectKey, $bodyKey, $tags, $htmlBodyKey);
    }
}
