<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

class PhocaCartControllerSubmit extends FormController
{

	function submit() {

		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$session = Factory::getSession();

		$app    = Factory::getApplication();
		$uri 	= Uri::getInstance();
		$user 	= PhocacartUser::getUser();

		$params 									= PhocacartUtils::getComponentParameters() ;
		$enable_submit_item 						= $params->get('enable_submit_item', 0);
		$display_submit_item_privacy_checkbox		= $params->get( 'display_submit_item_privacy_checkbox', 0 );

		$submit_item_form_fields					= $params->get( 'submit_item_form_fields', '' );
		$submit_item_form_fields_contact			= $params->get( 'submit_item_form_fields_contact', '' );

		$formFields = array_map('trim', explode(',', $submit_item_form_fields));
		$formFields = array_unique($formFields);
		$formFieldsC = array_map('trim', explode(',', $submit_item_form_fields_contact));
		$formFieldsC = array_unique($formFieldsC);

		$imageRequired = false;
		if (in_array('image*', $formFields)) {
			$imageRequired = true;
		}

		if ($enable_submit_item == 0) {
			throw new Exception(Text::_('COM_PHOCACART_SUBMIT_ITEM_DISABLED'), 500);
			return false;
		}

		if (!PhocacartSubmit::isAllowedToSubmit()) {
			throw new Exception(Text::_('COM_PHOCACART_SUBMIT_ITEM_NOT_ALLOWED'), 500);
			return false;
		}

		$namespace  		= 'phccrt' . $params->get('session_suffix');
		$data  				= $this->input->post->get('jform', array(), 'array');
		$file 				= Factory::getApplication()->input->files->get( 'jform', null, 'raw');
		$item['privacy']	= $this->input->get( 'privacy', false, 'string'  );

		$data['privacy'] 	= $item['privacy'] ? 1 : 0;

		if ($display_submit_item_privacy_checkbox == 2 && $data['privacy'] == 0) {
			$msg = Text::_('COM_PHOCACART_ERROR_YOU_NEED_TO_AGREE_TO_PRIVACY_TERMS_AND_CONDITIONS');
			$app->enqueueMessage($msg, 'error');
			$app->redirect(Route::_($uri));
			return false;

		}

		// Additional data
		$data['ip'] = PhocacartUtils::getIp();

		// *** SECURITY
		// Default session test always enabled!
		$valid = $session->get('form_id', NULL, $namespace);
		$session->clear('form_id', $namespace);
		if (!$valid){
			$app->setUserState('com_phocacart.submit.data', '');
			$session->clear('time', $namespace);

			PhocacartLog::add(3, 'Submit Item - Not valid session', 0, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);
			//jexit(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'));

			throw new Exception(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
			return false;
		}

		$model  = $this->getModel('submit');

		// *** SECURITY
		// Hidden Field
		if ($params->get('enable_hidden_field_submit_item', 0) == 1) {
			$params->set('hidden_field_id', $session->get('hidden_field_id', 'fieldnotvalid', $namespace));
			$params->set('hidden_field_name', $session->get('hidden_field_name', 'fieldnotvalid', $namespace));
			$hiddenField = $session->get('hidden_field_name', 'fieldnotvalid', $namespace);

			$session->clear('hidden_field_id', $namespace);
			$session->clear('hidden_field_name', $namespace);
			$session->clear('hidden_field_class', $namespace);

			if ($params->get('hidden_field_id') == 'fieldnotvalid') {
				$app->setUserState('com_phocacart.submit.data', '');
				$session->clear('time', $namespace);

				PhocacartLog::add(3, 'Submit Item - Hidden Field Error', 0, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);
				throw new Exception(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
				return false;
			}

			// Hidden field was filled
			if (isset($data[$hiddenField]) && $data[$hiddenField] != '') {
				$app->setUserState('com_phocacart.submit.data', '');
				$session->clear('time', $namespace);

				PhocacartLog::add(3, 'Submit Item - Hidden Field Filled', 0, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);
				throw new Exception(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
				return false;
			}

		}

		// *** SECURITY
		// Check for a valid session cookie
		if($session->getState() != 'active'){
			// Save the data in the session.
			$app->setUserState('com_phocacart.submit.data', $data);
			$message = Text::_( 'COM_PHOCACART_SESSION_INVALID' );
			$app->enqueueMessage($message, 'error');

			PhocacartLog::add(3, 'Submit Item - Session not active', 0, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username.', Message: '.$message);
			$app->redirect(Route::_($uri));
			return false;
		}

		// *** SECURITY
		// Task
		$task = $this->input->get('task');

		if ($task == 'phocacart.submit') {
			$task = 'submit';
		}
		if (($this->input->get('view') != 'submit') || ($this->input->get('option') != 'com_phocacart') || ($task != 'submit')) {
			$app->setUserState('com_phocacart.submit.data', '');
			$session->clear('time', $namespace);

			PhocacartLog::add(3, 'Submit Item - No Phoca Cart part', 0, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);
			throw new Exception(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
			return false;
		}

		// *** SECURITY
		// Check Time
	    if((int)$params->get('enable_time_check_submit_item', 0) > 0) {
            $time = $session->get('time', null, $namespace);
            $delta = time() - $time;

			if($params->get('enable_time_check_submit_item', 0) && $delta <= (int)$params->get('enable_time_check_submit_item', 0)) {

				$app->setUserState('com_phocacart.submit.data', $data);
				$message = Text::_( 'COM_PHOCACART_SUBMIT_TOO_FAST' );
				$app->enqueueMessage($message, 'error');

				PhocacartLog::add(3, 'Submit Item - Submit too fast', 0, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username.', Message: '.$message . ', Time: '. $delta . ' sec.');
				$app->redirect(Route::_($uri));
				return false;
            }
        }

		// ***SECURITY
		// IP Ban
		if ($params->get('ip_ban', '') != '') {

			$isSpam = PhocacartSecurity::checkIpAddress($data['ip'], $params->get('ip_ban'));

			if ($isSpam) {
				//$app->setUserState('com_phocacart.submit.data', $data);	// Save the data in the session.
				//$message = Text::_( 'COM_PHOCACART_POSSIBLE_SPAM_DETECTED' );
				//$app->enqueueMessage($message, 'error');
				//$app->redirect(Route::_($uri));

				$app->setUserState('com_phocacart.submit.data', '');
				$session->clear('time', $namespace);

				PhocacartLog::add(3, 'Submit Item - IP Ban', 0, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);
				throw new Exception(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
				return false;
			}
		}

		// *** SECURITY
		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			$app->setUserState('com_phocacart.submit.data', '');
			$session->clear('time', $namespace);

			PhocacartLog::add(2, 'Submit Item - ERROR - Model not loaded', 0, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username.', Message: '.$model->getError());
			throw new Exception($model->getError(), 500);
			return false;
		}

		// *** SECURITY
		// VALIDATE - continue with validation in case of problem
		$continueValidate 	= true;

		// SECURITY
		// Captcha - is validated in RULES of FORM FIELD - Exception for validate fields
		$captchaId = 1;//Possible parameters in Options for different captchas (reCaptcha = 1)
		switch ($captchaId) {
			case 1: // reCaptcha uses virtual field, so we cannot check the field set in form
				$data['phq_captcha'] = 'OK';
			break;
		}



		// IMAGE VALIDATION (need to be run before joomla validation)
		if ($imageRequired) {
			$imageUploaded = false;

			if (!empty($file['items_item']['image'])) {
				foreach ($file['items_item']['image'] as $k => $v) {
					if (isset($v['name']) && $v['name'] != '' && isset($v['tmp_name']) && $v['tmp_name'] != '' && isset($v['error']) && (int)$v['error'] < 1) {
						$imageUploaded = true;
						break;
					}
				}
			}

			if (!$imageUploaded) {
				$continueValidate = false;
				//PhocacartLog::add(3, 'Submit Item - Image not added - '.$v, 0, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
				$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_IMAGE_NOT_SUBMITTED' ), 'error');
			}

			// Used only for validation rules
			$data['items_item']['image'] = true;

		} else {
			// Remove empty form

			if (!empty($file['items_item']['image'])) {
				foreach ($file['items_item']['image'] as $k => $v) {
					if (isset($v['name']) && $v['name'] != '' && isset($v['tmp_name']) && $v['tmp_name'] != '' && isset($v['error']) && (int)$v['error'] < 1) {

					} else {
						unset($file['items_item']['image'][$k]);
					}
				}
			}

		}


		$validate 			= $model->validate($form, $data);// includes preprocessForm so it includes parameters too

		if ($validate === false) {
			$errors	= $model->getErrors();


			// Get (possible) attack issues
			for ($i = 0, $n = count($errors); $i < $n && $i < 5; $i++) {


				if (($errors[$i] instanceof \Exception) && ($errors[$i]->getCode() == E_ERROR)) {
					$app->setUserState('com_phocacart.submit.data', '');
					$session->clear('time', $namespace);

					PhocacartLog::add(2, 'Submit Item - Validate errors', 0, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);

					$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 'error');
					$app->redirect(Route::_($uri));
					return false;
				} else {

					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					$continueValidate = false;
				}

			}
			// Validate error message are now in stack, so no more render
			// just redirect back to the form with information about problems and fill the form field
			$continueValidate = false;
		}
		$data = $validate;


		// *** SECURITY
		// Forbidden Word Filter
		$fwfa	= explode( ',', trim( $params->get('forbidden_word_filter', '') ) );
		$fwwfa	= explode( ',', trim( $params->get('forbidden_whole_word_filter', '') ) );

		foreach ($fwfa as $item) {
			if (trim($item) != '') {

				if (!empty($formFields)) {
					foreach ($formFields as $k => $v) {
						$v = str_replace('*', '', trim($v));
						if (isset($data[$v]) && stripos($data[$v], trim($item)) !== false) {
							$continueValidate = false;
							PhocacartLog::add(3, 'Submit Item - Forbidden Word Filder - '.$v, 0, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
							$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
						}
					}
				}

				if (!empty($formFieldsC)) {
					foreach ($formFieldsC as $k => $v) {
						$v = str_replace('*', '', trim($v));
						if (isset($data[$v]) && stripos($data[$v], trim($item)) !== false) {
							$continueValidate = false;
							PhocacartLog::add(3, 'Submit Item - Forbidden Word Filder - '.$v, 0, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
							$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
						}
					}
				}

			}
		}

		foreach ($fwwfa as $item) {
			if ($item != '') {
				$itemBase		= trim($item);
				$item			= "/(^|[^a-zA-Z0-9_]){1}(".preg_quote(($item),"/").")($|[^a-zA-Z0-9_]){1}/i";

				if (!empty($formFields)) {
					foreach ($formFields as $k => $v) {
						$v = str_replace('*', '', trim($v));
						if (isset($data[$v]) && stripos($data[$v], trim($item)) !== false) {
							$continueValidate = false;
							PhocacartLog::add(3, 'Submit Item - Forbidden Whole Word Filder - '.$v, 0, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
							$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
						}
					}
				}

				if (!empty($formFieldsC)) {
					foreach ($formFieldsC as $k => $v) {
						$v = str_replace('*', '', trim($v));
						if (isset($data[$v]) && stripos($data[$v], trim($item)) !== false) {
							$continueValidate = false;
							PhocacartLog::add(3, 'Submit Item - Forbidden Whole Word Filder - '.$v, 0, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
							$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
						}
					}
				}

			}
		}


		// remove captcha from data after check
		$data['phq_captcha'] = '';





		if ($continueValidate == false) {
			// Save the data in the session.
			$app->setUserState('com_phocacart.submit.data', $data);
			// Log added before
			$app->redirect(Route::_($uri));
			return false;
		}





		// CHECKS DONE - store entry
		$msg = '';
		if ($model->store($data, $file)) {

			$msg = Text::_( 'COM_PHOCACART_THANK_YOU_FOR_SUBMITTING_YOUR_ITEM' );
		} else {
			$app->setUserState('com_phocacart.submit.data', '');
			$session->clear('time', $namespace);

			PhocacartLog::add(2, 'Submit Item - ERROR - Model store error', 0, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username.', Message: '.$model->getError());

			//throw new Exception($model->getError(), 500);
			//return false;
			$app->redirect(Route::_($uri));
			return false;
		}



		// Flush the data from the session
		$app->setUserState('com_phocacart.submit.data', '');
		//$session->clear('time', $namespace);
		$app->setUserState('com_phocacart.submit.data', 'success_post_saved');
		$app->enqueueMessage($msg, 'success');
		$this->setRedirect($uri->toString());

		return true;
	}
}
?>
