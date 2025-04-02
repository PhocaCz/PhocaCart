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
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\I18n\I18nAdminModelTrait;

jimport('joomla.application.component.modeladmin');

class PhocaCartCpModelPhocacartPayment extends AdminModel
{
	use I18nAdminModelTrait;

	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	public function __construct($config = [], MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{
		parent::__construct($config, $factory, $formFactory);

		$this->i18nTable = '#__phocacart_payment_methods_i18n';
		$this->i18nFields = [
			'title',
			'alias',
			'description',
			'description_info',
		];
	}

	public function getTable($type = 'PhocacartPayment', $prefix = 'Table', $config = array()) {
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_phocacart.phocacartpayment', 'phocacartpayment', array('control' => 'jform', 'load_data' => $loadData));
		return $this->prepareI18nForm($form);
	}

	protected function loadFormData() {
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartpayment.data', array());
		if (empty($data)) {
			$data = $this->getItem();
			$this->loadI18nItem($data);
			$price = new PhocacartPrice();
			$data->cost 			= $price->cleanPrice($data->cost);
			$data->cost_additional 	= $price->cleanPrice($data->cost_additional);
		}
		return $data;
	}

	protected function prepareTable($table) {
		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= ApplicationHelper::stringURLSafe($table->alias);

		$table->cost 			= PhocacartUtils::replaceCommaWithPoint($table->cost);
		$table->cost_additional	= PhocacartUtils::replaceCommaWithPoint($table->cost_additional);
		$table->lowest_amount 	= PhocacartUtils::replaceCommaWithPoint($table->lowest_amount);
		$table->highest_amount 	= PhocacartUtils::replaceCommaWithPoint($table->highest_amount);

		if (empty($table->alias)) {
			$table->alias = ApplicationHelper::stringURLSafe($table->title);
		}

		$table->tax_id 	= PhocacartUtils::getIntFromString($table->tax_id);

		if (empty($table->id)) {
			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_payment_methods');
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
	}

	public function save($data)
	{
		//$dispatcher = J EventDispatcher::getInstance();
		$table = $this->getTable();

		if ((!empty($data['tags']) && $data['tags'][0] != ''))
		{
			$table->newTags = $data['tags'];
		}

		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;
		$i18nData = $this->prepareI18nData($data);

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

			// Plugin parameters are converted to params column in payment table (x001)
			// Store form parameters of selected method
			$app		= Factory::getApplication();
			$dataPh		= $app->input->get('phform', array(), 'array');
			if (!empty($dataPh['params'])) {
				$registry 	= new Registry($dataPh['params']);
				//$registry 	= new JRegistry($dataPh);
				$dataPhNew 	= $registry->toString();
				if($dataPhNew != '') {
					$data['params'] = $dataPhNew;
				}
			} else {
				$data['params'] = '';
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


			if ((int)$table->id > 0) {

				if (!isset($data['zone'])) {
					$data['zone'] = array();
				}

				PhocacartZone::storeZones($data['zone'], (int)$table->id, 'payment');


				if (!isset($data['country'])) {
					$data['country'] = array();
				}

				PhocacartCountry::storeCountries($data['country'], (int)$table->id, 'payment');

				if (!isset($data['region'])) {
					$data['region'] = array();
				}

				PhocacartRegion::storeRegions($data['region'], (int)$table->id, 'payment');

				if (!isset($data['shipping'])) {
					$data['shipping'] = array();
				}

				PhocacartShipping::storeShippingMethods($data['shipping'], (int)$table->id, 'payment');

				if (!isset($data['group'])) {
					$data['group'] = array();
				}

				PhocacartGroup::storeGroupsById((int)$table->id, 8, $data['group']);

				if (!isset($data['currency'])) {
					$data['currency'] = array();
				}

				PhocacartCurrency::storeCurrencies($data['currency'], (int)$table->id, 'payment');

				$this->saveI18nData($table->id, $i18nData);
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

	public function delete(&$pks = array())
	{
		if (parent::delete($pks)) {
			$query = 'DELETE FROM #__phocacart_item_groups'
				. ' WHERE item_id IN ( ' . implode(',', $pks) . ' )'
				. ' AND type = 8';
			$this->_db->setQuery($query);
			$this->_db->execute();

			return $this->deleteI18nData($pks);
		}

		return false;
	}

	public function setDefault($id = 0) {

		$user = Factory::getUser();
		$db   = $this->getDbo();

		if (!$user->authorise('core.edit.state', 'com_phocacart')) {
			throw new Exception(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		if (!$table->load((int) $id)){
			throw new Exception(Text::_('COM_PHOCACART_ERROR_TABLE_NOT_FOUND'));
		}

		$db->setQuery("UPDATE #__phocacart_payment_methods SET ".$db->quoteName('default')." = '0'");
		$db->execute();

		$db->setQuery("UPDATE #__phocacart_payment_methods SET ".$db->quoteName('default')." = '1' WHERE id = " . (int) $id);
		$db->execute();

		$this->cleanCache();

		return true;
	}

	public function unsetDefault($id = 0) {

		$user = Factory::getUser();
		$db   = $this->getDbo();

		if (!$user->authorise('core.edit.state', 'com_phocacart')) {
			throw new Exception(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		if (!$table->load((int) $id)){
			throw new Exception(Text::_('COM_PHOCACART_ERROR_TABLE_NOT_FOUND'));
		}

		// It is possible that nothing will be set as default
		$db->setQuery("UPDATE #__phocacart_payment_methods SET ".$db->quoteName('default')." = '0' WHERE id = " . (int)$id);
		$db->execute();

		$this->cleanCache();

		return true;
	}
}
?>
