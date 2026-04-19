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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Mail\MailHelper as JoomlaMailHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Phoca\PhocaCart\Mail\MailTemplate;

jimport('joomla.application.component.model');

/**
 * Model for the EU Right of Withdrawal (cancellation) view.
 *
 * @since  6.1.0
 */
class PhocaCartModelCancellation extends BaseDatabaseModel
{
    /** @var object|null  Cached order data */
    protected ?object $order = null;

    /**
     * Returns all data needed by the cancellation view.
     * Performs eligibility checks and returns an array with order data and
     * the eligibility status.
     *
     * @return  array|false  Data array on success, false when order not accessible.
     *
     * @since  6.1.0
     */
    public function getData(): array|false
    {
        $app     = Factory::getApplication();
        $params  = $app->getParams();
        $input   = $app->getInput();

        // Feature must be enabled
        $enabled = (int) $params->get('cancellation_enable', 0);
        if (!$enabled) {
            return false;
        }

        $orderId = (int) $input->get('id', 0, 'int');
        $token   = $input->get('o', '', 'string');

        if ($orderId <= 0) {
            return false;
        }

        $order = $this->_loadOrder($orderId, $token);
        if (!$order) {
            return false;
        }

        $this->order = $order;

        $eligibility = $this->_checkEligibility($order, $params);

        return [
            'order'       => $order,
            'eligibility' => $eligibility,
            'token'       => $token,
            'params'      => $params,
        ];
    }

    /**
     * Load the order from the database, enforcing ownership.
     *
     * @param   int     $orderId  Order ID.
     * @param   string  $token    Guest order token.
     *
     * @return  object|null
     *
     * @since  6.1.0
     */
    protected function _loadOrder(int $orderId, string $token): ?object
    {
        $app    = Factory::getApplication();
        $params = $app->getParams();
        $u      = PhocacartUser::getUser();

        $orderGuestAccess = (int) $params->get('order_guest_access', 0);
        if ($orderGuestAccess == 0) {
            $token = '';
        }

        $db    = $this->_db;
        $orderAssoc = PhocacartOrder::getOrder($orderId, $token, $u->id);

        if ($orderAssoc) {
            $order = (object) $orderAssoc;
            $order->has_vat      = false;
            $order->cancellation = new \stdClass();

            $orderUsers = PhocacartOrder::getOrderUser($order->id);
            if (!empty($orderUsers)) {
                foreach ($orderUsers as $ou) {
                    $type = (int) ($ou['type'] ?? 0);
                    
                    // We only care about name and email for the withdrawal notice.
                    // Prefer Billing address (Type 0) for these details.
                    if ($type === 0 || empty($order->cancellation->email)) {
                        $order->cancellation->name_first = $ou['name_first'] ?? '';
                        $order->cancellation->name_last  = $ou['name_last'] ?? '';
                        $order->cancellation->email      = $ou['email'] ?? '';
                    }

                    if (!empty($ou['vat_1']) || !empty($ou['vat_2'])) {
                        $order->has_vat = true;
                    }
                }
            }

            // Fallback for names and email from logged-in user if still missing
            if (empty($order->cancellation->email) && !empty($u->email)) {
                $order->cancellation->email = $u->email;
                $names = explode(' ', $u->name, 2);
                $order->cancellation->name_first = $order->cancellation->name_first ?: ($names[0] ?? '');
                $order->cancellation->name_last  = $order->cancellation->name_last  ?: ($names[1] ?? '');
            }

            return $order;
        }

        return null;
    }

    /**
     * Check whether an order is eligible for withdrawal.
     *
     * @param   object          $order   Order object.
     * @param   \Joomla\Registry\Registry  $params  Component parameters.
     *
     * @return  array  ['eligible' => bool, 'reason' => string|null, 'days_remaining' => int]
     *
     * @since  6.1.0
     */
    protected function _checkEligibility(object $order, $params): array
    {
        $deadlineDays   = max(14, (int) $params->get('cancellation_deadline_days', 14));
        $deadlineStatus = (int) $params->get('cancellation_deadline_status', 0);
        $startStatuses  = $params->get('cancellation_start_status', []);
        $excludeVat     = (int) $params->get('cancellation_exclude_vat', 1);

        // Check if already withdrawn (result status applied)
        $resultStatus = (int) $params->get('cancellation_result_status', 0);
        if ($resultStatus > 0 && (int) $order->status_id === $resultStatus) {
            return ['eligible' => false, 'reason' => 'already', 'days_remaining' => 0];
        }

        // Check start status eligibility
        if (!empty($startStatuses)) {
            if (is_string($startStatuses)) {
                $allowedIds = array_filter(array_map('intval', explode(',', $startStatuses)));
            } else {
                $allowedIds = array_filter(array_map('intval', (array) $startStatuses));
            }
            if (!empty($allowedIds) && !in_array(-1, $allowedIds, true) && !in_array((int) $order->status_id, $allowedIds, true)) {
                return ['eligible' => false, 'reason' => 'status', 'days_remaining' => 0];
            }
        }

        // Exclude VAT / B2B orders
        if ($excludeVat && !empty($order->has_vat)) {
            return ['eligible' => false, 'reason' => 'vat', 'days_remaining' => 0];
        }

        // Check deadline
        $orderDate = strtotime($order->date);

        // Override start date if a specific status is set in configuration
        if ($deadlineStatus > 0) {
            $_statusDate = PhocacartOrder::getOrderStatusHistoryDate($order->id, $deadlineStatus);
            if ($_statusDate) {
                $orderDate = strtotime($_statusDate);
            }
        }

        $deadline  = $orderDate + ($deadlineDays * 86400);
        $now       = time();

        if ($now > $deadline) {
            return ['eligible' => false, 'reason' => 'deadline', 'days_remaining' => 0];
        }

        $daysRemaining = (int) ceil(($deadline - $now) / 86400);

        return ['eligible' => true, 'reason' => null, 'days_remaining' => $daysRemaining];
    }

    /**
     * Process the withdrawal POST request.
     * Validates CSRF, re-checks eligibility, updates the order status, and sends emails.
     *
     * @param   int     $orderId  Order ID.
     * @param   string  $token    Guest order token.
     *
     * @return  array  ['success' => bool, 'message' => string]
     *
     * @since  6.1.0
     */
    public function cancel(int $orderId, string $token): array
    {
        $app    = Factory::getApplication();
        $params = $app->getParams();

        // Feature gate
        if (!(int) $params->get('cancellation_enable', 0)) {
            return ['success' => false, 'message' => Text::_('COM_PHOCACART_CANCELLATION_ERROR_DISABLED')];
        }

        // Load & re-verify order ownership
        $order = $this->_loadOrder($orderId, $token);
        if (!$order) {
            return ['success' => false, 'message' => Text::_('COM_PHOCACART_CANCELLATION_ERROR_NOT_FOUND')];
        }

        // Re-check eligibility (race-condition protection)
        $eligibility = $this->_checkEligibility($order, $params);
        if (!$eligibility['eligible']) {
            $msgKey = match ($eligibility['reason']) {
                'deadline' => 'COM_PHOCACART_CANCELLATION_ERROR_DEADLINE',
                'status'   => 'COM_PHOCACART_CANCELLATION_ERROR_STATUS',
                'vat'      => 'COM_PHOCACART_CANCELLATION_ERROR_VAT',
                'already'  => 'COM_PHOCACART_CANCELLATION_ERROR_ALREADY',
                default    => 'COM_PHOCACART_CANCELLATION_ERROR_NOT_FOUND',
            };
            return ['success' => false, 'message' => Text::_($msgKey)];
        }

        // Update order status if a result status is configured
        $resultStatus = (int) $params->get('cancellation_result_status', 0);
        if ($resultStatus > 0 && (int)$order->status_id !== $resultStatus) {
            // 1. Change status in order table (changeStatus method only handles actions/notifications)
            \PhocacartOrderStatus::changeStatusInOrderTable($orderId, $resultStatus);

            // 2. Process status change actions (stock, points, user group, etc.)
            // notifyUser and notifyOthers are 0 because we send custom withdrawal emails below.
            $notified = \PhocacartOrderStatus::changeStatus($orderId, $resultStatus, $token, 0, 0);

            // 3. Add history record
            $historyComment = Text::_('COM_PHOCACART_CANCELLATION_BY_USER_WITHIN_PERIOD');
            \PhocacartOrderStatus::setHistory($orderId, $resultStatus, $notified, $historyComment);
        }

        // Send e-mails
        $this->_sendEmails($order, $params);

        // Log the action
        PhocacartLog::add(1, 'Cancellation - Right of Withdrawal', $orderId,
            'Customer submitted withdrawal notice for order ID: ' . $orderId);

        return ['success' => true, 'message' => Text::_('COM_PHOCACART_CANCELLATION_SUCCESS')];
    }

    /**
     * Send withdrawal confirmation e-mail to customer and notification to admin.
     *
     * @param   object  $order   Order object.
     * @param   object  $params  Component parameters.
     *
     * @since  6.1.0
     */
    protected function _sendEmails(object $order, $params): void
    {
        $app         = Factory::getApplication();
        $config      = Factory::getConfig();
        $siteUrl     = \Joomla\CMS\Uri\Uri::root();
        $siteName    = $config->get('sitename');
        $orderNumber = PhocacartOrder::getOrderNumber($order->id, $order->date, $order->order_number);
        $orderDate   = HTMLHelper::date($order->date, Text::_('DATE_FORMAT_LC2'));
        $now         = HTMLHelper::date('now', Text::_('DATE_FORMAT_LC2'));
        
        $firstName   = $order->cancellation->name_first ?? '';
        $lastName    = $order->cancellation->name_last ?? '';
        $custName    = htmlspecialchars(trim($firstName . ' ' . $lastName), ENT_QUOTES, 'UTF-8');
        $custEmail   = htmlspecialchars($order->cancellation->email ?? '', ENT_QUOTES, 'UTF-8');
        
        $userLang    = $order->user_lang ?? $app->getLanguage()->getTag();
        $defaultLang = $order->default_lang ?? $app->getLanguage()->getTag();

        $mailData = [
            'ordernumber'     => $orderNumber,
            'orderdate'       => $orderDate,
            'customer_name'   => $custName,
            'customer_email'  => $custEmail,
            'withdrawal_date' => $now,
            'site_name'       => $siteName,
            'site_url'        => $siteUrl,
            // Standard MailTemplate tags (uppercase)
            'ORDERNUMBER'     => $orderNumber,
            'ORDERDATE'       => $orderDate,
            'CUSTOMER_NAME'   => $custName,
            'CUSTOMER_EMAIL'  => $custEmail,
            'WITHDRAWAL_DATE' => $now,
            'SITE_NAME'       => $siteName,
            'SITE_URL'        => $siteUrl,
        ];

        // --- Customer mail ---
        if (JoomlaMailHelper::isEmailAddress($custEmail)) {
            try {
                $mailData['html.document'] = \Phoca\PhocaCart\Mail\MailHelper::renderBody('cancellation', 'html', [], $mailData);
                $mailData['text.document'] = \Phoca\PhocaCart\Mail\MailHelper::renderBody('cancellation', 'text', [], $mailData);

                $mailer = new MailTemplate('com_phocacart.order_cancellation', $userLang);
                $mailer->addTemplateData($mailData);
                $mailer->addRecipient($custEmail);
                $mailer->send();
            } catch (\Exception $e) {
                PhocacartLog::add(2, 'Cancellation - Email ERROR (customer)', $order->id, $e->getMessage());
            }
        }

        // --- Admin notification ---
        $adminEmail = trim((string) $params->get('cancellation_admin_email', ''));
        if ($adminEmail === '') {
            $adminEmail = $config->get('mailfrom', '');
        }

        $adminRecipients = array_filter(array_map('trim', explode(',', $adminEmail)));
        if (!empty($adminRecipients) && JoomlaMailHelper::isEmailAddress($adminRecipients[0])) {
            try {
                $mailData['html.document'] = \Phoca\PhocaCart\Mail\MailHelper::renderBody('cancellation_admin', 'html', [], $mailData);
                $mailData['text.document'] = \Phoca\PhocaCart\Mail\MailHelper::renderBody('cancellation_admin', 'text', [], $mailData);

                $mailer = new MailTemplate('com_phocacart.order_cancellation_admin', $defaultLang);
                $mailer->addTemplateData($mailData);
                $mailer->addRecipient(array_shift($adminRecipients));
                foreach ($adminRecipients as $bcc) {
                    if (JoomlaMailHelper::isEmailAddress($bcc)) {
                        $mailer->addRecipient($bcc, null, 'bcc');
                    }
                }
                $mailer->send();
            } catch (\Exception $e) {
                PhocacartLog::add(2, 'Cancellation - Email ERROR (admin)', $order->id, $e->getMessage());
            }
        }
    }
}
