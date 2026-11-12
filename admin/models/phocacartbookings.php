<?php
/**
 * @package    phocacart
 * @subpackage Models
 * @copyright  Copyright (C) Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

jimport('joomla.application.component.modellist');

class PhocaCartCpModelPhocaCartBookings extends ListModel
{
    protected $option = 'com_phocacart';

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'user_id', 'a.user_id',
                'product_id', 'a.product_id',
                'status', 'a.status',
                'booking_mode', 'a.booking_mode',
                'booking_date_start', 'a.booking_date_start',
                'booking_date_end', 'a.booking_date_end',
                'quantity', 'a.quantity'
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.id', $direction = 'DESC')
    {
        $app = Factory::getApplication();

        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $status = $app->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', '', 'string');
        $this->setState('filter.status', $status);

        $bookingMode = $app->getUserStateFromRequest($this->context . '.filter.booking_mode', 'filter_booking_mode', '', 'string');
        $this->setState('filter.booking_mode', $bookingMode);

        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.user_id, a.product_id, a.slot_id, a.order_id, a.booking_mode, a.status,' .
                'a.booking_date_start, a.booking_date_end, a.booking_time_start, a.booking_time_end,' .
                'a.quantity, a.checked_out, a.checked_out_time,' .
                'u.name AS user_name, u.email AS user_email, p.title AS product_title'
            )
        );
        $query->from($db->quoteName('#__phocacart_bookings', 'a'));

        // Join over the users for the user name.
        $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.user_id'));

        // Join over the products for the product title.
        $query->join('LEFT', $db->quoteName('#__phocacart_products', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('a.product_id'));

        // Join over the orders.
        $query->select('o.date AS order_date, o.order_number as order_number');
        $query->join('LEFT', $db->quoteName('#__phocacart_orders', 'o') . ' ON ' . $db->quoteName('o.id') . ' = ' . $db->quoteName('a.order_id'));

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'));

        // Filter by status
        $status = $this->getState('filter.status');
        if (!empty($status)) {
            $query->where($db->quoteName('a.status') . ' = ' . (int)$status);
        }

        // Filter by booking mode
        $mode = $this->getState('filter.booking_mode');
        if (!empty($mode)) {
            $query->where($db->quoteName('a.booking_mode') . ' = ' . (int)$mode);
        }

        // Filter by search
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $searchVal = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(u.name LIKE ' . $searchVal . ' OR p.title LIKE ' . $searchVal . ' OR u.email LIKE ' . $searchVal . ')');
            }
        }

        // Add ordering clause
        $orderCol  = $this->state->get('list.ordering', 'a.id');
        $orderDirn = $this->state->get('list.direction', 'desc');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    /**
     * Get all active bookings for the interactive backend calendar
     *
     * @return array
     */
    public function getCalendarBookings(): array
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select(
            'a.id, a.user_id, a.product_id, a.slot_id, a.order_id, a.booking_mode, a.status,' .
            'a.booking_date_start, a.booking_date_end, a.booking_time_start, a.booking_time_end,' .
            'a.quantity, u.name AS user_name, p.title AS product_title'
        );
        $query->from($db->quoteName('#__phocacart_bookings', 'a'));
        $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.user_id'));
        $query->join('LEFT', $db->quoteName('#__phocacart_products', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('a.product_id'));
        $query->where($db->quoteName('a.status') . ' != ' . (int)\PhocacartBooking::STATUS_CANCELLED);
        $query->where($db->quoteName('a.status') . ' != ' . (int)\PhocacartBooking::STATUS_REFUNDED);
        $query->order('a.booking_date_start ASC, a.id ASC');

        $db->setQuery($query);

        $bookings = (array)$db->loadObjectList();

        if (!empty($bookings)) {
            foreach ($bookings as $k => $v) {

                $v->order_number = PhocacartOrder::getOrderNumber($v->order_id);

            }
        }

        return $bookings;
    }
}
