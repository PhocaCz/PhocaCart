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

class PhocaCartCpModelPhocaCartBookingSlots extends ListModel
{
    protected $option = 'com_phocacart';

    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'product_id', 'a.product_id',
                'slot_date', 'a.slot_date',
                'slot_time_start', 'a.slot_time_start',
                'slot_time_end', 'a.slot_time_end',
                'capacity', 'a.capacity',
                'booked', 'a.booked',
                'published', 'a.published'
            );
        }

        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.slot_date', $direction = 'DESC')
    {
        $app = Factory::getApplication();

        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $app->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        parent::populateState($ordering, $direction);
    }

    protected function getListQuery()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.product_id, a.slot_date, a.slot_time_start, a.slot_time_end,' .
                'a.capacity, a.booked, a.published, a.ordering,' .
                'p.title AS product_title'
            )
        );
        $query->from($db->quoteName('#__phocacart_booking_slots', 'a'));
        $query->join('LEFT', $db->quoteName('#__phocacart_products', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('a.product_id'));

        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where($db->quoteName('a.published') . ' = ' . (int)$published);
        }

        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $searchVal = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(p.title LIKE ' . $searchVal . ' OR a.slot_date LIKE ' . $searchVal . ')');
            }
        }

        $orderCol  = $this->state->get('list.ordering', 'a.slot_date');
        $orderDirn = $this->state->get('list.direction', 'desc');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }
}
