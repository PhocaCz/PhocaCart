<?php
/**
 * @package    phocacart
 * @subpackage Library
 * @copyright  Copyright (C) Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

class PhocacartSubscription
{
    const STATUS_ACTIVE       = 1;
    const STATUS_FUTURE       = 2;
    const STATUS_EXPIRED      = 3;
    const STATUS_ON_HOLD      = 4;
    const STATUS_PENDING      = 5;
    const STATUS_FAILED       = 6;
    const STATUS_IN_TRIAL     = 7;
    const STATUS_CARD_EXPIRED = 8;
    const STATUS_CANCELED     = 9;

    const BEHAVIOR_NEUTRAL   = 0;
    const BEHAVIOR_ACTIVE    = 1;
    const BEHAVIOR_INACTIVE  = 2;

    const EVENT_CREATED = 'created';
    const EVENT_ACTIVATED = 'activated';
    const EVENT_RENEWED = 'renewed';
    const EVENT_EXPIRING_SOON = 'expiring_soon';
    const EVENT_EXPIRED = 'expired';
    const EVENT_CANCELED = 'canceled';
    const EVENT_TRIAL_ACTIAVTED = 'trial_activated';
    const EVENT_STATUS_CHANGED = 'status_changed';
    const EVENT_DEACTIVATED = 'deactivated';


    public static function getEvents()
    {
        return array(
            self::EVENT_CREATED         => 'created',
            self::EVENT_ACTIVATED       => 'activated',
            self::EVENT_RENEWED         => 'renewed',
            self::EVENT_EXPIRING_SOON   => 'expiring_soon',
            self::EVENT_EXPIRED         => 'expired',
            self::EVENT_CANCELED        => 'canceled',
            self::EVENT_TRIAL_ACTIAVTED => 'trial_activated',
            self::EVENT_STATUS_CHANGED  => 'status_changed',
            self::EVENT_DEACTIVATED     => 'deactivated'
        );
    }

    /**
     * Get the statuses as an array (ID => Language String)
     *
     * @return array
     */
    public static function getStatuses()
    {
        return array(
            self::STATUS_ACTIVE       => 'COM_PHOCACART_SUBSCRIPTION_STATUS_ACTIVE',
            self::STATUS_FUTURE       => 'COM_PHOCACART_SUBSCRIPTION_STATUS_FUTURE',
            self::STATUS_EXPIRED      => 'COM_PHOCACART_SUBSCRIPTION_STATUS_EXPIRED',
            self::STATUS_ON_HOLD      => 'COM_PHOCACART_SUBSCRIPTION_STATUS_ON_HOLD',
            self::STATUS_PENDING      => 'COM_PHOCACART_SUBSCRIPTION_STATUS_PENDING',
            self::STATUS_FAILED       => 'COM_PHOCACART_SUBSCRIPTION_STATUS_FAILED',
            self::STATUS_IN_TRIAL     => 'COM_PHOCACART_SUBSCRIPTION_STATUS_IN_TRIAL',
            self::STATUS_CARD_EXPIRED => 'COM_PHOCACART_SUBSCRIPTION_STATUS_CARD_EXPIRED',
            self::STATUS_CANCELED     => 'COM_PHOCACART_SUBSCRIPTION_STATUS_CANCELED'
        );
    }

    public static function getStatusStyles()
    {
        return array(
            self::STATUS_ACTIVE       => 'status-active',
            self::STATUS_FUTURE       => 'status-future',
            self::STATUS_EXPIRED      => 'status-expired',
            self::STATUS_ON_HOLD      => 'status-on-hold',
            self::STATUS_PENDING      => 'status-pending',
            self::STATUS_FAILED       => 'status-failed',
            self::STATUS_IN_TRIAL     => 'status-in-trial',
            self::STATUS_CARD_EXPIRED => 'status-card-expired',
            self::STATUS_CANCELED     => 'status-canceled'
        );
    }

    public static function getUnits() {
        return array(
            1 => 'DAY',
            2 => 'WEEK',
            3 => 'MONTH',
            4 => 'YEAR');
    }

    public static function getUnit($unit){
        $units = self::getUnits();
        if (isset($units[(int)$unit])) {
            return $units[(int)$unit];
        }
        return '';
    }

   /* public static function  getUnitLangSuffix($period) {

        if ((int)$period == 1) {
            return 1;// 1 Months
        }
        if ((int)$period < 1) {
            return 2; // 0 Months
        }
        if ((int)$period > 1) {
            return 2; // 2 Months
        }

        // Possible to do for languages with 3 or more varians

        return 1;
    }*/

    /**
     * Get systemic properties for a status
     *
     * @param int $id Status ID
     * @return array Status properties
     */
    public static function getStatusProperties($id)
    {
        $p = array(
            'behavior' => self::BEHAVIOR_NEUTRAL
        );

        switch ((int)$id) {
            case self::STATUS_ACTIVE:
            case self::STATUS_IN_TRIAL:
                $p['behavior'] = self::BEHAVIOR_ACTIVE;
                break;

            case self::STATUS_EXPIRED:
            case self::STATUS_ON_HOLD:
            case self::STATUS_CANCELED:
            case self::STATUS_FAILED:
            case self::STATUS_CARD_EXPIRED:
                $p['behavior'] = self::BEHAVIOR_INACTIVE;
                break;

            case self::STATUS_PENDING:
            case self::STATUS_FUTURE:
            default:
                $p['behavior'] = self::BEHAVIOR_NEUTRAL;
                break;
        }

        return $p;
    }

    /**
     * Get the language string for a specific status ID
     *
     * @param int $id Status ID
     * @return string Language string
     */
    public static function getStatus($id)
    {
        $statuses = self::getStatuses();
        if (isset($statuses[(int)$id])) {
            return $statuses[(int)$id];
        }
        return '';
    }

    public static function getStatusStyle($id)
    {
        $statuses = self::getStatusStyles();
        if (isset($statuses[(int)$id])) {
            return $statuses[(int)$id];
        }
        return '';
    }

    /**
     * @param   array  $t   Task variables
     * @param   int    $id  Item ID
     *
     * @return  \Joomla\CMS\Object\CMSObject
     */
   /* public static function getActions($t, $id = 0)
    {
        $user   = Factory::getUser();
        $result = new \Joomla\CMS\Object\CMSObject;

        if (empty($id)) {
            $assetName = $t['o'];
        } else {
            $assetName = $t['o'] . '.' . $t['tasks'] . '.' . (int) $id;
        }

        $actions = array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete');

        foreach ($actions as $action) {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }*/

    /**
     * Calculate the new end date for a subscription based on start date, period and unit.
     *
     * @param   string|Date  $startDate  The start date (or date to add to)
     * @param   int          $period     Number of units
     * @param   string       $unit       Day, Week, Month, Year
     *
     * @return  Date
     */
    public static function calculateEndDate($startDate, int $period, string $unit): Date
    {
        $date = new Date($startDate);

        switch ((int)$unit) {
            case 1: // Day
                $interval = "P{$period}D";
                break;
            case 2: // Week
                $days = $period * 7;
                $interval = "P{$days}D";
                break;
            case 3: // Month
                $interval = "P{$period}M";
                break;
            case 4: // Year
                $interval = "P{$period}Y";
                break;
            default:
                return $date;
        }

        $date->add(new \DateInterval($interval));
        return $date;
    }

    /**
     * Determine the subscription scenario for a user and product.
     *
     * @param   int  $userId     User ID
     * @param   int  $itemId  Product ID
     * @param   object $item  The product object with subscription fields
     *
     * @return  array  Scenario details
     */
    public static function determineSubscriptionScenario($order, $item): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Find existing subscription (must be the same product ID)
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__phocacart_subscriptions'))
            ->where($db->quoteName('user_id') . ' = ' . (int) $order->user_id)
            ->where($db->quoteName('product_id') . ' = ' . (int) $item->product_id)
            ->order($db->quoteName('id') . ' DESC')
            ->setLimit(1);

        $db->setQuery($query);
        $existing = $db->loadObject();


        $now = new Date();

        // Get renewal mode from plugin params
        $plugin = PluginHelper::getPlugin('system', 'phocacartsubscription');
        $params = new Registry($plugin->params ?? '');
        $renewalMode = (int)$params->get('subscription_renewal_mode', 1);

        // Handle both product data (->price) and order subscription data (->subscription_order_base_price)
        $basePrice = 0.0;
        if (isset($item->subscription_order_base_price)) {
            $basePrice = (float)$item->subscription_order_base_price;
        } elseif (isset($item->price)) {
            $basePrice = (float)$item->price;
        }

        $totalPrice = 0.0;
        if (isset($item->subscription_order_total_price)) {
            $totalPrice = (float)$item->subscription_order_total_price;
        } else {
            $totalPrice = $basePrice;
        }

        $defaults = [
            'base_price' => $basePrice,
            'signup_fee' => 0.0,
            'renewal_discount' => 0.0,
            'total_price' => $totalPrice,
            'start_date' => $now->toSql(),
            'end_date' => $now->toSql(),
            'start_date_default' => $now->toSql(),// not manipulated by options
            'end_date_default' => $now->toSql(),// not manipulated by options
            'type' => 'new',
            'period' => 0,
            'unit' => 0
        ];

        // Scenario 1: New Subscription
        if (!$existing) {
            $signupFee = (float)($item->subscription_signup_fee ?? 0);
            $scenario = $defaults;
            $scenario['type'] = 'new';
            $scenario['signup_fee'] = $signupFee;
            $scenario['total_price'] = $scenario['base_price'] + $signupFee;
            $scenario['end_date'] = self::calculateEndDate($now, (int)$item->subscription_period, (string)$item->subscription_unit)->toSql();
            $scenario['end_date_default'] = $scenario['end_date'];
            $scenario['period'] = $item->subscription_period;
            $scenario['unit'] = $item->subscription_unit;


            return $scenario;
        }

        // Existing subscription found
        $endDate = new Date($existing->end_date);
        $props = \PhocacartSubscription::getStatusProperties($existing->status);

        // Is currently active (by status behavior AND time)
        $isActive = ($props['behavior'] === \PhocacartSubscription::BEHAVIOR_ACTIVE && $endDate > $now);


        // Calculate Renewal Discount
        $discount = 0.0;
        if ((float)$item->subscription_renewal_discount > 0) {
            if ((int)$item->subscription_renewal_discount_calculation_type === 1) { // 1 = percentage
                $discount = ($defaults['base_price'] * $item->subscription_renewal_discount) / 100;
            } else {
                $discount = (float)$item->subscription_renewal_discount;
            }
        }
        $discount = min($discount, $defaults['base_price']);

        // Scenario 2: Active Renewal (Early)
        if ($isActive) {
            $scenario = $defaults;
            $scenario['type'] = 'renewal_active';
            $scenario['renewal_discount'] = $discount;
            $scenario['total_price'] = max(0, $scenario['base_price'] - $discount);

            if ($renewalMode === 1) {
                // APPEND: Start from existing end date
                $scenario['start_date'] = $existing->start_date; // Keep original start
                $scenario['end_date'] = self::calculateEndDate($endDate, (int)$item->subscription_period, (string)$item->subscription_unit)->toSql();
                $scenario['start_date_default'] = $existing->start_date;
                $scenario['end_date_default'] =  $existing->end_date;
            } else {
                // FROM NOW: Overwrite current period
                $scenario['start_date'] = $now->toSql();
                $scenario['end_date'] = self::calculateEndDate($now, (int)$item->subscription_period, (string)$item->subscription_unit)->toSql();
                $scenario['start_date_default'] = $scenario['start_date'];
                $scenario['end_date_default'] = $scenario['end_date'];
            }

            $scenario['period'] = $item->subscription_period;
            $scenario['unit'] = $item->subscription_unit;

            return $scenario;
        }

        // Scenario 3: Expired Renewal

        // If the subscription was manually set to expired (3) OR any other inactive status (4, 6, 8, 9)
        // we should use the date from history as end date e.g. for calculation of grace period
        if ($props['behavior'] === self::BEHAVIOR_INACTIVE) {
            $query = $db->getQuery(true)
                ->select($db->quoteName('event_date'))
                ->from($db->quoteName('#__phocacart_subscription_history'))
                ->where($db->quoteName('subscription_id') . ' = ' . (int)$existing->id)
                ->where($db->quoteName('status_to') . ' = ' . (int)$existing->status)
                ->order($db->quoteName('id') . ' DESC')
                ->setLimit(1);

            $db->setQuery($query);
            $historyDate = $db->loadResult();

            if ($historyDate) {
                $endDate = new Date($historyDate);
                $manualDateFound = true;
            }
        }
        $gracePeriodDays = (int)($item->subscription_grace_period_days ?? 0);
        $graceEnd = clone $endDate;
        if ($gracePeriodDays > 0) {
            $graceEnd->add(new \DateInterval("P{$gracePeriodDays}D"));
        }

        // Charge signup fee if outside grace period AND plugin is configured to charge it
        $chargeSignupFeeGlobally = (int)$params->get('charge_expired_signup_fee', 1);
        $chargeSignupFee = ($now->getTimestamp() > $graceEnd->getTimestamp() && $chargeSignupFeeGlobally === 1);

        // If manual date was found (meaning manual expiration or similar) and we are out of grace period, remove discount
        if (isset($manualDateFound) && $manualDateFound && $now->getTimestamp() > $graceEnd->getTimestamp()) {
            $discount = 0.0;
        }

        $scenario = $defaults;
        $scenario['type'] = 'renewal_expired';
        $scenario['renewal_discount'] = $discount;
        $scenario['signup_fee'] = $chargeSignupFee ? (float)($item->subscription_signup_fee ?? 0) : 0.0;
        $scenario['total_price'] = max(0, $scenario['base_price'] + $scenario['signup_fee'] - $discount);

        // Expired always starts NOW
        $scenario['start_date'] = $now->toSql();
        $scenario['end_date'] = self::calculateEndDate($now, (int)$item->subscription_period, (string)$item->subscription_unit)->toSql();
        $scenario['start_date_default'] = $scenario['start_date'];
        $scenario['end_date_default'] = $scenario['end_date'];

        $scenario['period'] = $item->subscription_period;
        $scenario['unit'] = $item->subscription_unit;
        return $scenario;
    }

    public static function logHistory($subscriptionId, $statusNew, $eventType, $triggeredBy = 'system', $statusOld = null, $notifyUser = 0) {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $history = new \stdClass();
        $history->subscription_id = $subscriptionId;
        $history->status_from     = $statusOld;
        $history->status_to       = $statusNew;
        $history->event_type      = $eventType;
        $history->event_date      = Factory::getDate()->toSql();
        $history->triggered_by    = $triggeredBy;
        $history->notify_user     = $notifyUser;

        try {
            $db->insertObject('#__phocacart_subscription_history', $history);
        } catch (\Exception $e) {
            PhocacartLog::add(2, 'Subscription Log History - Failed', $subscriptionId, 'Failed to log subscription history: Subscription ID: ' . $subscriptionId. ', triggered by: '.$triggeredBy );
        }
    }

    public static function logACL($subscriptionId, $userId, $groupId, $action)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $acl = new \stdClass();
        $acl->subscription_id = $subscriptionId;
        $acl->group_id        = $groupId;
        $acl->action          = $action;
        $acl->applied_date    = Factory::getDate()->toSql();
        try {
            $db->insertObject('#__phocacart_subscription_acl', $acl);
        } catch (\Exception $e) {
            PhocacartLog::add(2, 'Subscription Log ACL - Failed', $subscriptionId, 'Failed to log subscription ACL: Subscription ID: ' . $subscriptionId. ', action: '.$action );
        }
    }

    /**
     * Get list of subscriptions for a user
     *
     * @param   int    $userId  User ID
     *
     * @return  array  List of subscription objects
     */
    public static function getSubscriptions($userId)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('a.*, p.title AS product_title, p.alias AS product_alias, p.catid, c.alias as category_alias')
            ->from($db->quoteName('#__phocacart_subscriptions', 'a'))
            ->join('LEFT', $db->quoteName('#__phocacart_products', 'p') . ' ON a.product_id = p.id')
            ->join('LEFT', $db->quoteName('#__phocacart_categories', 'c') . ' ON p.catid = c.id')
            ->where('a.user_id = ' . (int)$userId)
            ->order('a.id DESC');

        $db->setQuery($query);
        return $db->loadObjectList();
    }
}
