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

class PhocaCartCpModelPhocacartParameterValue extends JModelAdmin
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	protected function canDelete($record) {
		$user = JFactory::getUser();

		if (!empty($record->catid)) {
			return $user->authorise('core.delete', 'com_phocacart.phocacartparametervalue.'.(int) $record->parent_id);
		} else {
			return parent::canDelete($record);
		}
	}

	protected function canEditState($record) {
		$user = JFactory::getUser();

		if (!empty($record->catid)) {
			return $user->authorise('core.edit.state', 'com_phocacart.phocacartparametervalue.'.(int) $record->parent_id);
		} else {
			return parent::canDelete($record);
		}
	}

	public function getTable($type = 'PhocacartParameterValue', $prefix = 'Table', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {
		$app	= JFactory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartparametervalue', 'phocacartparametervalue', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData() {

		$app = JFactory::getApplication('administrator');

		$data = JFactory::getApplication()->getUserState('com_phocacart.edit.phocacartparametervalue.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}

		// Try to preselect category when we add new image
		// Take the value from filter select box in image list
		if (empty($data) || (!empty($data) && (int)$data->id < 1)) {
			$filter = (array) $app->getUserState('com_phocacart.phocacartparametervalues.filter.parameter_id');

			if (isset($filter[0]) && (int)$filter[0] > 0) {
				$data->set('parameter_id', (int)$filter[0]);
			}
		}

		return $data;
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

		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_parameter_values WHERE parent_id = '.(int)$table->parent_id);
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
		$condition[] = 'parent_id = '. (int) $table->parent_id;;
		return $condition;
	}

	public function save($data) {

	    if (parent::save($data)) {

	        $savedId = $this->getState($this->getName().'.id');
		    if ((int)$savedId > 0) {
               PhocacartCount::setProductCount(array(0 => (int)$savedId), 'parameter', 0);
            }
		    return true;
        } else {
	        return false;
        }

    }
}
?>
