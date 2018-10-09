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

class PhocaCartCpModelPhocacartUser extends JModelAdmin
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';
	
	protected $fields;

	public function getFields(){
		if (empty($this->fields)) {
			$this->fields = PhocacartFormUser::getFormXml('', '_phs', 1, 1, 0);//Fields in XML Format
		}
		return $this->fields;
	}
	
	
	protected function canDelete($record) {
		return parent::canDelete($record);
	}
	
	protected function canEditState($record) {
		return parent::canEditState($record);
	}
	
	public function getTable($type = 'PhocacartUser', $prefix = 'Table', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getFormSpecific($data = array(), $loadData = true) {
		
		if (empty($this->fields['xml'])) {
			$this->fields = $this->getFields();
		}
		$form = $this->loadForm('com_phocacart.userspecific', (string)$this->fields['xml'], array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	public function getForm($data = array(), $loadData = true) {
		
		$form = $this->loadForm('com_phocacart.user', 'phocacartuser', array('control' => 'jform', 'load_data' => $loadData));
		
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	
	protected function loadFormData() {
		$formData = (array) JFactory::getApplication()->getUserState('com_phocacart.user.data', array());
		
		if (empty($data)) {
			$formData = $this->getItem();
		}
		return $formData;
	}
	
	public function getItem($pk = null) {
		$app	= JFactory::getApplication();
		
		if (empty($pk)) {
			$pk = (int) $this->getState($this->getName() . '.id');
		}
		
		$user 	= $this->getUser($pk);
		
		
		$table 	= $this->getTable('PhocacartUser', 'Table');
		$tableS = $this->getTable('PhocacartUser', 'Table');
		
		// Billing
		if(isset($user->id) && (int)$user->id > 0) {
			
			$return = $table->load(array('user_id' => (int)$user->id, 'type' => 0));
			
			if ($return === false && $table->getError()) {
				$this->setError($table->getError());
				return false;
			}
		}
		
		// Shipping
		if(isset($user->id) && (int)$user->id > 0) {
			$returnS = $tableS->load(array('user_id' => (int)$user->id, 'type' => 1));
			if ($returnS === false && $tableS->getError()) {
				$this->setError($tableS->getError());
				return false;
			}
		}
		
		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		$item = \Joomla\Utilities\ArrayHelper::toObject($properties, 'JObject');
		
		$propertiesS = $tableS->getProperties(1);
		//$itemS = \Joomla\Utilities\ArrayHelper::toObject($propertiesS, 'JObject');
		
		//Add shipping data to billing and do both data package
		if(!empty($propertiesS) && is_object($item)) {
			foreach($propertiesS as $k => $v) {
				$newName = $k . '_phs';
				$item->$newName = $v;
			
			}
		
		}
		/*

		if (property_exists($item, 'params'))
		{
			$registry = new JRegistry;
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}*/
		
		return $item;
	}
	
	/*
	 * User id is the key, not the id in table users
	 * we are managing two rows in table - shipping, billing
	 * so we cannot do standard checkout
	 */
	 
	public function checkout($pk = null) {
		return true;
	}
	
	protected function getUser() {
		$app	= JFactory::getApplication();
		$userId = $app->input->get('id', 0, 'int'); 
		$user 	= JFactory::getUser($userId);
		return $user;
	}
	
	
	public function save($data, $type = 0) {
		
		$app	= JFactory::getApplication();
		$data['type']		= (int)$type;
		$data['country']	= PhocacartUtils::getIntFromString($data['country']);
		$data['region']		= PhocacartUtils::getIntFromString($data['region']);
		$row = $this->getTable('PhocacartUser', 'Table');

		if(isset($data['user_id']) && $data['user_id'] > 0) {
			if (!$row->load(array('user_id' => (int)$data['user_id'], 'type' => $type))) {
				// No data yet
				
			} else {
				if (isset($row->id) && (int)$row->id > 0 && (!isset($data['id']) || (isset($data['id']) && $data['id'] == ''))) {
					$data['id'] = (int)$row->id;
				}
			}
		}
	

		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$row->date = gmdate('Y-m-d H:i:s');
		

		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if ($row->id == '') {
			// fix the type by new items
			$row->id = null;
		}
		
		// Store the table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		// We save shipping and billing after each other - twice, so don't delete the group and run only once
		if ($type == 0) {
			PhocacartGroup::storeGroupsById((int)$row->user_id, 1, $data['group']);
		}
	
		return $row->user_id;
	}
	
	public function delete(&$cid = array()) {
		return false;
	}
}
?>