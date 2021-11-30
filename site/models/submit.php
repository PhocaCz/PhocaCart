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
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Form\Form;



jimport('joomla.application.component.model');

class PhocaCartModelSubmit extends FormModel
{
	function __construct() {
		$app	= Factory::getApplication();
		parent::__construct();
		$this->setState('filter.language',$app->getLanguageFilter());
	}

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_phocacart.submit', 'submit', array('control' => 'jform', 'load_data' => true));
		if (empty($form)) {
			return false;
		}

		$app	= Factory::getApplication();
		$params = $app->getParams();

		/*
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
		}*/

		if (!$params->get('enable_hidden_field_submit_item', 0)){
			$form->removeField('hidden_field');
		} else {

			$form->setFieldAttribute('hidden_field', 'id', $params->get('hidden_field_id'));
			$form->setFieldAttribute('hidden_field', 'class', $params->get('hidden_field_class'));
			$form->setFieldAttribute('hidden_field', 'name', $params->get('hidden_field_name'));

	}

		if (!$params->get('enable_captcha_submit_item', 2)) {
			$form->removeField('phq_captcha');
		} else {
			$form->setFieldAttribute('phq_captcha', 'type', 'phocacaptcha');
			$form->setFieldAttribute('phq_captcha', 'captcha_id', $params->get('captcha_id'));
			$form->setFieldAttribute('phq_captcha', 'validate', 'phocacartcaptcha');
		}

		return $form;
	}

	protected function loadFormData() {
		$data = (array) Factory::getApplication()->getUserState('com_phocacart.submit.data', array());
		return $data;
	}

	function store(&$data, $file)
	{


		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$uri = Uri::getInstance();
		$app = Factory::getApplication();
		$user = Factory::getUser();
		$path       = PhocacartPath::getPath('submititem');

		$params = PhocacartUtils::getComponentParameters();
		$submit_item_max_char_textarea 	= $params->get('submit_item_max_char_textarea', 5000);
		$submit_item_form_fields 		= $params->get('submit_item_form_fields', '');
		$items = array_map('trim', explode(',', $submit_item_form_fields));
		$items = array_unique($items);

		$submit_item_form_fields_contact = $params->get('submit_item_form_fields_contact', '');
		$itemsC = array_map('trim', explode(',', $submit_item_form_fields_contact));
		$itemsC = array_unique($itemsC);

		$submit_item_form_fields_parameters	= $params->get( 'submit_item_form_fields_parameters', '' );
		$itemsP = array_map('trim', explode(',', $submit_item_form_fields_parameters));
		$itemsP = array_unique($itemsP);




		// Maximum of character, they will be saved in database
		$data['items_item']['description'] = isset($data['items_item']['description']) ? substr($data['items_item']['description'], 0, $submit_item_max_char_textarea) : '';
		$data['items_item']['description_long'] = isset($data['items_item']['description_long']) ? substr($data['items_item']['description_long'], 0, $submit_item_max_char_textarea) : '';
		$data['items_item']['features'] = isset($data['items_item']['features']) ? substr($data['items_item']['features'], 0, $submit_item_max_char_textarea) : '';
		$data['items_item']['type_feed'] = isset($data['items_item']['type_feed']) ? substr($data['items_item']['type_feed'], 0, $submit_item_max_char_textarea) : '';
		$data['items_item']['type_category_feed'] = isset($data['items_item']['type_category_feed']) ? substr($data['items_item']['type_category_feed'], 0, $submit_item_max_char_textarea) : '';
		$data['items_item']['metakey'] = isset($data['items_item']['metakey']) ? substr($data['items_item']['metakey'], 0, $submit_item_max_char_textarea) : '';
		$data['items_item']['metadesc'] = isset($data['items_item']['metadesc']) ? substr($data['items_item']['metadesc'], 0, $submit_item_max_char_textarea) : '';
		$data['items_item']['message'] = isset($data['items_item']['message']) ? substr($data['items_item']['message'], 0, $submit_item_max_char_textarea) : '';


		$tempData = array();
		$tempData['ip'] = $data['ip'];
		$tempData['privacy'] = $data['privacy'];


		$item = array();
		$contact = array();
		$parameter = array();

		if (!empty($items)) {
			foreach ($items as $k => $v) {
				$v = trim(str_replace('*', '', $v));
				if (isset($data['items_item'][$v]) && $data['items_item'][$v] != '') {
					$item[$v] = $data['items_item'][$v];
				}
			}
		}



		if (!empty($itemsP)) {
			$parameters = PhocacartParameter::getAllParameters('alias');
			foreach ($itemsP as $k => $v) {
				$v = trim(str_replace('*', '', $v));
				$vId   	= 0;
                if (isset($parameters[$v]->id) && $parameters[$v]->id > 0) {
                    $vId = (int)$parameters[$v]->id;
                }
                if (!empty($data['items_parameter'][$vId])) {
                	$parameter[$vId] = $data['items_parameter'][$vId];
                }
			}
		}


		if (!empty($itemsC)) {
			foreach ($itemsC as $k => $v) {
				$v = trim(str_replace('*', '', $v));
				if (isset($data['items_contact'][$v]) && $data['items_contact'][$v] != '') {
					$contact[$v] = $data['items_contact'][$v];
				}
			}
		}




		$data = array();
		$data = $tempData;

		$data['items_item'] = $item;
		$data['items_contact'] = $contact;
		$data['items_parameter'] = $parameter;

		$data['date_submit'] = gmdate('Y-m-d H:i:s');   // Create the timestamp for the date
		$data['user_id'] 	= (int)$user->id;
		$data['title'] 		= isset($item['title']) && $item['title'] != '' ? $item['title'] : $data['date_submit'];
		$data['alias']		= PhocacartUtils::getAliasName($data['title']);
		$data['ordering']	= $this->increaseOrdering();
		$data['published']	= 1;

		$data['upload_token'] 			= PhocacartUtils::getToken();
		$data['upload_folder']			= PhocacartUtils::getToken('folder');


		$folderPath         = Path::clean($path['orig_abs_ds'] . $data['upload_folder']);

		// Images upload
		$fileData = array();
		if (!empty($file['items_item']['image'])) {

			if (!isset($file['items_item']['image'][0]) || (isset($file['items_item']['image'][0]) && $file['items_item']['image'][0]['error'] == 4)){

				// No file uploaded - is OK when not required
				// If requried - this will be checkedn in form field and in controller

			} else {
				$filesUploaded = PhocacartFileUpload::submitItemUpload($file['items_item']['image'], $data, $fileData, 'image');

				if (!$filesUploaded) {

					// message set in app
					if (Folder::exists($folderPath)) {
						Folder::delete($folderPath);
					}
					return false;

				} else {
					$data['items_item']['image'] = $fileData;
				}
			}


		}


		$dataItem 		= $data['items_item'];
		$dataContact 	= $data['items_contact'];
		$dataParameter	= $data['items_parameter'];


		$data['items_item'] = json_encode($dataItem);
		$data['items_contact'] = json_encode($dataContact);
		$data['items_parameter'] = json_encode($dataParameter);


		$row = $this->getTable('PhocaCartSubmitItem');


		if (!$row->bind($data)) {
			$this->setError($row->getError());
			if (Folder::exists($folderPath)) { Folder::delete($folderPath); }
			return false;
		}


		if (!$row->check()) {
			$this->setError($row->getError());
			if (Folder::exists($folderPath)) { Folder::delete($folderPath); }
			return false;
		}

		if (!$row->store()) {
			$this->setError($row->getError());
			if (Folder::exists($folderPath)) { Folder::delete($folderPath); }
			return false;
		}

		// Everything OK - send email
		if ($params->get('send_email_submit_item', 0) > 0 || $params->get('send_email_submit_item_others', '') != '') {

			$send = PhocacartEmail::sendSubmitItemMail($dataItem, $dataContact, $dataParameter, Uri::getInstance()->toString(), $params);

			if (!$send) {
				$user 	= PhocacartUser::getUser();
				PhocacartLog::add(2, 'Submit Item - ERROR - Problems with sending email', 0, 'IP: '. $data['ip'].', User ID: '.$user->id);
			}
		}

		$data['id'] = $row->id;

		return true;
	}

	protected function preprocessForm(Form $form, $data, $group = 'content'){


		// Load Parameter Values for Parameters
		$parameters = PhocacartParameter::getAllParameters();

		// Items and Items (Contact) are defined in view
		// Items (Parameters) will be defined here

		$pC 		= PhocacartUtils::getComponentParameters();

		// Items and Items (Contact) are defined in this view
		// Items (Parameters) will be defined model (when creating the form)

        // ITEMS
        // Preprocess form before saving - before validate the form - we need to set required fields so validate can check them
        $submit_item_form_fields 		= $pC->get('submit_item_form_fields', '');
        $submit_item_form_fields_contact = $pC->get('submit_item_form_fields_contact', '');

        $items = array();
        if($submit_item_form_fields != '') {
            $items = array_map('trim', explode(',', $submit_item_form_fields));
            $items = array_unique($items);
        }

        $itemsC = array();
        if($submit_item_form_fields_contact != '') {
            $itemsC = array_map('trim', explode(',', $submit_item_form_fields_contact));
            $itemsC = array_unique($itemsC);
        }


        $fieldSets = $form->getFieldsets();

        foreach ($fieldSets as $name => $fieldSet) {
            if (isset($fieldSet->name) && ($fieldSet->name == 'items_item' || $fieldSet->name == 'items_contact')) {
                foreach ($form->getFieldset($name) as $field) {

                    $itemsCurrent = array();
                    if ($fieldSet->name == 'items_item') {
                        $itemsCurrent = $items;
                    }
                    if ($fieldSet->name == 'items_contact') {
                        $itemsCurrent = $itemsC;
                    }

                    $isIncluded = 0;
                    if (in_array($field->fieldname . '*', $itemsCurrent)) {
                        $isIncluded = 2;// included and required
                    }

                    if ($isIncluded == 2) {

                        //$field->required = true;
                        //$field->addAttribute($field->fieldname, 'true');
                        //$field->__set('required', true);
                        // BE AWARE - GROUP NEEDS TO BE DEFINED
                        $form->setFieldAttribute($field->fieldname, 'required', 'true', $fieldSet->name);

                    }
                }
            }
        }

		$submit_item_form_fields_parameters	= $pC->get( 'submit_item_form_fields_parameters', '' );


		if($submit_item_form_fields_parameters != '') {
			$itemsP = array_map('trim', explode(',', $submit_item_form_fields_parameters));
			$itemsP = array_unique($itemsP);



			if (count($parameters) > 0 && !empty($itemsP)) {
				$addform = new SimpleXMLElement('<form />');
				$fields = $addform->addChild('fields');
				$fields->addAttribute('name', 'items_parameter');
				$fieldset = $fields->addChild('fieldset');
				$fieldset->addAttribute('name', 'items_parameter');

				foreach ($parameters as $k => $v) {

					$isIncluded = 0;
					if (in_array($v->alias, $itemsP)) {
						$isIncluded = 1;// included
					}
					if (in_array($v->alias . '*', $itemsP)) {
						$isIncluded = 2;// included and required
					}

					if ($isIncluded > 0) {

						$field = $fieldset->addChild('field');
						$field->addAttribute('name', $v->id);
						$field->addAttribute('parameterid', $v->id);
				        $field->addAttribute('parameteralias', $v->alias);
						$field->addAttribute('type', 'PhocaCartParameterValues');
						//$field->addAttribute('language', $language->lang_code);
						$field->addAttribute('label', $v->title);
						$field->addAttribute('class', 'chosen-select');
						$field->addAttribute('multiple', 'true');
						$field->addAttribute('translate_label', 'false');
						$field->addAttribute('select', 'true');
						$field->addAttribute('new', 'true');
						$field->addAttribute('edit', 'true');
						$field->addAttribute('clear', 'true');
						$field->addAttribute('propagate', 'true');
						$field->addAttribute('filter', 'int_array');
						if ($isIncluded == 2) {
							$field->addAttribute('required', 'true');
						}
					}
				}


				$form->load($addform, false);
			}
		}

		parent::preprocessForm($form, $data, $group);
	}

	public function increaseOrdering() {
		$this->_db->setQuery('SELECT MAX(ordering) FROM #__phocacart_submit_items');
		$max = $this->_db->loadResult();
		$ordering = $max + 1;
		return $ordering;
	}
}
?>
