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
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Log\Log;
jimport('joomla.application.component.modeladmin');

class PhocaCartCpModelPhocaCartFormfield extends AdminModel
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

	public function getTable($type = 'PhocaCartFormfield', $prefix = 'Table', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {

		$app	= Factory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartformfield', 'phocacartformfield', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartformfield.data', array());

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

		$table->title 		= str_replace('_', 'ph-u-ph', $table->title);// only alphabetical characters possible
		$table->title		= PhocacartText::stringURLSafe($table->title);
		$table->title		= str_replace('ph-u-ph', '_', $table->title);// only alphabetical characters possible
		$table->alias		= PhocacartText::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = PhocacartText::stringURLSafe($table->title);
		}

		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toSql();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = Factory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_form_fields');
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

	function displayItem(&$pks, $value = 1, $column = 'display_billing') {


		//$dispatcher	= JDispatcher::getInstance();
		$user		= Factory::getUser();
		$table		= $this->getTable('phocacartformfield');
		$pks		= (array) $pks;

		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				if (!$this->canEditState($table)) {
					unset($pks[$i]);
					$this->setError(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				}
			}
		}

		if (!$table->displayItem($pks, $value, $user->get('id'), $column)) {
			$this->setError($table->getError());
			return false;
		}
		return true;
	}


	public function save($data) {


		$save = parent::save($data);
		if ($save) {
			$savedId = $this->getState($this->getName().'.id');
			if ((int)$savedId > 0) {

				if (!isset($data['id']) || (isset($data['id']) && $data['id'] == 0)) {

					$type				= PhocacartFormItems::getColumnType($data['type']);
					$data['title']		= PhocacartText::stringURLSafe($data['title']);
					$data['title']		= strip_tags($data['title']);
					$db 				= Factory::getDBO();
					$config				= Factory::getConfig();
					$dbName 			= $config->get('db', '');


					if ($dbName != '' && $data['title'] != '' && $type != '') {
						/*$query = 'SELECT * FROM information_schema.COLUMNS'
						.' WHERE TABLE_SCHEMA = '.$db->quote($dbName)
						.' AND TABLE_NAME = '.$db->quote('#__phocacart_users')
						.' AND COLUMN_NAME = '.$db->quote($data['title']);*/

						// Manage _ as wildcard - BECAUSE OF LIKE
						$dataTitle = str_replace('_', '\_', $db->quote($data['title']));
						$dataTitle = str_replace('\\_', '\_', $dataTitle);


						$query10 = 'SHOW COLUMNS FROM #__phocacart_users LIKE '.$dataTitle;

						$db->setQuery($query10);
						$column1 = $db->loadResult();

						if (empty($column1)) {
							$query11 = 'ALTER TABLE #__phocacart_users ADD '.$db->quoteName($data['title']).' '.$type.';';

							$db->setQuery($query11);

							if (!$db->execute()){
								//$this->setError($db->getErrorMsg());
								throw new Exception('Database Error: Alter Phoca Cart User table', 500);
								return false;
							}

						}

						$query20 = 'SHOW COLUMNS FROM #__phocacart_order_users LIKE '.$dataTitle;
						$db->setQuery($query20);
						$column2 = $db->loadResult();

						if (empty($column2)) {
							$query21 = 'ALTER TABLE #__phocacart_order_users ADD '.$db->quoteName($data['title']).' '.$type.'';
							$db->setQuery($query21);
							if (!$db->execute()){
								//$this->setError($db->getErrorMsg());
								throw new Exception('Database Error: Alter Phoca Cart Order User table', 500);
								return false;
							}
						}
					}
				}

				PhocacartGroup::storeGroupsById((int)$savedId, 9, $data['group']);
			}
		}
		return $save;
	}



	public function delete(&$cid = array()) {

		$app	= Factory::getApplication();
		$db 	= Factory::getDBO();

		$result = false;
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

			// Select id's from product table, if there are some items, don't delete it.
			$query = 'SELECT a.id, a.title, a.type_default'
			. ' FROM #__phocacart_form_fields AS a'
			. ' WHERE a.id IN ( '.$cids.' )'
			. ' GROUP BY a.id, a.title, a.type_default';

			$db->setQuery( $query );

			if (!($rows = $db->loadObjectList())) {
				throw new Exception( Text::_('COM_PHOCACART_ERROR_PROBLEM_LOADING_DATA'), 500 );
				return false;
			}


			$cidOK 			= array();
			$cidOKTitle		= array();
			$cidError 		= array();
			foreach ($rows as $row) {
				if ($row->type_default == 0) {
					$cidOK[] 		= (int)$row->id;
					$cidOKTitle[] 	= $row->title;
				} else {
					$cidError[] = $row->title;
				}
			}

			if (count( $cidOK )) {
				$cidsOK = implode( ',', $cidOK );
				$query2 = 'DELETE FROM #__phocacart_form_fields'
				. ' WHERE id IN ( '.$cidsOK.' )';
				$db->setQuery( $query2 );
				if (!$db->execute()) {
					//$this->setError($row->getError());
					throw new Exception('Database Error: Delete items in form fields', 500);
					return false;
				};

				if (!empty($cidOKTitle)) {
					foreach($cidOKTitle as $k => $v) {
						$v		= PhocacartText::stringURLSafe($v);
						$v		= strip_tags($v);

						// Manage _ as wildcard - BECAUSE OF LIKE
						$dataTitle = str_replace('_', '\_', $db->quote($v));
						$dataTitle = str_replace('\\_', '\_', $dataTitle);

						$query10 = 'SHOW COLUMNS FROM #__phocacart_users LIKE '.$dataTitle;
						$db->setQuery($query10);
						$column1 = $db->loadResult();

						if (!empty($column1)) {
							$query11 = 'ALTER TABLE #__phocacart_users DROP COLUMN '.$db->quoteName($v).';';
							$db->setQuery($query11);
							$db->execute();
						}

						$query20 = 'SHOW COLUMNS FROM #__phocacart_order_users LIKE '.$dataTitle;
						$db->setQuery($query20);
						$column2 = $db->loadResult();

						if (!empty($column2)) {
							$query21 = 'ALTER TABLE #__phocacart_order_users DROP COLUMN '.$db->quoteName($v).';';
							$db->setQuery($query21);
							$db->execute();
						}
					}

				}
			}

			$msg = '';
			if (!empty($cidError)) {
				$cidErrorString = implode( ", ", $cidError );
				$msg .= Text::plural( 'COM_PHOCACART_ERROR_DELETE_DEFAULT_FORM_FIELDS', $cidErrorString );
			}
			if (!empty($cidOKTitle)) {
				$cidOKTitleString = implode( ", ", $cidOKTitle );
				if ($msg != '') { $msg .= "<br />";}
				$msg .= Text::plural( 'COM_PHOCACART_SUCCESS_FORM_FIELDS_DELETED', $cidOKTitleString );
			}

			$link = 'index.php?option=com_phocacart&view=phocacartformfields';
			$app->enqueueMessage($msg, 'error');
			$app->redirect($link);
		}
		return true;
	}
}
?>
