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

class PhocaCartCpModelPhocacartRegion extends JModelAdmin
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';
	
	protected function canDelete($record)
	{
		//$user = JFactory::getUser();
		return parent::canDelete($record);
	}
	
	protected function canEditState($record)
	{
		//$user = JFactory::getUser();
		return parent::canEditState($record);
	}
	
	public function getTable($type = 'PhocacartRegion', $prefix = 'Table', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) {
		
		$app	= JFactory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartregion', 'phocacartregion', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_phocacart.edit.phocacartregion.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}
	
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= JApplication::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = JApplication::stringURLSafe($table->title);
		}

		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_regions WHERE country_id = '.(int)$table->country_id);
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
	
	public function importregions() {
		$app	= JFactory::getApplication();
		$db		= JFactory::getDBO();
		
		$db->setQuery('SELECT COUNT(id) FROM #__phocacart_regions');
		$sum = $db->loadResult();
		
		/*if ((int)$sum > 3900) {
			$message = JText::_('COM_PHOCACART_REGIONS_ALREADY_IMPORTED');
			$app->enqueueMessage($message, 'error');
			return false;
		}*/
		
		if ((int)$sum > 0) {
			$message = JText::_('COM_PHOCACART_REGIONS_CAN_BE_IMPORTED_ONLY_WHEN_REGION_TABLE_IS_EMPTY');
			$app->enqueueMessage($message, 'error');
			return false;
		}
		
		$db->setQuery('SELECT COUNT(id) FROM #__phocacart_countries');
		$sum = $db->loadResult();
		
		if ((int)$sum < 240) {
			$message = JText::_('COM_PHOCACART_FIRST_COUNTRIES_NEED_TO_BE_INPORTED');
			$app->enqueueMessage($message, 'error');
			return false;
		}
		
		$file	= JPATH_ADMINISTRATOR . '/components/com_phocacart/install/sql/mysql/regions.utf8.sql';
		if(JFile::exists($file)) {
			$buffer = file_get_contents($file);
			$queries = JDatabaseDriver::splitSql($buffer);
			if (count($queries) == 0) {
				return false;
			}
			
			foreach ($queries as $query){
				$query = trim($query);
				if ($query != '' && $query{0} != '#'){
					$db->setQuery($query);
					if (!$db->execute()){
						JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), JLog::WARNING);
						return false;
					}
				}
			}
			return true;
		} else {
			$app->enqueueMessage(JText::_('COM_PHOCACART_IMPORT_FILE_NOT_EXIST'), 'error');
			return false;
		}
	}
	
	public function delete(&$cid = array()) {
		
		if (count( $cid )) {
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			
			$table = $this->getTable();
			if (!$this->canDelete($table)){
				$error = $this->getError();
				if ($error){
					JLog::add($error, JLog::WARNING);
					return false;
				} else {
					JLog::add(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), JLog::WARNING);
					return false;
				}
			}
			
			// 1. DELETE REGIONS
			$query = 'DELETE FROM #__phocacart_regions'
				. ' WHERE id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			
			// 2. DELETE REGION TAXES
			$query = 'DELETE FROM #__phocacart_tax_regions'
				. ' WHERE region_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			
			// 3. DELETE COUNTRIES IN SHIPPING METHOD
			$query = 'DELETE FROM #__phocacart_shipping_method_regions'
				. ' WHERE region_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
			
			// 4. DELETE COUNTRIES IN PAYMENT METHOD
			$query = 'DELETE FROM #__phocacart_payment_method_regions'
				. ' WHERE region_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();
		}
		return true;
	}
}
?>