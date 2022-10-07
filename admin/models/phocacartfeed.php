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
use Joomla\Registry\Registry;
use Joomla\CMS\Application\ApplicationHelper;


jimport('joomla.application.component.modeladmin');

class PhocaCartCpModelPhocacartFeed extends AdminModel
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	protected function canDelete($record) {
		return parent::canDelete($record);
	}

	protected function canEditState($record) {
		return parent::canEditState($record);
	}

	public function getTable($type = 'PhocacartFeed', $prefix = 'Table', $config = array()) {
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {
		$app	= Factory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartfeed', 'phocacartfeed', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData() {
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartfeed.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}

	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {
			// Convert the params field to an array.
			if (isset($item->item_params)) {
				$registry = new Registry;
				$registry->loadString($item->item_params);
				$item->item_params = $registry->toArray();
			}

			if (isset($item->feed_params)) {
				$registry = new Registry;
				$registry->loadString($item->feed_params);
				$item->feed_params = $registry->toArray();
			}

		}

		return $item;
	}

	protected function prepareTable($table) {
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
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_feeds');
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


	public function save($data) {


		if (isset($data['item_params']) && is_array($data['item_params'])) {
			$registry = new Registry;
			$registry->loadArray($data['item_params']);
			$data['item_params'] = (string) $registry;
		}

		if (isset($data['feed_params']) && is_array($data['feed_params'])) {
			$registry = new Registry;
			$registry->loadArray($data['feed_params']);
			$data['feed_params'] = (string) $registry;
		}

		if (parent::save($data)) {
			return true;
		}
		return false;
	}
}
?>
