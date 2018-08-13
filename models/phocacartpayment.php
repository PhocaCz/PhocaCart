<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport('joomla.application.component.modeladmin');

class PhocaCartCpModelPhocacartPayment extends JModelAdmin
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';
	
	protected function canDelete($record) {
		return parent::canDelete($record);
	}
	
	protected function canEditState($record) {
		return parent::canEditState($record);
	}
	
	public function getTable($type = 'PhocacartPayment', $prefix = 'Table', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) {
		$app	= JFactory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartpayment', 'phocacartpayment', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	protected function loadFormData() {
		$data = JFactory::getApplication()->getUserState('com_phocacart.edit.phocacartpayment.data', array());
		if (empty($data)) {
			$data = $this->getItem();
			$price = new PhocacartPrice();
			$data->cost 			= $price->cleanPrice($data->cost);
			$data->cost_additional 	= $price->cleanPrice($data->cost_additional);
		}
		return $data;
	}
	
	protected function prepareTable($table) {
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= JApplicationHelper::stringURLSafe($table->alias);
		
		$table->cost 			= PhocacartUtils::replaceCommaWithPoint($table->cost);
		$table->cost_additional	= PhocacartUtils::replaceCommaWithPoint($table->cost_additional);
		$table->lowest_amount 	= PhocacartUtils::replaceCommaWithPoint($table->lowest_amount);
		$table->highest_amount 	= PhocacartUtils::replaceCommaWithPoint($table->highest_amount);

		if (empty($table->alias)) {
			$table->alias = JApplicationHelper::stringURLSafe($table->title);
		}
		
		$table->tax_id 	= PhocacartUtils::getIntFromString($table->tax_id);

		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_payment_methods');
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
	
	public function save($data)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$table = $this->getTable();
		
		

		if ((!empty($data['tags']) && $data['tags'][0] != ''))
		{
			$table->newTags = $data['tags'];
		}

		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');

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
			$app		= JFactory::getApplication();
			$dataPh		= $app->input->get('phform', array(), 'array');
			if (!empty($dataPh['params'])) {
				$registry 	= new JRegistry($dataPh['params']);
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
			$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, $table, $isNew));

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
				
				PhocacartGroup::storeGroupsById((int)$table->id, 8, $data['group']);
			
			}
		

			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, $table, $isNew));
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
	
	public function delete(&$cid = array()) {

		if (count( $cid )) {
			$delete = parent::delete($cid);
			if ($delete) {
				
				JArrayHelper::toInteger($cid);
				$cids = implode( ',', $cid );
			
				$query = 'DELETE FROM #__phocacart_item_groups'
				. ' WHERE item_id IN ( '.$cids.' )'
				. ' AND type = 8';
				$this->_db->setQuery( $query );
				$this->_db->execute();
			}
		}
	}
	
	public function setDefault($id = 0) {
		
		$user = JFactory::getUser();
		$db   = $this->getDbo();

		if (!$user->authorise('core.edit.state', 'com_phocacart')) {
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		if (!$table->load((int) $id)){
			throw new Exception(JText::_('COM_PHOCACART_ERROR_TABLE_NOT_FOUND'));
		}

		$db->setQuery("UPDATE #__phocacart_payment_methods SET ".$db->quoteName('default')." = '0'");
		$db->execute();

		$db->setQuery("UPDATE #__phocacart_payment_methods SET ".$db->quoteName('default')." = '1' WHERE id = " . (int) $id);
		$db->execute();

		$this->cleanCache();

		return true;
	}
	
	public function unsetDefault($id = 0) {
		
		$user = JFactory::getUser();
		$db   = $this->getDbo();

		if (!$user->authorise('core.edit.state', 'com_phocacart')) {
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		$table = $this->getTable();

		if (!$table->load((int) $id)){
			throw new Exception(JText::_('COM_PHOCACART_ERROR_TABLE_NOT_FOUND'));
		}

		// It is possible that nothing will be set as default
		$db->setQuery("UPDATE #__phocacart_payment_methods SET ".$db->quoteName('default')." = '0' WHERE id = " . (int)$id);
		$db->execute();

		$this->cleanCache();

		return true;
	}
}
?>