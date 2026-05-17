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
use Joomla\Component\PhocaCart\Administrator\Helper\SubscriptionHelper;

jimport('joomla.application.component.modellist');

class PhocaCartCpModelPhocaCartSubscriptions extends ListModel
{
    protected $option   = 'com_phocacart';

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'user_id', 'a.user_id',
                'product_id', 'a.product_id',
                'status', 'a.status',
                'start_date', 'a.start_date',
                'end_date', 'a.end_date'
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.id', $direction = 'DESC')
    {
        $app = Factory::getApplication();

        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $state = $app->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', '', 'string');
        $this->setState('filter.status', $state);

        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.user_id, a.product_id, a.status,' .
                'a.start_date, a.end_date, a.checked_out, a.checked_out_time,' .
                'u.name AS user_name, u.email AS user_email, p.title AS product_title'
            )
        );
        $query->from($db->quoteName('#__phocacart_subscriptions', 'a'));

        // Join over the users for the user name.
        $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.user_id'));

        // Join over the products for the product title.
        $query->join('LEFT', $db->quoteName('#__phocacart_products', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('a.product_id'));

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor');
        $query->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'));

        // Filter by status
        $status = $this->getState('filter.status');

        if (!empty($status)) {
            $query->where($db->quoteName('a.status') . ' = ' . $db->quote($status));
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(u.name LIKE ' . $search . ' OR p.title LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.id');
        $orderDirn = $this->state->get('list.direction', 'desc');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}
