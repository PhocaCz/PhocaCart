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
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\I18n\I18nAdminModelTrait;

jimport('joomla.application.component.modeladmin');

class PhocaCartCpModelPhocacartTax extends AdminModel
{
	use I18nAdminModelTrait;

	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	public function __construct($config = [], MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{
		parent::__construct($config, $factory, $formFactory);

		$this->i18nTable = '#__phocacart_taxes_i18n';
		$this->i18nFields = [
			'title',
			'alias'
		];
	}

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

	public function getTable($type = 'PhocacartTax', $prefix = 'Table', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {

		$form 	= $this->loadForm('com_phocacart.phocacarttax', 'phocacarttax', array('control' => 'jform', 'load_data' => $loadData));
		return $this->prepareI18nForm($form);
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacarttax.data', array());

		if (empty($data)) {
			$data = $this->getItem();
			$this->loadI18nItem($data);
		}

		return $data;
	}

	public function getItem($pk = null) {
		if ($item = parent::getItem($pk)) {
			$item->tax_rate	= PhocacartPrice::cleanPrice($item->tax_rate);

			if (isset($item->tax_hide)) {
				$registry = new Registry;
				$registry->loadString($item->tax_hide);
				$item->tax_hide = $registry->toArray();
				//$item->tax_hide = json_decode($item->tax_hide, true);
			}
		}
		return $item;
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


		$table->tax_rate 			= PhocacartUtils::replaceCommaWithPoint($table->tax_rate);


		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_taxes');
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

		$i18nData = $this->prepareI18nData($data);
		//$table = $this->getTable();


		if (!empty($data['tax_hide'])) {
			$registry 	= new Registry($data['tax_hide']);
			$taxHide 	= $registry->toString();
			if($taxHide != '') {
				$data['tax_hide'] = $taxHide;
			}
			//$data['tax_hide'] = json_encode($data['tax_hide']);
		} else {
			$data['tax_hide'] = '';
		}

		if (parent::save($data)) {
            $savedId = $this->getState($this->getName() . '.id');

            return $this->saveI18nData($savedId, $i18nData);
        }

        return false;
	}


	public function delete(&$cid = array()) {


		if (count( $cid )) {
			ArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			$table = $this->getTable();
			if (!$this->canDelete($table)){
				$error = $this->getError();
				if ($error){
					Log::add($error, Log::WARNING);
					return false;
				} else {
					Log::add(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), Log::WARNING);
					return false;
				}
			}

			// 1. DELETE TAXES
			$query = 'DELETE FROM #__phocacart_taxes'
				. ' WHERE id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();


			// 2. DELETE COUNTRY TAXES
			$query = 'DELETE FROM #__phocacart_tax_countries'
				. ' WHERE tax_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			$this->_db->execute();

			$this->deleteI18nData($cid);

		}
		return true;
	}
}
?>
