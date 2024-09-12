<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Dispatcher\Dispatcher;

jimport('joomla.application.component.modeladmin');

class PhocaCartCpModelPhocacartZone extends AdminModel
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	protected function canDelete($record)
	{
		//$user = Factory::getUser();
		return parent::canDelete($record);
	}

	protected function canEditState($record)
	{
		//$user = Factory::getUser();
		return parent::canEditState($record);
	}

	public function getTable($type = 'PhocacartZone', $prefix = 'Table', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {

		$app	= Factory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartzone', 'phocacartzone', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartzone.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = Factory::getDate();
		$user = Factory::getUser();

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= ApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = ApplicationHelper::stringURLSafe($table->title);
		}

		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_zones');
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
		else {
			// Set the values
			//$table->modified	= $date->toSql();
			//$table->modified_by	= $user->get('id');
		}
	}


	public function delete(&$cid = array()) {

		if (count( $cid )) {
			ArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			$table = $this->getTable();
			if (!$this->canDelete($table)){
				$error = $this->getError();
				if ($error){
					Log::add($error, Log::WARNING);
					return false;
				} else {
					Log::add(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), Log::WARNING);
					return false;
				}
			}

			// 1. DELETE ZONES
			$query = 'DELETE FROM #__phocacart_zones'
				. ' WHERE id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			// 2. DELETE ZONES IN SHIPPING METHOD
			$query = 'DELETE FROM #__phocacart_shipping_method_zones'
				. ' WHERE zone_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			// 3. DELETE ZONES IN PAYMENT METHOD
			$query = 'DELETE FROM #__phocacart_payment_method_zones'
				. ' WHERE zone_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			// 4. DELETE COUNTRIES IN ZONES
			$query = 'DELETE FROM #__phocacart_zone_countries'
				. ' WHERE zone_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			// 5. DELETE REGIONS IN ZONES
			$query = 'DELETE FROM #__phocacart_zone_regions'
				. ' WHERE zone_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
		}
		return true;
	}

	public function save($data)
	{
		//$dispatcher = J EventDispatcher::getInstance();
		$table = $this->getTable();



		/*
		if ((!empty($data['tags']) && $data['tags'][0] != ''))
		{
			$table->newTags = $data['tags'];
		}
		*/

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

			/*
			// Plugin parameters are converted to params column in payment table (x001)
			// Store form parameters of selected method
			$app		= Factory::getApplication();
			$dataPh		= $app->input->get('phform', array(), 'array');
			$registry 	= new Registry($dataPh['params']);
			//$registry 	= new JRegistry($dataPh);
			$dataPhNew 	= $registry->toString();
			if($dataPhNew != '') {
				$data['params'] = $dataPhNew;
			}
			*/

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

			if (in_array(false, $result, true)) {
				$this->setError($table->getError());
				return false;
			}

			// Store the data.
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}

			if ((int)$table->getId() > 0) {

				if (!isset($data['country'])) {
					$data['country'] = array();
				}

				PhocacartCountry::storeCountries($data['country'], (int)$table->getId(), 'zone');

				if (!isset($data['region'])) {
					$data['region'] = array();
				}

				PhocacartRegion::storeRegions($data['region'], (int)$table->getId(), 'zone');
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
}
?>
