<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocaCartWizard extends PhocaCartCpControllerPhocaCartCommon

{
	// WIZARD
	// 1 ... automatically opened when there are no items set
	// 2 ... force wizard
	// 10 ... force wizard page 1 (not the main page)
	
	public function skipwizard() {
		
		$app		= Factory::getApplication();
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		PhocacartUtils::setWizard(0);
		$redirect	= 'index.php?option=com_phocacart';
		$app->redirect($redirect);
	}

	public function enablewizard() {
		$app		= Factory::getApplication();
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		PhocacartUtils::setWizard(2);	
		$redirect	= 'index.php?option=com_phocacart';
		$app->redirect($redirect);
	}
	
	public function backtowizard() {
		
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$app		= Factory::getApplication();
		$taskGroup	= $app->input->get('taskgroup', '');
		
		if ($taskGroup != '') {
			$this->unlockTable($taskGroup);
		}
		
		PhocacartUtils::setWizard(11);	
		$redirect	= 'index.php?option=com_phocacart';
		$app->redirect($redirect);
	}
	
	public function unlockTable($taskGroup) {
		
		$a = str_replace('phocacart', '', $taskGroup);
		$b = ucfirst($a);
		$c = 'Phocacart'.strip_tags($b);
		
		$model 		= $this->getModel($c, 'PhocaCartCpModel');
		$context 	= 'com_phocacart.edit.'.strip_tags($taskGroup);

		
		$table 		= $model->getTable();
		$key 		= $table->getKeyName();
		$recordId 	= $this->input->getInt($key);
		

		// Attempt to check-in the current record.
		if ($recordId)
		{
			if (property_exists($table, 'checked_out'))
			{
				if ($model->checkin($recordId) === false)
				{
					// Check-in failed, go back to the record and display a notice.
					/*$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
					$this->setMessage($this->getError(), 'error');

					$this->setRedirect(
						Route::_(
							'index.php?option=' . $this->option . '&view=' . $this->view_item
							. $this->getRedirectToItemAppend($recordId, $key), false
						)
					);*/

					return false;
				}
			}
		}

		// Clean the session data and redirect.
		$this->releaseEditId($context, $recordId);
		Factory::getApplication()->setUserState($context . '.data', null);

		return true;
	}
}
?>
