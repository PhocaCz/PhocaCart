<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

class PhocaCartModelQuestion extends JModelForm
{
	function __construct() {
		$app	= JFactory::getApplication();
		parent::__construct();
		$this->setState('filter.language',$app->getLanguageFilter());
	}

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_phocacart.question', 'question', array('control' => 'jform', 'load_data' => true));
		if (empty($form)) {
			return false;
		}

		$app	= JFactory::getApplication();
		$params = $app->getParams();
		
		// Set required or not && disable if not available
		if(!$params->get('display_name_form', 2)){
			$form->removeField('name');
		} else if ($params->get('display_name_form', 2) == 2){
			$form->setFieldAttribute('name', 'required', 'true');
		}

		if(!$params->get('display_email_form', 2)){
			$form->removeField('email');
		} else if ($params->get('display_email_form', 2) == 2){
			$form->setFieldAttribute('email', 'required', 'true');
		}

		if(!$params->get('display_phone_form', 2)){
			$form->removeField('phone');
		} else if ($params->get('display_phone_form', 2) == 2){
			$form->setFieldAttribute('phone', 'required', 'true');
		}

		if(!$params->get('display_message_form', 2)){
			$form->removeField('message');
		} else if ($params->get('display_message_form', 2) == 2){
			$form->setFieldAttribute('message', 'required', 'true');
		}
		
		if (!$params->get('enable_hidden_field_question', 0)){
			$form->removeField('hidden_field');
		} else {
			
			$form->setFieldAttribute('hidden_field', 'id', $params->get('hidden_field_id'));
			$form->setFieldAttribute('hidden_field', 'class', $params->get('hidden_field_class'));
			$form->setFieldAttribute('hidden_field', 'name', $params->get('hidden_field_name'));
			
		}
		
		if (!$params->get('enable_captcha_question', 2)) {
			$form->removeField('phq_captcha');
		} else {
			$form->setFieldAttribute('phq_captcha', 'type', 'phocacaptcha');
			$form->setFieldAttribute('phq_captcha', 'captcha_id', $params->get('captcha_id'));
			$form->setFieldAttribute('phq_captcha', 'validate', 'phocacartcaptcha');
		}
		
		return $form;
	}
	
	protected function loadFormData() {
		$data = (array) JFactory::getApplication()->getUserState('com_phocacart.question.data', array());
		return $data;
	}
	
	function store(&$data) {
		
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$uri 	= JFactory::getURI();
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_phocacart') ;
		
		// Maximum of character, they will be saved in database
		$data['message']	= substr($data['message'], 0, $params->get('max_char_question', 3000));
		$data['date'] 		= gmdate('Y-m-d H:i:s');   // Create the timestamp for the date
		
		$row = $this->getTable('PhocaCartQuestion');

		// Bind the form fields to the table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}	
	
		// Make sure the table is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
			
		// Store the Phoca guestbook table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		
		// Everything OK - send email
		if ($params->get('send_email_question', 0) > 0) {
			
			$data['product'] 	= array();
			$data['category']	= array();
			$productId			= 0;
			if(isset($data['product_id']) && (int)$data['product_id'] > 0) {
				if(isset($data['category_id']) && (int)$data['category_id'] > 0) {
					$data['product'] = PhocacartProduct::getProduct($data['product_id'], $data['category_id']);
					$data['category'] = PhocacartCategory::getCategoryById($data['category_id']);
				} else {
					$data['product'] = PhocacartProduct::getProduct($data['product_id']);
				}
				$productId = $data['product'];
			}
			
			
			$send = PhocaCartEmail::sendQuestionMail($params->get('send_email_question'), $data, JFactory::getURI()->toString(), $params);
		
			if (!$send) {
				$user 	= JFactory::getUser();
				PhocacartLog::add(1, 'Ask a Question - Problems with sending email', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id);
			}
		}
		
		$data['id'] = $row->id;
			
		return true;
	}
}
?>