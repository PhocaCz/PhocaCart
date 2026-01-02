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

class PhocaCartCpModelPhocaCartSubscription extends AdminModel
{
    use AdminModelTrait;

    protected $option      = 'com_phocacart';
    protected $text_prefix = 'com_phocacart';

    public function getTable($type = 'PhocaCartSubscription', $prefix = 'Table', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm('com_phocacart.phocacartsubscription', 'phocacartsubscription', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        // Make user and product readonly and display names when editing
        $id = (int)$this->getState($this->getName() . '.id');
        if ($id > 0) {
            $form->setFieldAttribute('user_id', 'type', 'hidden');
            $form->setFieldAttribute('product_id', 'type', 'hidden');
        } else {
            // New record - don't show name fields (they should be blank/hidden anyway)
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
                ->from($db->quoteName('#__phocacart_subscriptions', 'a'))
                ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('a.user_id'))
                ->join('LEFT', $db->quoteName('#__phocacart_products', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('a.product_id'))
                ->where($db->quoteName('a.id') . ' = ' . (int) $item->id);

            $db->setQuery($query);
            $names = $db->loadObject();

            if ($names) {
                $item->user_name = $names->user_name;
                $item->product_title = $names->product_title;
            }
        }

        return $item;
    }

    public function getOrderInfo($id) {
        if ((int)$id <= 0) {
            return [];
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('os.*')
            ->from($db->quoteName('#__phocacart_order_subscriptions', 'os'))
            ->join('LEFT', $db->quoteName('#__phocacart_subscriptions', 's') . ' ON ' . $db->quoteName('os.id') . ' = ' . $db->quoteName('s.order_item_id'))
            ->where($db->quoteName('s.id') . ' = ' . (int)$id)
            ->order($db->quoteName('os.id') . ' DESC');

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
            ->from($db->quoteName('#__phocacart_subscription_history'))
            ->where($db->quoteName('subscription_id') . ' = ' . (int)$id)
            ->order($db->quoteName('event_date') . ' DESC');

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getACL($id)
    {
        if ((int)$id <= 0) {
            return [];
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('a.*, g.title AS group_title')
            ->from($db->quoteName('#__phocacart_subscription_acl', 'a'))
            ->join('LEFT', $db->quoteName('#__usergroups', 'g') . ' ON ' . $db->quoteName('g.id') . ' = ' . $db->quoteName('a.group_id'))
            ->where($db->quoteName('a.subscription_id') . ' = ' . (int)$id)
            ->order($db->quoteName('a.applied_date') . ' DESC');

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    protected function loadFormData()
    {
        $data = \Joomla\CMS\Factory::getApplication()->getUserState('com_phocacart.edit.phocacartsubscription.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function save($data)
    {
        $id = (int)($data['id'] ?? 0);
        $statusOld = null;

        // Load the current item from the database before it is updated to capture the previous status
        if ($id > 0) {
            $item = $this->getItem($id);
            $statusOld = $item ? (int)$item->status : null;
        }

        // Execute the standard Joomla save process
        if (parent::save($data)) {
            // Retrieve the updated record (important for new records to get the ID)
            $id = $id ?: (int)$this->getState($this->getName() . '.id');
            $newItem = $this->getItem($id);

            // Directly get the dispatcher and trigger the custom event for the system plugin
            $dispatcher = Factory::getContainer()->get(\Joomla\Event\DispatcherInterface::class);
            $event = new Event('onPhocaCartAfterSubscriptionSave', [
                'item'      => $newItem,
                'statusOld' => $statusOld,
                'data'      => $data
            ]);
            $dispatcher->dispatch('onPhocaCartAfterSubscriptionSave', $event);

            return true;
        }

        return false;
    }

     public function delete(&$pks)
    {
        $pks = (array) $pks;
        $db  = $this->getDbo();

        if (empty($pks)) {
            return true;
        }

        $dispatcher = Factory::getContainer()->get(\Joomla\Event\DispatcherInterface::class);

        foreach ($pks as $pk) {
            $item = $this->getItem($pk);
            if ($item) {
                // Trigger event before delete to handle ACL removal in plugin
                $event = new Event('onPhocaCartBeforeSubscriptionDelete', [
                    'item' => $item
                ]);
                $dispatcher->dispatch('onPhocaCartBeforeSubscriptionDelete', $event);
            }
        }

        // 1. Delete History Logs first
        // We do this first because history depends on the existence of the subscription ID
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__phocacart_subscription_history'))
            ->where($db->quoteName('subscription_id') . ' IN (' . implode(',', $pks) . ')');
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // 2. Delete ACL Logs
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__phocacart_subscription_acl'))
            ->where($db->quoteName('subscription_id') . ' IN (' . implode(',', $pks) . ')');
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // 3. Perform standard deletion of the main subscription records
        // If this returns false, the items will stay in the list
        $result = parent::delete($pks);
        return $result;
    }
}
