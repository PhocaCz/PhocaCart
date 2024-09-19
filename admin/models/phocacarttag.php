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

class PhocaCartCpModelPhocacartTag extends AdminModel
{
	use I18nAdminModelTrait;

	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	public function __construct($config = [], MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{
		parent::__construct($config, $factory, $formFactory);

		$this->i18nTable = '#__phocacart_tags_i18n';
		$this->i18nFields = [
			'title',
			'alias',
			'description',
		];
	}

	public function getTable($type = 'PhocacartTag', $prefix = 'Table', $config = array()) {
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_phocacart.phocacarttag', 'phocacarttag', array('control' => 'jform', 'load_data' => $loadData));
        return $this->prepareI18nForm($form);
	}

	protected function loadFormData() {
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacarttag.data', array());
		if (empty($data)) {
			$data = $this->getItem();
			$this->loadI18nItem($data);
		}
		return $data;
	}

	protected function prepareTable($table) {
		jimport('joomla.filter.output');

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= ApplicationHelper::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = ApplicationHelper::stringURLSafe($table->title);
		}

		if (empty($table->id)) {
			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_tags');
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
	}

	public function save($data)
	{
		$data['link_cat'] = (int) $data['link_cat'];

		$i18nData = $this->prepareI18nData($data);
		if (parent::save($data)) {
			$savedId = $this->getState($this->getName() . '.id');
			PhocacartCount::setProductCount(array(0 => (int) $savedId), 'tag', 1);
			PhocacartCount::setProductCount(array(0 => (int) $savedId), 'label', 1);

			return $this->saveI18nData($savedId, $i18nData);
		}

		return false;
	}

	public function delete(&$pks)
	{

        if (parent::delete($pks)) {
            if (PhocacartTag::deleteTagsLabelsRelated($pks) && $this->deleteI18nData($pks)) {
                return true;
            }
		}

		return false;
	}
}

