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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\ApplicationHelper;
use Phoca\PhocaCart\I18n\I18nAdminModelTrait;

class PhocaCartCpModelPhocacartSpecification extends AdminModel
{
	use I18nAdminModelTrait;

	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	public function __construct($config = [], MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{
		parent::__construct($config, $factory, $formFactory);

		$this->i18nTable = '#__phocacart_specification_groups_i18n';
		$this->i18nFields = [
			'title',
			'alias',
		];
	}

	public function getTable($type = 'PhocacartSpecification', $prefix = 'Table', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_phocacart.phocacartspecification', 'phocacartspecification', array('control' => 'jform', 'load_data' => $loadData));
        return $this->prepareI18nForm($form);
	}

	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartspecification.data', array());

		if (empty($data)) {
			$data = $this->getItem();
            $this->loadI18nItem($data);
		}

		return $data;
	}

	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');
		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= ApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = ApplicationHelper::stringURLSafe($table->title);
		}

		$table->date = PhocacartUtils::getDateFromString($table->date);

		if (empty($table->id) && empty($table->ordering)) {
			$db = Factory::getDbo();
			$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_specification_groups');
			$table->ordering = (int)$db->loadResult() + 1;
		}
	}

	public function save($data)
	{
		$i18nData   = $this->prepareI18nData($data);

		if (!parent::save($data)) {
			return false;
		}

		$id = $this->getState($this->getName() . '.id');

		return $this->saveI18nData($id, $i18nData);
	}

	public function delete(&$pks)
	{
		return parent::delete($pks)
			&& $this->deleteI18nData($pks);
	}
}
