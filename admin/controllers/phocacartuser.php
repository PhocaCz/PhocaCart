<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocacartUser extends PhocaCartCpControllerPhocaCartCommon {


	public function save($key = null, $urlVar = null)
	{

		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app   = Factory::getApplication();
		$lang  = Factory::getLanguage();
		$model = $this->getModel();
		$table = $model->getTable();
		$data  = $this->input->post->get('jform', array(), 'array');
		//$checkin = property_exists($table, 'checked_out');
		$context = "$this->option.edit.$this->context";
		$task = $this->getTask();


		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		//$recordId = $this->input->getInt('user_id');

		// Populate the row id from the session.
		//$data[$key] = $recordId;
		$recordId = $data['user_id'];

		// The save2copy task needs to be handled slightly differently.
		if ($task == 'save2copy')
		{
			// Check-in the original row.
			/*if ($checkin && $model->checkin($data[$key]) === false)
			{
				// Check-in failed. Go back to the item and display a notice.
				$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
				$this->setMessage($this->getError(), 'error');

				$this->setRedirect(
					Route::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);

				return false;
			}*/

			// Reset the ID and then treat the request as for Apply.
			$data[$key] = 0;
			$task = 'apply';
		}

		// Access check.
		if (!$this->allowSave($data, $key))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		/*$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		// Test whether the data is valid.
		/*$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}
		*/
		/*if (!isset($validData['tags']))
		{
			$validData['tags'] = null;
		}*/
		$validData = $data;

		//$error = 0;

		if(!empty($data)) {

			$data['user_id_phs'] = $data['user_id'];// clone user id for shipping
			// Form Data
			$billing	= array();
			$shipping	= array();
			foreach($data as $k => $v) {
				$pos = strpos($k, '_phs');
				if ($pos === false) {
					$billing[$k] = $v;
				} else {
					$k = str_replace('_phs', '', $k);
					$shipping[$k] = $v;
				}
			}




			// Form Items
			/*$items = PhocacartFormItems::getFormItems(1,1,0);
			if(!empty($items)) {
				foreach($items as $k => $v) {
					if ($v->required == 1) {
						if (isset($billing[$v->title]) && $billing[$v->title] == '') {
							$msg = Text::_('COM_PHOCACART_FILL_OUT_THIS_FIELD') . ': '.Text::_($v->label)
							. ' <small>('.Text::_('COM_PHOCACART_BILLING_ADDRESS').')</small>';
							$app->enqueueMessage($msg, 'error');
							$error = 1;
						}

						// Don't check the shipping as it is not required
						if ($item['phcheckoutbsas']) {
							$billing['ba_sa'] = 1;
							$shipping['ba_sa'] = 1;
							// CHECKBOX IS ON
						} else {
							// CHECKBOX IS OFF
							$billing['ba_sa'] = 0;
							$shipping['ba_sa'] = 0;
							if (isset($shipping[$v->title]) && $shipping[$v->title] == '') {
								$msg = Text::_('COM_PHOCACART_FILL_OUT_THIS_FIELD') . ': '.Text::_($v->label)
								. ' <small>('.Text::_('COM_PHOCACART_SHIPPING_ADDRESS').')</small>';
								$app->enqueueMessage($msg, 'error');
								$error = 1;
							}
						}
					}

				}
			} else {
				$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_NO_FORM_LOADED'), 'error');
				$error = 1;
			}*/
		} else {
			$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list. $this->getRedirectToListAppend(), false));
			return false;
		}
		/*if ($error == 1) {
			$app->redirect(base64_decode($item['return']));
		}*/

		if (!empty($billing)) {
			//$model 	= $this->getModel('checkout');
			if(!$model->save($billing)) {
				$msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
				$this->setMessage($msg, 'error');
				$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item. $this->getRedirectToItemAppend($recordId, $urlVar), false));
				return false;
			} else {
				//$msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
				//$app->enqueueMessage($msg, 'success');
				// Waiting for shipping
			}
			//$app->redirect(base64_decode($item['return']));
		}

		if (!empty($shipping)) {
			//$model 	= $this->getModel('checkout');
			if(!$model->save($shipping, 1)) {
				$msg = Text::_('COM_PHOCACART_ERROR_DATA_NOT_STORED');
				$this->setMessage($msg, 'error');
				$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item. $this->getRedirectToItemAppend($recordId, $urlVar), false));
				return false;
			} else {
				//$msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
				//$app->enqueueMessage($msg, 'success');
				// Waiting for shipping
			}
			//$app->redirect(base64_decode($item['return']));
		}

		// Remove shipping because shipping methods can change while chaning address
		/*PhocacartShipping::removeShipping();
		$msg = Text::_('COM_PHOCACART_SUCCESS_DATA_STORED');
		$app->enqueueMessage($msg, 'success');
		$app->redirect(base64_decode($item['return']));*/





		// Attempt to save the data.
		/*if (!$model->save($validData))
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			// Redirect back to the edit screen.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item. $this->getRedirectToItemAppend($recordId, $urlVar), false));

			return false;
		}*/

		// Save succeeded, so check-in the record.
		/*if ($checkin && $model->checkin($validData[$key]) === false)
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			// Check-in failed, so go back to the record and display a notice.
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}*/



		$this->setMessage(
			Text::_(
				($lang->hasKey($this->text_prefix . ($recordId == 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
					? $this->text_prefix
					: 'JLIB_APPLICATION') . ($recordId == 0 && $app->isClient('site') ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
			)
		);

		// Redirect the user and adjust session state based on the chosen task.

		switch ($task)
		{
			case 'apply':
				// Set the record data in the session.
				//$recordId = $model->getState($this->context . '.id');

				$this->holdEditId($context, $recordId);
				$app->setUserState($context . '.data', null);
				$model->checkout($recordId);



				// Redirect back to the edit screen.
				$this->setRedirect(
					Route::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);
				break;

			/*case 'save2new':
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				// Redirect back to the edit screen.
				$this->setRedirect(
					Route::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item
						. $this->getRedirectToItemAppend(null, $urlVar), false
					)
				);
				break;*/

			default:
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				// Redirect to the list screen.
				$this->setRedirect(
					Route::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list
						. $this->getRedirectToListAppend(), false
					)
				);

				break;
		}

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model, $validData);

		return true;
	}
}
?>
