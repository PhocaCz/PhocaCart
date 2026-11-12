<?php
/**
 * @package    phocacart
 * @subpackage Models
 * @copyright  Copyright (C) Jan Pavelka www.phoca.cz
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Phoca\PhocaCart\MVC\Model\AdminModelTrait;
use Joomla\CMS\Factory;
use Joomla\Event\Event;

class PhocaCartCpModelPhocaCartBooking extends AdminModel
{
    use AdminModelTrait;

    protected $option      = 'com_phocacart';
    protected $text_prefix = 'com_phocacart';

    public function getTable($type = 'PhocaCartBooking', $prefix = 'Table', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_phocacart.phocacartbooking', 'phocacartbooking', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        $id = (int)$this->getState($this->getName() . '.id');
        if ($id > 0) {
            $form->setFieldAttribute('user_id', 'type', 'hidden');
            $form->setFieldAttribute('product_id', 'type', 'hidden');
        } else {
            $form->removeField('user_name');
            $form->removeField('product_title');
        }

        return $form;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item && !empty($item->id)) {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('u.name AS user_name, p.title AS product_title')
                ->from($db->quoteName('#__phocacart_bookings', 'a'))
                ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.user_id'))
                ->join('LEFT', $db->quoteName('#__phocacart_products', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('a.product_id'))
                ->where($db->quoteName('a.id') . ' = ' . (int)$item->id);

            $db->setQuery($query);
            $names = $db->loadObject();

            if ($names) {
                $item->user_name     = $names->user_name;
                $item->product_title = $names->product_title;
            }
        }

        return $item;
    }

    public function getOrderInfo($id)
    {
        if ((int)$id <= 0) {
            return null;
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('ob.*')
            ->from($db->quoteName('#__phocacart_order_bookings', 'ob'))
            ->join('LEFT', $db->quoteName('#__phocacart_bookings', 'b') . ' ON ' . $db->quoteName('ob.order_product_id') . ' = ' . $db->quoteName('b.order_item_id') . ' AND ' . $db->quoteName('ob.order_id') . ' = ' . $db->quoteName('b.order_id'))
            ->where($db->quoteName('b.id') . ' = ' . (int)$id)
            ->order($db->quoteName('ob.id') . ' DESC');

        $db->setQuery($query);
        return $db->loadObject();
    }

    public function getHistory($id)
    {
        if ((int)$id <= 0) {
            return [];
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__phocacart_booking_history'))
            ->where($db->quoteName('booking_id') . ' = ' . (int)$id)
            ->order($db->quoteName('event_date') . ' DESC');

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartbooking.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function save($data)
    {
        $id        = (int)($data['id'] ?? 0);
        $statusOld = null;

        if ($id > 0) {
            $item      = $this->getItem($id);
            $statusOld = $item ? (int)$item->status : null;
        }

        $bookingMode = (int)($data['booking_mode'] ?? 1);
        if ($bookingMode === 2) {
            // Date Range mode does not use slot_id
            $data['slot_id'] = null;
        } else {
            // Time Slot mode
            $slotId = !empty($data['slot_id']) ? (int)$data['slot_id'] : 0;
            // If slot_id is empty, auto-resolve from product_id, slot_date, and time_start
            if ($slotId <= 0 && !empty($data['product_id']) && !empty($data['booking_date_start'])) {
                $db = $this->getDbo();
                $q = $db->getQuery(true)
                    ->select('id')
                    ->from($db->quoteName('#__phocacart_booking_slots'))
                    ->where($db->quoteName('product_id') . ' = ' . (int)$data['product_id'])
                    ->where($db->quoteName('slot_date') . ' = ' . $db->quote($data['booking_date_start']));
                if (!empty($data['booking_time_start'])) {
                    $q->where($db->quoteName('slot_time_start') . ' = ' . $db->quote($data['booking_time_start']));
                }
                $db->setQuery($q, 0, 1);
                $foundId = (int)$db->loadResult();
                if ($foundId > 0) {
                    $slotId = $foundId;
                }
            }
            $data['slot_id'] = $slotId > 0 ? $slotId : null;
        }

        if (parent::save($data)) {
            $id = $id ?: (int)$this->getState($this->getName() . '.id');
            $newItem = $this->getItem($id);

            // Log history if status changed
            $statusNew = (int)($newItem->status ?? 0);
            if ($statusOld !== null && $statusOld !== $statusNew) {
                \PhocacartBooking::logHistory(
                    $id,
                    $statusNew,
                    \PhocacartBooking::EVENT_STATUS_CHANGED,
                    'admin',
                    $statusOld,
                    0,
                    $data['notes'] ?? null
                );
            }

            return true;
        }

        return false;
    }

    public function delete(&$pks)
    {
        $pks = (array)$pks;
        $db  = $this->getDbo();

        if (empty($pks)) {
            return true;
        }

        // 1. Delete history logs
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__phocacart_booking_history'))
            ->where($db->quoteName('booking_id') . ' IN (' . implode(',', $pks) . ')');
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // 2. Perform deletion of bookings
        return parent::delete($pks);
    }
}
