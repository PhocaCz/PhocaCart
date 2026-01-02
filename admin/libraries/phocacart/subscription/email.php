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
use Joomla\CMS\Router\Route;

class PhocacartSubscriptionEmail
{
    /**
     * Event type constants
     */


    /**
     * Prepare subscription-specific mail data
     *
     * @param   object  $subscription  The subscription object
     * @param   object  $product       The product object
     * @param   object  $user          The user object
     * @param   string  $eventType     The event type
     *
     * @return  array   Mail data for template tags
     */
    public static function prepareSubscriptionMailData(object $subscription, object $product, object $user, string $eventType = ''): array
    {
        $app = Factory::getApplication();
        $siteUrl = Uri::root();

        $userLang = $user->getParam('language', Factory::getApplication()->get('language'));
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_phocacart', JPATH_ADMINISTRATOR, $userLang, true);
        $language->load('com_phocacart', JPATH_SITE, $userLang, true);


        // Calculate days remaining if subscription has end_date
        $daysRemaining = 0;
        if ($subscription->end_date) {
            $now = new \DateTime();
            $end = new \DateTime($subscription->end_date);
            $diff = $now->diff($end);
            $daysRemaining = $diff->invert ? 0 : $diff->days;
        }

        // Get status text
        $statusText = Text::_(\PhocacartSubscription::getStatus($subscription->status));

        // Format dates
        $startDate = $subscription->start_date ? HTMLHelper::date($subscription->start_date, Text::_('DATE_FORMAT_LC2')) : '';
        $endDate = $subscription->end_date ? HTMLHelper::date($subscription->end_date, Text::_('DATE_FORMAT_LC2')) : '';

        $mailData = MailHelper::prepareMailData([
            // User data
            'user_name' => $user->name,
            'user_username' => $user->username,
            'user_email' => $user->email,

            // Product data
            'product_id' => $product->id,
            'product_name' => $product->title,
            'product_title' => $product->title,
            'product_sku' => $product->sku ?? '',
            //'product_link' => $siteUrl . 'index.php?option=com_phocacart&view=item&id=' . (int)$product->id,
            'product_link' => MailHelper::link(PhocacartRoute::getItemRoute($product->id, $product->catid, $product->alias, $product->catalias)),

            // Subscription data
            'subscription_id' => $subscription->id,
            'subscription_status' => $statusText,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'days_remaining' => $daysRemaining,
            'renewal_count' => $subscription->renewal_count ?? 0,

            // Event-specific
            'event_type' => $eventType,
            'renewal_date' => $startDate,
            'new_end_date' => $endDate,
            'expired_date' => $endDate,
            'cancellation_date' => HTMLHelper::date('now', Text::_('DATE_FORMAT_LC2')),
            'cancellation_reason' => '',

            // Links
            /*'renewal_url' => $siteUrl . 'index.php?option=com_phocacart&view=item&id=' . (int)$product->id,
            'resubscribe_url' => $siteUrl . 'index.php?option=com_phocacart&view=item&id=' . (int)$product->id,
            'download_url' => $siteUrl . 'index.php?option=com_phocacart&view=account',
            'account_url' => $siteUrl . 'index.php?option=com_phocacart&view=account',*/

            'renewal_url' => MailHelper::link(PhocacartRoute::getItemRoute($product->id, $product->catid, $product->alias, $product->catalias)),
            'resubscribe_url' => MailHelper::link(PhocacartRoute::getItemRoute($product->id, $product->catid, $product->alias, $product->catalias)),
            'download_url' => MailHelper::link(PhocacartRoute::getAccountRoute($product->id, $product->catid)),
            'account_url' => MailHelper::link(PhocacartRoute::getAccountRoute($product->id, $product->catid))
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
            \Phocacartsubscription::EVENT_ACTIVATED => 'com_phocacart.subscription.activated',
            \Phocacartsubscription::EVENT_RENEWED => 'com_phocacart.subscription.renewed',
            \Phocacartsubscription::EVENT_EXPIRING_SOON => 'com_phocacart.subscription.expiring_soon',
            \Phocacartsubscription::EVENT_EXPIRED => 'com_phocacart.subscription.expired',
            \Phocacartsubscription::EVENT_CANCELED => 'com_phocacart.subscription.canceled',
            \Phocacartsubscription::EVENT_STATUS_CHANGED => 'com_phocacart.subscription.status_changed',
        ];

        return $templateMap[$eventType] ?? 'com_phocacart.subscription.status_changed';
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
        // Get the system plugin parameters
        $plugin = PluginHelper::getPlugin('system', 'phocacartsubscription');

        if (!$plugin) {
            // Plugin not installed or disabled - default to sending emails
            return true;
        }

        $params = new Registry($plugin->params);

        // Map event types to parameter names
        $paramMap = [
            \Phocacartsubscription::EVENT_ACTIVATED => 'email_activated',
            \Phocacartsubscription::EVENT_RENEWED => 'email_renewed',
            \Phocacartsubscription::EVENT_EXPIRING_SOON => 'email_expiring_soon',
            \Phocacartsubscription::EVENT_EXPIRED => 'email_expired',
            \Phocacartsubscription::EVENT_CANCELED => 'email_canceled',
            \Phocacartsubscription::EVENT_STATUS_CHANGED => 'email_status_changed',
        ];

        $paramName = $paramMap[$eventType] ?? null;

        if (!$paramName) {
            return true; // Unknown event type - default to enabled
        }

        // Default values: most are enabled (1), status_changed is disabled (0)
        $default = ($eventType === \Phocacartsubscription::EVENT_STATUS_CHANGED) ? 0 : 1;

        return (bool)$params->get($paramName, $default);
    }


    public static function sendEmail(object $subscription, ?int $statusOld, int $statusNew, string $eventType, bool $notifyUser = true, bool $notifyAdmin = false, string $triggeredBy = 'email'): int
    {

        if ($triggeredBy != 'email') {
            $triggeredBy = $triggeredBy . '_email';
        }

        // Check if email is enabled for this event type
        if (!self::isEmailEnabled($eventType)) {
            PhocacartLog::add(3, 'Subscription Email - Disabled', $subscription->id, 'Email disabled for event: ' . $eventType);
            return 0;
        }

        $db = Factory::getContainer()->get('DatabaseDriver');

        // Load user
        $userFactory = Factory::getContainer()->get(UserFactoryInterface::class);
        $user = $userFactory->loadUserById($subscription->user_id);

        if (!$user->id) {
            PhocacartLog::add(2, 'Subscription Email - ERROR', $subscription->id, 'User not found: ' . $subscription->user_id);
            return -1;
        }

        // Load product
        /*$query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__phocacart_products'))
            ->where($db->quoteName('id') . ' = ' . (int)$subscription->product_id);
        $db->setQuery($query);
        $product = $db->loadObject();*/
        $product = \PhocacartProduct::getProduct((int)$subscription->product_id);

        if (!$product) {
            PhocacartLog::add(2, 'Subscription Email - ERROR', $subscription->id, 'Product not found: ' . $subscription->product_id);
            return -1;
        }

        // Check if user has a valid email
        if (!$notifyUser || !JoomlaMailHelper::isEmailAddress($user->email)) {
            PhocacartLog::add(3, 'Subscription Email - Skipped', $subscription->id, 'No valid user email for notification');
            return 0;
        }

        // Get user's language preference
        $userLang = $user->getParam('language', Factory::getApplication()->get('language'));

        // Prepare mail data
        $mailData = self::prepareSubscriptionMailData($subscription, $product, $user, $eventType);

        // Render subscription body for email template
        $mailData['html.document'] = self::renderSubscriptionBody($subscription, $product, $user, $eventType, 'html', $mailData);
        $mailData['text.document'] = self::renderSubscriptionBody($subscription, $product, $user, $eventType, 'text', $mailData);

        // Check/create mail template if it doesn't exist
        self::checkSubscriptionMailTemplate($eventType);

        // Get template key
        $templateKey = self::getTemplateKey($eventType);

        // Create and send email
        try {
            $mailer = new MailTemplate($templateKey, $userLang);
            $mailer->addTemplateData($mailData);
            $mailer->addRecipient($user->email, $user->name);

            if ($mailer->send()) {
                PhocacartLog::add(1, 'Subscription Email - Sent', $subscription->id, 'Email sent to: ' . $user->email . ' (Event: ' . $eventType . ')');

                // Update history to mark notification sent
                \PhocacartSubscription::logHistory($subscription->id, $statusNew, $eventType, $triggeredBy, $statusOld, (int)$notifyUser);

                return 1;
            } else {
                PhocacartLog::add(2, 'Subscription Email - Failed', $subscription->id, 'Failed to send email to: ' . $user->email);
                return -1;
            }
        } catch (\Exception $e) {
            PhocacartLog::add(2, 'Subscription Email - Exception', $subscription->id, $e->getMessage());
            Factory::getApplication()->enqueueMessage(Text::_($e->getMessage()), 'warning');
            return -1;
        }
    }

    /**
     * Render subscription email body from layout
     *
     * @param   object  $subscription  The subscription object
     * @param   object  $product       The product object
     * @param   object  $user          The user object
     * @param   string  $eventType     The event type
     * @param   string  $format        'html' or 'text'
     * @param   array   $mailData      Reference to mail data array
     *
     * @return  string  Rendered body
     */
    public static function renderSubscriptionBody(object $subscription, object $product, object $user, string $eventType, string $format, array &$mailData): string
    {
        $displayData = [];
        $displayData['params'] = \PhocacartUtils::getComponentParameters();
        $displayData['subscription'] = $subscription;
        $displayData['product'] = $product;
        $displayData['user'] = $user;
        $displayData['eventType'] = $eventType;

        $blocks = [];
        $displayData['blocks'] = &$blocks;

        $attachments = [];
        $displayData['attachments'] = &$attachments;

        $displayData['mailData'] = &$mailData;

        $layout = new SiteLayout('phocacart.mail.' . $format . '.subscription');
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
     * Check if subscription mail template exists, create if not
     *
     * @param   string  $eventType  The event type
     *
     * @return  void
     */
    public static function checkSubscriptionMailTemplate(string $eventType): void
    {
        $templateKey = self::getTemplateKey($eventType);

        $tags = [
            'html.document', 'text.document',
            'user_name', 'product_name', 'product_title',
            'start_date', 'end_date', 'days_remaining',
            'subscription_status', 'renewal_count',
            'site_name', 'site_link', 'renewal_url', 'account_url'
        ];

        $subjectKey = 'COM_PHOCACART_MAIL_SUBSCRIPTION_' . strtoupper($eventType) . '_SUBJECT';
        $bodyKey = 'COM_PHOCACART_MAIL_SUBSCRIPTION_' . strtoupper($eventType) . '_BODY';
        $htmlBodyKey = 'COM_PHOCACART_MAIL_SUBSCRIPTION_' . strtoupper($eventType) . '_HTMLBODY';

        MailTemplate::checkTemplate($templateKey, $subjectKey, $bodyKey, $tags, $htmlBodyKey);
    }

/*
    protected static function logEmailSent(object $subscription, string $eventType, string $triggeredBy = 'email'): void
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        if (isset($subscription->id) && (int)$subscription->id > 0) {

            $history                  = new \stdClass();
            $history->subscription_id = (int)$subscription->id;
            $history->status_to       = isset($subscription->status) ? (int)$subscription->status : 0;
            $history->event_type      = 'email_sent_' . $eventType;
            $history->event_date      = Factory::getDate()->toSql();
            $history->triggered_by    = $triggeredBy;
            $history->notify_user     = 1;


            try {
                $db->insertObject('#__phocacart_subscription_history', $history);
            } catch (\Exception $e) {
                // Silently fail - logging should not break the email send
            }
        }
    }*/

    /**
     * Send expiration notification emails for subscriptions expiring soon
     *
     * @param   int  $daysBeforeExpiry  Number of days before expiry to send notification
     *
     * @return  int  Number of emails sent
     */
    public static function sendExpirationNotifications(int $daysBeforeExpiry = 7): int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Find active subscriptions expiring within the specified days
        // that haven't received a notification recently
        $query = $db->getQuery(true)
            ->select('s.*')
            ->from($db->quoteName('#__phocacart_subscriptions', 's'))
            ->where($db->quoteName('s.status') . ' = ' . \PhocacartSubscription::STATUS_ACTIVE)
            ->where($db->quoteName('s.end_date') . ' BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ' . (int)$daysBeforeExpiry . ' DAY)')
            ->where('NOT EXISTS (
                SELECT 1 FROM ' . $db->quoteName('#__phocacart_subscription_history') . ' h
                WHERE h.subscription_id = s.id
                AND h.event_type = ' . $db->quote('email_sent_expiring_soon') . '
                AND h.event_date >= DATE_SUB(NOW(), INTERVAL ' . (int)$daysBeforeExpiry . ' DAY)
            )');

        $db->setQuery($query);
        $subscriptions = $db->loadObjectList();

        $count = 0;
        foreach ($subscriptions as $subscription) {

            // No status change
            $statusOld = $subscription->status;
            $statusNew  = $subscription->status;

            $result = self::sendEmail($subscription, $statusOld, $statusNew, \Phocacartsubscription::EVENT_EXPIRING_SOON);
            if ($result === 1) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Send expiration emails for subscriptions that have just expired
     *
     * @return  int  Number of emails sent
     */
    public static function sendExpirationEmails(): int
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Find subscriptions that expired recently and haven't received expiration email
        $query = $db->getQuery(true)
            ->select('s.*')
            ->from($db->quoteName('#__phocacart_subscriptions', 's'))
            ->where($db->quoteName('s.status') . ' = ' . \PhocacartSubscription::STATUS_EXPIRED)
            ->where('NOT EXISTS (
                SELECT 1 FROM ' . $db->quoteName('#__phocacart_subscription_history') . ' h
                WHERE h.subscription_id = s.id
                AND h.event_type = ' . $db->quote('email_sent_expired') . '
            )');

        $db->setQuery($query);
        $subscriptions = $db->loadObjectList();

        $count = 0;
        foreach ($subscriptions as $subscription) {

            // No status change
            $statusOld = $subscription->status;
            $statusNew  = $subscription->status;

            $result = self::sendEmail($subscription, $statusOld, $statusNew, \Phocacartsubscription::EVENT_EXPIRED);
            if ($result === 1) {
                $count++;
            }
        }

        return $count;
    }
}
