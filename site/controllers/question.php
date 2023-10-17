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

class PhocaCartControllerQuestion extends FormController
{

	function submit() {

		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$session = Factory::getSession();

		$app    = Factory::getApplication();
		$uri 	= Uri::getInstance();
		$user 	= PhocacartUser::getUser();

		$params 							= PhocacartUtils::getComponentParameters() ;
		$enable_ask_question 				= $params->get('enable_ask_question', 0);
		$display_question_privacy_checkbox	= $params->get( 'display_question_privacy_checkbox', 0 );

		if ($enable_ask_question == 0) {
			throw new Exception(Text::_('COM_PHOCACART_ASK_QUESTION_DISABLED'), 500);
			return false;
		}

		$namespace  		= 'phccrt' . $params->get('session_suffix');
		$data  				= $this->input->post->get('jform', array(), 'array');
		$item['privacy']	= $this->input->get( 'privacy', false, 'string'  );

		$data['privacy'] 	= $item['privacy'] ? 1 : 0;

		if ($display_question_privacy_checkbox == 2 && $data['privacy'] == 0) {
			$msg = Text::_('COM_PHOCACART_ERROR_YOU_NEED_TO_AGREE_TO_PRIVACY_TERMS_AND_CONDITIONS');
			$app->enqueueMessage($msg, 'error');
			$app->redirect(Route::_($uri));
			return false;

		}

		// Additional data
		$data['ip'] = PhocacartUtils::getIp();

		// Only because of information in LOG
		$productId = '';
		if (isset($data['product_id']) && (int)$data['product_id'] > 0) {
			$productId = (int)$data['product_id'];
		}

		// *** SECURITY
		// Default session test always enabled!
		$valid = $session->get('form_id', NULL, $namespace);
		$session->clear('form_id', $namespace);
		if (!$valid){
			$app->setUserState('com_phocacart.question.data', '');
			$session->clear('time', $namespace);

			PhocacartLog::add(3, 'Ask a Question - Not valid session', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);
			//jexit(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'));
			throw new Exception(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
			return false;
		}

		$model  = $this->getModel('question');

		// *** SECURITY
		// Hidden Field
		if ($params->get('enable_hidden_field_question', 0) == 1) {
			$params->set('hidden_field_id', $session->get('hidden_field_id', 'fieldnotvalid', $namespace));
			$params->set('hidden_field_name', $session->get('hidden_field_name', 'fieldnotvalid', $namespace));
			$hiddenField = $session->get('hidden_field_name', 'fieldnotvalid', $namespace);

			$session->clear('hidden_field_id', $namespace);
			$session->clear('hidden_field_name', $namespace);
			$session->clear('hidden_field_class', $namespace);

			if ($params->get('hidden_field_id') == 'fieldnotvalid') {
				$app->setUserState('com_phocacart.question.data', '');
				$session->clear('time', $namespace);

				PhocacartLog::add(3, 'Ask a Question - Hidden Field Error', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);
				throw new Exception(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
				return false;
			}

			// Hidden field was filled
			if (isset($data[$hiddenField]) && $data[$hiddenField] != '') {
				$app->setUserState('com_phocacart.question.data', '');
				$session->clear('time', $namespace);

				PhocacartLog::add(3, 'Ask a Question - Hidden Field Filled', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);
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

			PhocacartLog::add(3, 'Ask a Question - Session not active', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username.', Message: '.$message);
			$app->redirect(Route::_($uri));
			return false;
		}

		// *** SECURITY
		// Task
		$task = $this->input->get('task');

		if ($task == 'phocacart.submit') {
			$task = 'submit';
		}
		if (($this->input->get('view') != 'question') || ($this->input->get('option') != 'com_phocacart') || ($task != 'submit')) {
			$app->setUserState('com_phocacart.question.data', '');
			$session->clear('time', $namespace);

			PhocacartLog::add(3, 'Ask a Question - No Phoca Cart part', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);
			throw new Exception(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
			return false;
		}

		// *** SECURITY
		// Check Time
	    if((int)$params->get('enable_time_check_question', 0) > 0) {
            $time = $session->get('time', null, $namespace);
            $delta = time() - $time;

			if($params->get('enable_time_check_question', 0) && $delta <= (int)$params->get('enable_time_check_question', 0)) {

				$app->setUserState('com_phocacart.question.data', $data);
				$message = Text::_( 'COM_PHOCACART_SUBMIT_TOO_FAST' );
				$app->enqueueMessage($message, 'error');

				PhocacartLog::add(3, 'Ask a Question - Submit too fast', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username.', Message: '.$message . ', Time: '. $delta . ' sec.');
				$app->redirect(Route::_($uri));
				return false;
            }
        }

		// ***SECURITY
		// IP Ban
		if ($params->get('ip_ban', '') != '') {

			$isSpam = PhocacartSecurity::checkIpAddress($data['ip'], $params->get('ip_ban'));

			if ($isSpam) {
				//$app->setUserState('com_phocacart.question.data', $data);	// Save the data in the session.
				//$message = Text::_( 'COM_PHOCACART_POSSIBLE_SPAM_DETECTED' );
				//$app->enqueueMessage($message, 'error');
				//$app->redirect(Route::_($uri));

				$app->setUserState('com_phocacart.question.data', '');
				$session->clear('time', $namespace);

				PhocacartLog::add(3, 'Ask a Question - IP Ban', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);
				throw new Exception(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
				return false;
			}
		}

		// *** SECURITY
		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			$app->setUserState('com_phocacart.question.data', '');
			$session->clear('time', $namespace);

			PhocacartLog::add(2, 'Ask a Question - ERROR - Model not loaded', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username.', Message: '.$model->getError());
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

		$validate 			= $model->validate($form, $data);

		if ($validate === false) {
			$errors	= $model->getErrors();


			// Get (possible) attack issues
			for ($i = 0, $n = count($errors); $i < $n && $i < 5; $i++) {


				if (($errors[$i] instanceof \Exception) && ($errors[$i]->getCode() == E_ERROR)) {
					$app->setUserState('com_phocacart.question.data', '');
					$session->clear('time', $namespace);

					PhocacartLog::add(2, 'Ask a Question - Validate errors', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);

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

				if (isset($data['message']) && stripos($data['message'], trim($item)) !== false) {
					$continueValidate = false;
					PhocacartLog::add(3, 'Ask a Question - Forbidden Word Filder - Message', $productId, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
				if (isset($data['name']) && stripos($data['name'], trim($item)) !== false) {
					$continueValidate = false;
					PhocacartLog::add(3, 'Ask a Question - Forbidden Word Filder - Name', $productId, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
				if (isset($data['phone']) && stripos($data['phone'], trim($item)) !== false) {
					$continueValidate = false;
					PhocacartLog::add(3, 'Ask a Question - Forbidden Word Filder - Phone', $productId, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
				if (isset($data['email']) && stripos($data['email'], trim($item)) !== false) {
					$continueValidate = false;
					PhocacartLog::add(3, 'Ask a Question - Forbidden Word Filder - Email', $productId, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
			}
		}

		foreach ($fwwfa as $item) {
			if ($item != '') {
				$itemBase		= trim($item);
				$item			= "/(^|[^a-zA-Z0-9_]){1}(".preg_quote(($item),"/").")($|[^a-zA-Z0-9_]){1}/i";


				if (isset($data['message']) && preg_match( $item, $data['message']) == 1) {
					$continueValidate = false;
					PhocacartLog::add(3, 'Ask a Question - Forbidden Whole Word Filder - Message', $productId, 'Word: '.$itemBase.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
				if (isset($data['name']) && preg_match( $item, $data['name']) == 1) {
					$continueValidate = false;
					PhocacartLog::add(3, 'Ask a Question - Forbidden Whole Word Filder - Name', $productId, 'Word: '.$itemBase.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
				if (isset($data['phone']) && preg_match( $item, $data['phone']) == 1) {
					$continueValidate = false;
					PhocacartLog::add(3, 'Ask a Question - Forbidden Whole Word Filder - Phone', $productId, 'Word: '.$itemBase.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
				if (isset($data['email']) && preg_match( $item, $data['email']) == 1) {
					$continueValidate = false;
					PhocacartLog::add(3, 'Ask a Question - Forbidden Whole Word Filder - Email', $productId, 'Word: '.$itemBase.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(Text::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
			}
		}


		// remove captcha from data after check
		$data['phq_captcha'] = '';

		if ($continueValidate == false) {
			// Save the data in the session.
			$app->setUserState('com_phocacart.question.data', $data);
			// Log added before
			$app->redirect(Route::_($uri));
			return false;
		}


		// CHECKS DONE - store entry
		$msg = '';
		if ($model->store($data)) {

			$msg = Text::_( 'COM_PHOCACART_THANK_YOU_FOR_LEAVING_INFORMATION_ASKING_QUESTION' );
		} else {
			$app->setUserState('com_phocacart.question.data', '');
			$session->clear('time', $namespace);

			PhocacartLog::add(2, 'Ask a Question - ERROR - Model store error', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username.', Message: '.$model->getError());

			throw new Exception($model->getError(), 500);
			return false;
		}



		// Flush the data from the session
		$app->setUserState('com_phocacart.question.data', '');
		//$session->clear('time', $namespace);
		$app->setUserState('com_phocacart.question.data', 'success_post_saved');
		$app->enqueueMessage($msg, 'success');
		$this->setRedirect($uri->toString());

		return true;
	}
}
?>
