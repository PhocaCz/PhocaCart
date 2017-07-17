<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartControllerQuestion extends JControllerForm
{

	function submit() { 
		
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$session = JFactory::getSession();
		
		$app    = JFactory::getApplication();
		$uri 	= JFactory::getURI();
		$user 	= JFactory::getUser();
	
		$params 				= JComponentHelper::getParams('com_phocacart') ;
		$enable_ask_question 	= $params->get('enable_ask_question', 0);
		
		if ($enable_ask_question == 0) {
			throw new Exception(JText::_('COM_PHOCACART_ASK_QUESTION_DISABLED'), 500);
			return false;
		}
		
		$namespace  = 'phccrt' . $params->get('session_suffix');
		$data  		= $this->input->post->get('jform', array(), 'array');
		
		// Additional data
		$data['ip'] = PhocacartUtils::getIp();
		
		// Only because of information in LOG
		$productId = '';
		if (isset($data['product_id']) && (int)$data['product_id'] > 0) {
			$productId = (int)$data['product_id'];
		}
		
		// *** SECURITY
		// Default session test always enabled!
		$valid = $session->get('form_id', NULL, 'phocacart');
		$session->clear('form_id', 'phocacart');
		if (!$valid){
			$app->setUserState('com_phocacart.question.data', '');
			$session->clear('time', 'phccrt'.$params->get('session_suffix'));
			
			PhocacartLog::add(1, 'Ask a Question - Not valid session', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->username);
			//jexit(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'));
			throw new Exception(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
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
				$session->clear('time', 'phccrt'.$params->get('session_suffix'));
				
				PhocacartLog::add(1, 'Ask a Question - Hidden Field Error', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->unsername);
				throw new Exception(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
				return false;
			}
			
			// Hidden field was filled
			if (isset($data[$hiddenField]) && $data[$hiddenField] != '') {
				$app->setUserState('com_phocacart.question.data', '');
				$session->clear('time', 'phccrt'.$params->get('session_suffix'));
				
				PhocacartLog::add(1, 'Ask a Question - Hidden Field Filled', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->unsername);
				throw new Exception(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
				return false;
			}
			
		}
		
		// *** SECURITY
		// Check for a valid session cookie
		if($session->getState() != 'active'){
			// Save the data in the session.
			$app->setUserState('com_phocacart_cart.data', $data);
			$message = JText::_( 'COM_PHOCACART_SESSION_INVALID' );
			$app->enqueueMessage($message, 'error');
			
			PhocacartLog::add(1, 'Ask a Question - Session not active', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->unsername.', Message: '.$message);
			$app->redirect(JRoute::_($uri));
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
			$session->clear('time', 'phccrt'.$params->get('session_suffix'));
			
			PhocacartLog::add(1, 'Ask a Question - No Phoca Cart part', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->unsername);
			throw new Exception(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
			return false;
		}
		
		// *** SECURITY
		// Check Time
	    if((int)$params->get('enable_time_check_question', 0) > 0) {
            $time = $session->get('time', null, 'phccrt'.$params->get('session_suffix'));
            $delta = time() - $time;
			
			if($params->get('enable_time_check_question', 0) && $delta <= (int)$params->get('enable_time_check_question', 0)) {

				$app->setUserState('com_phocacart.question.data', $data);
				$message = JText::_( 'COM_PHOCACART_SUBMIT_TOO_FAST' );
				$app->enqueueMessage($message, 'error');
				
				PhocacartLog::add(1, 'Ask a Question - Submit too fast', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->unsername.', Message: '.$message . ', Time: '. $delta . ' sec.');
				$app->redirect(JRoute::_($uri));
				return false;
            }
        }
		
		// ***SECURITY
		// IP Ban
		if ($params->get('ip_ban', '') != '') {
			
			$isSpam = PhocacartSecurity::checkIpAddress($data['ip'], $params->get('ip_ban'));
			
			if ($isSpam) {
				//$app->setUserState('com_phocacart.question.data', $data);	// Save the data in the session.
				//$message = JText::_( 'COM_PHOCACART_POSSIBLE_SPAM_DETECTED' );
				//$app->enqueueMessage($message, 'error');
				//$app->redirect(JRoute::_($uri));
				
				$app->setUserState('com_phocacart.question.data', '');
				$session->clear('time', 'phccrt'.$params->get('session_suffix'));
				
				PhocacartLog::add(1, 'Ask a Question - IP Ban', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->unsername);
				throw new Exception(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
				return false;
			}
		} 
			
		// *** SECURITY
		// Validate the posted data.
		$form = $model->getForm();
		if (!$form) {
			$app->setUserState('com_phocacart.question.data', '');
			$session->clear('time', 'phccrt'.$params->get('session_suffix'));
			
			PhocacartLog::add(1, 'Ask a Question - Model not loaded', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->unsername.', Message: '.$model->getError());
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
				if (($errors[$i] instanceof JException) && ($errors[$i]->get('Level') == E_ERROR)) {
					$app->setUserState('com_phocacart.question.data', '');
					$session->clear('time', 'phccrt'.$params->get('session_suffix'));
					
					PhocacartLog::add(1, 'Ask a Question - Validate errors', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->unsername);
					throw new Exception(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED'), 500);
					return false;
				} else {
					
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					$continueValidate = false;
				}
			}
		}
		
		// *** SECURITY
		// Forbidden Word Filter
		$fwfa	= explode( ',', trim( $params->get('forbidden_word_filter', '') ) );
		$fwwfa	= explode( ',', trim( $params->get('forbidden_whole_word_filter', '') ) );
		
		foreach ($fwfa as $item) {
			if (trim($item) != '') {
				
				if (isset($data['message']) && stripos($data['message'], trim($item)) !== false) { 
					$continueValidate = false;
					PhocacartLog::add(1, 'Ask a Question - Forbidden Word Filder - Message', $productId, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
				if (isset($data['name']) && stripos($data['name'], trim($item)) !== false) { 
					$continueValidate = false;
					PhocacartLog::add(1, 'Ask a Question - Forbidden Word Filder - Name', $productId, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
				if (isset($data['phone']) && stripos($data['phone'], trim($item)) !== false) { 
					$continueValidate = false;
					PhocacartLog::add(1, 'Ask a Question - Forbidden Word Filder - Phone', $productId, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
				if (isset($data['email']) && stripos($data['email'], trim($item)) !== false) { 
					$continueValidate = false;
					PhocacartLog::add(1, 'Ask a Question - Forbidden Word Filder - Email', $productId, 'Word: '.$item.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
			}
		}
		
		foreach ($fwwfa as $item) {
			if ($item != '') {
				$itemBase		= trim($item);
				$item			= "/(^|[^a-zA-Z0-9_]){1}(".preg_quote(($item),"/").")($|[^a-zA-Z0-9_]){1}/i";
			

				if (isset($data['message']) && preg_match( $item, $data['message']) == 1) { 
					$continueValidate = false;
					PhocacartLog::add(1, 'Ask a Question - Forbidden Whole Word Filder - Message', $productId, 'Word: '.$itemBase.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
				if (isset($data['name']) && preg_match( $item, $data['name']) == 1) { 
					$continueValidate = false;
					PhocacartLog::add(1, 'Ask a Question - Forbidden Whole Word Filder - Name', $productId, 'Word: '.$itemBase.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
				if (isset($data['phone']) && preg_match( $item, $data['phone']) == 1) { 
					$continueValidate = false;
					PhocacartLog::add(1, 'Ask a Question - Forbidden Whole Word Filder - Phone', $productId, 'Word: '.$itemBase.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
				if (isset($data['email']) && preg_match( $item, $data['email']) == 1) { 
					$continueValidate = false;
					PhocacartLog::add(1, 'Ask a Question - Forbidden Whole Word Filder - Email', $productId, 'Word: '.$itemBase.', IP: '. $data['ip'].', User ID: '.$user->id);
					$app->enqueueMessage(JText::_('COM_PHOCACART_POSSIBLE_SPAM_DETECTED' ), 'warning');
				}
			}
		}
			
		
		// remove captcha from data after check
		$data['phq_captcha'] = '';
		
		if ($continueValidate == false) {			
			// Save the data in the session.
			$app->setUserState('com_phocacart.question.data', $data);
			// Log added before
			$app->redirect(JRoute::_($uri));
			return false;
		}
		
		
		// CHECKS DONE - store entry	
		$msg = '';
		if ($model->store($data)) {
			
			$msg = JText::_( 'COM_PHOCACART_THANK_YOU_FOR_LEAVING_INFORMATION_ASKING_QUESTION' );
		} else {
			$app->setUserState('com_phocacart.question.data', '');
			$session->clear('time', 'phccrt'.$params->get('session_suffix'));
			
			PhocacartLog::add(1, 'Ask a Question - Model store error', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id . ', User Name: '.$user->unsername.', Message: '.$model->getError());
			
			throw new Exception($model->getError(), 500);
			return false;
		}
		
	
	
		// Flush the data from the session
		$app->setUserState('com_phocacart.question.data', '');
		//$session->clear('time', 'phccrt'.$params->get('session_suffix'));
		$app->setUserState('com_phocacart.question.data', 'success_post_saved');
		$app->enqueueMessage($msg, 'success');
		$this->setRedirect($uri->toString());
		
		return true;
	}
}
?>
