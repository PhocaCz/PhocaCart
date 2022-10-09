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
use Joomla\Registry\Registry;

jimport('joomla.application.component.modeladmin');

class PhocaCartCpModelPhocacartManufacturer extends AdminModel
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	protected function canDelete($record) {
		return parent::canDelete($record);
	}

	protected function canEditState($record) {
		return parent::canEditState($record);
	}

	public function getTable($type = 'PhocacartManufacturer', $prefix = 'Table', $config = array()) {
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {
		$form 	= $this->loadForm('com_phocacart.phocacartmanufacturer', 'phocacartmanufacturer', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

  public function getItem($pk = null) {
    if ($item = parent::getItem($pk)) {
      // Convert the metadata field to Registry
      if (isset($item->metadata)) {
        $registry = new Registry;
        $registry->loadString($item->metadata);
        $item->metadata = $registry->toArray();
      }
    }
    return $item;
  }

	protected function loadFormData() {
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartmanufacturer.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}

    $this->preprocessData('com_phocacart.phocacartmanufacturer', $data);
		return $data;
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
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_manufacturers');
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
		if (parent::save($data)) {
			$savedId = $this->getState($this->getName().'.id');
			if ((int)$savedId > 0) {
				PhocacartCount::setProductCount(array(0 => (int)$savedId), 'manufacturer', 1);
			}
			return true;
		} else {
			return false;
		}

	}
}
