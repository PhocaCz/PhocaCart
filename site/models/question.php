<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;



jimport('joomla.application.component.model');

class PhocaCartModelQuestion extends FormModel
{
	function __construct() {
		$app	= Factory::getApplication();
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

		$app	= Factory::getApplication();
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
		$data = (array) Factory::getApplication()->getUserState('com_phocacart.question.data', array());
		return $data;
	}

	function store(&$data) {

		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$uri 	= Uri::getInstance();
		$app    = Factory::getApplication();
		$params = PhocacartUtils::getComponentParameters() ;

		// Maximum of character, they will be saved in database
		$data['message']		= substr($data['message'], 0, $params->get('max_char_question', 3000));
		$data['date'] 			= gmdate('Y-m-d H:i:s');   // Create the timestamp for the date

		$data['params']			= '';
		$data['category_id']	= PhocacartUtils::getIntFromString($data['category_id']);
		$data['product_id']		= PhocacartUtils::getIntFromString($data['product_id']);
		$data['ordering']		= $this->increaseOrdering();
		$row = $this->getTable('PhocaCartQuestion');


		// Bind the form fields to the table
		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		// Make sure the table is valid
		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}

		// Store the Phoca guestbook table to the database
		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}

		// Everything OK - send email
		if ($params->get('send_email_question', 0) > 0 || $params->get('send_email_question_others', '') != '') {

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


			$send = PhocacartEmail::sendQuestionMail($data, Uri::getInstance()->toString(), $params);

			if (!$send) {
				$user 	= PhocacartUser::getUser();
				PhocacartLog::add(2, 'Ask a Question - ERROR - Problems with sending email', $productId, 'IP: '. $data['ip'].', User ID: '.$user->id);
			}
		}

		$data['id'] = $row->id;

		return true;
	}

	public function increaseOrdering() {
		$this->_db->setQuery('SELECT MAX(ordering) FROM #__phocacart_questions');
		$max = $this->_db->loadResult();
		$ordering = $max + 1;
		return $ordering;
	}
}
?>
