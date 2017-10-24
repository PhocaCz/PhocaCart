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

class PhocaCartCpModelPhocacartDiscount extends JModelAdmin
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';
	
	protected function canDelete($record) {
		return parent::canDelete($record);
	}
	
	protected function canEditState($record) {
		return parent::canEditState($record);
	}
	
	public function getTable($type = 'PhocacartDiscount', $prefix = 'Table', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) {
		$app	= JFactory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartdiscount', 'phocacartdiscount', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	protected function loadFormData() {
		$data = JFactory::getApplication()->getUserState('com_phocacart.edit.phocacartdiscount.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}
	
	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {
			$item->discount	= PhocacartPrice::cleanPrice($item->discount);
		}
		return $item;
	}
	
	protected function prepareTable($table) {
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= JApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = JApplicationHelper::stringURLSafe($table->title);
		}
		
		$table->valid_from 				= PhocacartUtils::getDateFromString($table->valid_from);
		$table->valid_to 				= PhocacartUtils::getDateFromString($table->valid_to);
		
		
		$table->quantity_from			= PhocacartUtils::getIntFromString($table->quantity_from);
		$table->total_amount	= PhocacartUtils::replaceCommaWithPoint($table->total_amount);
		$table->discount		= PhocacartUtils::replaceCommaWithPoint($table->discount);

		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_discounts');
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
			
				if (!isset($data['product_ids'])) {
					$data['product_ids'] = '';
				}
				PhocacartDiscountCart::storeDiscountProductsById($data['product_ids'], (int)$table->id );
				
				if (!isset($data['cat_ids'])) {
					$data['cat_ids'] = array();
				}
				PhocacartDiscountCart::storeDiscountCatsById($data['cat_ids'], (int)$table->id);
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
		
		if ((int)$table->id > 0) {
			PhocacartGroup::storeGroupsById((int)$table->id, 5, $data['group']);
		}
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
				. ' AND type = 5';
				$this->_db->setQuery( $query );
				$this->_db->execute();
			}
		}
	}
}
?>