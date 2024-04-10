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
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Log\Log;
jimport('joomla.application.component.modeladmin');

class PhocaCartCpModelPhocaCartStatus extends AdminModel
{
	protected	$option 		= 'com_phocacart';
	protected 	$text_prefix	= 'com_phocacart';

	protected function canDelete($record) {
		return parent::canDelete($record);
	}

	protected function canEditState($record) {
		return parent::canEditState($record);
	}

	public function getTable($type = 'PhocaCartStatus', $prefix = 'Table', $config = array()) {
		return Table::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {
		$app	= Factory::getApplication();
		$form 	= $this->loadForm('com_phocacart.phocacartstatus', 'phocacartstatus', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData() {
		$data = Factory::getApplication()->getUserState('com_phocacart.edit.phocacartstatus.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
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
				$db->setQuery('SELECT MAX(ordering) FROM #__phocacart_order_statuses');
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
		else {
			// Set the values
			//$table->modified	= $date->toSql();
			//$table->modified_by	= $user->get('id');
		}

		/*if (isset($table->type) && isset($table->published) && $table->type == 1 && $table->published == 0) {
			$table->published = 1;
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_DEFAULT_ITEMS_CANNOT_BE_UNPUBLISHED'));
		}*/
	}

	public function delete(&$cid = array()) {


		if (count( $cid )) {
			ArrayHelper::toInteger($cid);
			//$cids = implode( ',', $cid );
			//$app 	= Factory::getApplication();
			$error 	= 0;
			if (!empty($cid)) {
				foreach ($cid as $k => $v) {
					$query = 'SELECT type FROM #__phocacart_order_statuses WHERE id ='.(int)$v;
					$this->_db->setQuery($query);
					$type = $this->_db->loadRow();
					if (isset($type[0]) && $type[0] == 1) {
						$error = 1;
					} else {
						$query = 'DELETE FROM #__phocacart_order_statuses'
							. ' WHERE id = '.(int)$v;
						$this->_db->setQuery( $query );
						$this->_db->execute();
					}

				}
			}
		}
		if ($error) {
			$this->setError(Text::_('COM_PHOCACART_ERROR_DEFAULT_ITEMS_CANNOT_BE_DELETED'));
			//$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_DEFAULT_ITEMS_CANNOT_BE_DELETED'));
			return false;
		} else {
			return true;
		}

	}

	public function publish(&$pks, $value = 1)
	{

		$user = Factory::getUser();
		$table = $this->getTable();
		$pks = (array) $pks;
		$app = Factory::getApplication();

		$error 	= 0;
		foreach ($pks as $i => $pk){
			$table->reset();

			if ($table->load($pk)) {


				if (!$this->canEditState($table)){
					// Prune items that you can't change.
					unset($pks[$i]);
					Log::add(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), Log::WARNING, 'jerror');
					return false;
				}

				// If the table is checked out by another user, drop it and report to the user trying to change its state.
				if (property_exists($table, 'checked_out') && $table->checked_out && ($table->checked_out != $user->id)){
					Log::add(Text::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'), Log::WARNING, 'jerror');
					// Prune items that you can't change.
					unset($pks[$i]);
					return false;
				}

				/*if (property_exists($table, 'type') && $table->type && ((int)$table->type == 1) && $value == 0){
					$error = 1;
					unset($pks[$i]);
					//return false;
				}*/
			}
		}


		// Attempt to change the state of the records.
		if (!empty($pks)) {
			if (!$table->publish($pks, $value, $user->get('id'))) {
				$this->setError($table->getError());
				return false;
			}
		}



/*		if ($error) {

			//$this->setError(Text::_('COM_PHOCACART_ERROR_DEFAULT_ITEMS_CANNOT_BE_UNPUBLISHED'));
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_DEFAULT_ITEMS_CANNOT_BE_UNPUBLISHED'));
			return true;
		} else {*/
			return true;
		/*}*/
		$this->cleanCache();
	}

	public function save($data) {




		if (!isset($data['date'])) {
			$data['date'] = Factory::getDate()->toSql();;
		}

	    if(!isset($data['orders_view_display'])) {
	        $data['orders_view_display'] = array();
        }

	    if(!isset($data['email_attachments'])) {
	        $data['email_attachments'] = array();
        }
	    $data['orders_view_display'] = json_encode($data['orders_view_display']);
	    $data['email_attachments'] = json_encode($data['email_attachments']);



	    return parent::save($data);
    }
}
?>
