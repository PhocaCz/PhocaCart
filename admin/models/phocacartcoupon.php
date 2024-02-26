<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\I18n\I18nAdminModelTrait;

class PhocaCartCpModelPhocacartCoupon extends AdminModel
{
	use I18nAdminModelTrait;

	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

    public function __construct($config = [], MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
    {
        parent::__construct($config, $factory, $formFactory);

        $this->i18nTable = '#__phocacart_coupons_i18n';
        $this->i18nFields = [
            'title',
            'alias',
            'description',
            'gift_description',
            'gift_sender_message',
        ];
    }

	public function getTable($type = 'PhocacartCoupon', $prefix = 'Table', $config = array()) {
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_phocacart.phocacartcoupon', 'phocacartcoupon', array('control' => 'jform', 'load_data' => $loadData));
        return $this->prepareI18nForm($form);
	}

	protected function loadFormData() {
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartcoupon.data', array());
		if (empty($data)) {
			$data = $this->getItem();
			$this->loadI18nItem($data);
		}
		return $data;
	}

	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {
			$item->discount		= PhocacartPrice::cleanPrice($item->discount);
			$item->total_amount	= PhocacartPrice::cleanPrice($item->total_amount);
		}
		return $item;
	}

	protected function prepareTable($table) {
		jimport('joomla.filter.output');

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= ApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = ApplicationHelper::stringURLSafe($table->title);
		}

		$table->total_amount	= PhocacartUtils::replaceCommaWithPoint($table->total_amount);
		$table->discount		= PhocacartUtils::replaceCommaWithPoint($table->discount);

		$table->quantity_from			= PhocacartUtils::getIntFromString($table->quantity_from);
		$table->available_quantity		= PhocacartUtils::getIntFromString($table->available_quantity);
		$table->available_quantity_user	= PhocacartUtils::getIntFromString($table->available_quantity_user);
		$table->valid_from 				= PhocacartUtils::getDateFromString($table->valid_from);
		$table->valid_to 				= PhocacartUtils::getDateFromString($table->valid_to);

		if (empty($table->id)) {
			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_coupons');
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
	}

	public function save($data)
	{
		$i18nData = $this->prepareI18nData($data);
		$table = $this->getTable();

		if ((!empty($data['tags']) && $data['tags'][0] != ''))
		{
			$table->newTags = $data['tags'];
		}

		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
		PluginHelper::importPlugin('content');

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}

			// Bind the data.
			if (!$table->bind($data))
			{
				$this->setError($table->getError());

				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}

			// Trigger the onContentBeforeSave event.
			$result = Dispatcher::dispatchBeforeSave($this->event_before_save, $this->option . '.' . $this->name, $table, $isNew, $data);

			if (in_array(false, $result, true))
			{
				$this->setError($table->getError());
				return false;
			}

			// Store the data.
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}

			if ((int)$table->getId() > 0) {

				if (!isset($data['product_ids'])) {
					$data['product_ids'] = '';
				}
				PhocacartCoupon::storeCouponProductsById($data['product_ids'], (int)$table->getId() );

				if (!isset($data['cat_ids'])) {
					$data['cat_ids'] = array();
				}
				PhocacartCoupon::storeCouponCatsById($data['cat_ids'], (int)$table->getId());

				if (empty($data['group'])) {
					$data['group'] = array();
				}

				PhocacartGroup::storeGroupsById((int)$table->getId(), 6, $data['group']);

				$this->saveI18nData($table->getId(), $i18nData);
			}

			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			Dispatcher::dispatchAfterSave($this->event_after_save, $this->option . '.' . $this->name, $table, $isNew, $data);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		$this->setState($this->getName() . '.new', $isNew);

		return true;
	}

	public function delete(&$pks = [])
	{
		ArrayHelper::toInteger($pks);
		if (parent::delete($pks)) {
			$cids = implode(',', $pks);

			$query = 'DELETE FROM #__phocacart_item_groups'
				. ' WHERE item_id IN ( ' . $cids . ' )'
				. ' AND type = 6';
			$this->_db->setQuery($query);
			$this->_db->execute();

			return $this->deleteI18nData($pks);
		}

		return false;
	}
}
