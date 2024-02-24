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
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Application\ApplicationHelper;
use Phoca\PhocaCart\I18n\I18nAdminModelTrait;

class PhocaCartCpModelPhocacartParameterValue extends AdminModel
{
    use I18nAdminModelTrait;

	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

    public function __construct($config = [], MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
    {
        parent::__construct($config, $factory, $formFactory);

        $this->i18nTable = '#__phocacart_parameter_values_i18n';
        $this->i18nFields = [
            'title',
            'alias',
        ];
    }

	protected function canDelete($record) {
		$user = Factory::getUser();

		if (!empty($record->catid)) {
			return $user->authorise('core.delete', 'com_phocacart.phocacartparametervalue.'.(int) $record->parent_id);
		} else {
			return parent::canDelete($record);
		}
	}

	protected function canEditState($record) {
		$user = Factory::getUser();

		if (!empty($record->catid)) {
			return $user->authorise('core.edit.state', 'com_phocacart.phocacartparametervalue.'.(int) $record->parent_id);
		} else {
			return parent::canDelete($record);
		}
	}

	public function getTable($type = 'PhocacartParameterValue', $prefix = 'Table', $config = array()) {
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_phocacart.phocacartparametervalue', 'phocacartparametervalue', array('control' => 'jform', 'load_data' => $loadData));
        return $this->prepareI18nForm($form);
	}

	protected function loadFormData() {
		$app = Factory::getApplication('administrator');

		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartparametervalue.data', array());
		if (empty($data)) {
			$data = $this->getItem();
            $this->loadI18nItem($data);
		}

		if (empty($data) || (!empty($data) && (int)$data->id < 1)) {
			$filter = (array) $app->getUserState('com_phocacart.phocacartparametervalues.filter.parameter_id');

			if (isset($filter[0]) && (int)$filter[0] > 0) {
				$data->set('parameter_id', (int)$filter[0]);
			}
		}

		return $data;
	}

	protected function prepareTable($table) {
		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= ApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = ApplicationHelper::stringURLSafe($table->title);
		}

		if (empty($table->id)) {
			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_parameter_values WHERE parameter_id = '.(int)$table->parameter_id);
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
	}

	protected function getReorderConditions($table = null)
	{
		$condition = array();
		$condition[] = 'parent_id = '. (int) $table->parent_id;;
		return $condition;
	}

	public function save($data)
    {
        $i18nData = $this->prepareI18nData($data);

        if (parent::save($data)) {
            $savedId = $this->getState($this->getName() . '.id');
            if ((int) $savedId > 0) {
                PhocacartCount::setProductCount(array(0 => (int) $savedId), 'parameter', 0);
            }

            return $this->saveI18nData($savedId, $i18nData);
        }

        return false;
    }

	public function delete(&$pks)
	{
		$result = parent::delete($pks);

		if ($result) {
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->delete('#__phocacart_parameter_values_related')
				->whereIn('parameter_value_id', $pks);
			$db->setQuery($query);
			$db->execute();

            $this->deleteI18nData($pks);
		}

		return $result;
	}
}
