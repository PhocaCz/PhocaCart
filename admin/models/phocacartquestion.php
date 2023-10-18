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
jimport('joomla.application.component.modeladmin');

class PhocaCartCpModelPhocaCartQuestion extends AdminModel
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
	
	public function getTable($type = 'PhocaCartQuestion', $prefix = 'Table', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}
	
	public function getForm($data = array(), $loadData = true) {
		
		$app	= Factory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartquestion', 'phocacartquestion', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}
	
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartquestion.data', array());

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
		
		$table->params 	= PhocacartUtils::getStringFromItem($table->params);
		$table->date	= PhocacartUtils::getDateFromString($table->date);
		
		
		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_questions WHERE product_id = '. (int) $table->product_id);
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
	
	protected function getReorderConditions($table = null)
	{
		$condition = array();
		$condition[] = 'product_id = '. (int) $table->product_id;
		return $condition;
	}
	/*
	public function increaseOrdering($productId) {
		
		$ordering = 1;
		$this->_db->setQuery('SELECT MAX(ordering) FROM #__phocacart_reviews WHERE product_id='.(int)$productId);
		$max = $this->_db->loadResult();
		$ordering = $max + 1;
		return $ordering;
	}*/
}
?>
