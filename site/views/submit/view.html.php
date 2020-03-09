<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view');

class PhocaCartViewSubmit extends JViewLegacy
{
	protected $t;
	protected $p;
	protected $u;
	protected $s;
	protected $category;
	protected $item;
	protected $form;

	public function display($tpl = null) {

		$app								= JFactory::getApplication();
		$document							= JFactory::getDocument();
		$session 							= JFactory::getSession();

		$uri 								= \Joomla\CMS\Uri\Uri::getInstance();
		$this->t['action']					= $uri->toString();
		$this->t['actionbase64']			= base64_encode($this->t['action']);
		$this->u							= PhocacartUser::getUser();
		$this->s                            = PhocacartRenderStyle::getStyles();
		$this->p 							= $app->getParams();


		$this->t['enable_submit_item'] 		= $this->p->get('enable_submit_item', 0);

		if ($this->t['enable_submit_item'] == 0) {
			//throw new Exception(JText::_('COM_PHOCACART_SUBMIT_ITEM_DISABLED'), 500);
			$app->enqueueMessage(JText::_('COM_PHOCACART_SUBMIT_ITEM_DISABLED'), 'error');
			return false;
		}


		$this->t['submit_item_description'] = $this->p->get('submit_item_description', '');
		$this->t['submit_item_description'] = PhocacartRenderFront::renderArticle($this->t['submit_item_description']);
		if (PhocacartSubmit::isAllowedToSubmit()) {




			$this->t['submit_item_form_fields'] = $this->p->get('submit_item_form_fields', '');
			//$this->t['submit_item_form_fields'] = 'title, alias, catid_multiple, image, sku, upc, ean, jan, isbn, mpn, serial_number, registration_key, external_id, external_key, external_link, external_text, external_link2, external_text2, price, price_original, tax_id, manufacturer_id, description, description_long, features, video, type, unit_amount, unit_unit, length, width, height, weight, volume, condition, type_feed, type_category_feed, delivery_date, metatitle, metakey, metadesc, date, date_update, tags, taglabels';

			$this->t['items_item'] = array_map('trim', explode(',', $this->t['submit_item_form_fields']));
			$this->t['items_item'] = array_unique($this->t['items_item']);

			// Contact
			$this->t['submit_item_form_fields_contact'] = $this->p->get('submit_item_form_fields_contact', '');
			//$this->t['submit_item_form_fields_contact'] = 'name*, email*, phone, message';

			$this->t['items_contact'] = array_map('trim', explode(',', $this->t['submit_item_form_fields_contact']));
			$this->t['items_contact'] = array_unique($this->t['items_contact']);


			$this->t['submit_item_form_fields_parameters'] = $this->p->get('submit_item_form_fields_parameters', '');
			$this->t['items_parameter'] = array_map('trim', explode(',', $this->t['submit_item_form_fields_parameters']));
			$this->t['items_parameter'] = array_unique($this->t['items_parameter']);




			$this->t['enable_submit_item'] = $this->p->get('enable_submit_item', 0);
			if ($this->t['enable_submit_item'] == 0) {
				//throw new Exception(JText::_('COM_PHOCACART_SUBMIT_ITEM_DISABLED'), 403);
				$app->enqueueMessage(JText::_('COM_PHOCACART_SUBMIT_ITEM_DISABLED'), 'error');
				return false;
			}

			// Submit Item Privacy checkbox
			$this->t['display_submit_item_privacy_checkbox'] = $this->p->get('display_submit_item_privacy_checkbox', 0);
			if ($this->t['display_submit_item_privacy_checkbox'] > 0) {
				$this->t['submit_item_privacy_checkbox_label_text'] = $this->p->get('submit_item_privacy_checkbox_label_text', 0);
				$this->t['submit_item_privacy_checkbox_label_text'] = PhocacartRenderFront::renderArticle((int)$this->t['submit_item_privacy_checkbox_label_text'], 'html', '');
			}

			// Security
			$namespace = 'phccrt' . $this->p->get('session_suffix');
			$session->set('form_id', PhocacartUtils::getRandomString(mt_rand(6, 10)), $namespace);

			if ((int)$this->p->get('enable_time_check_submit_item', 0) > 0) {
				$sesstime = $session->get('time', time(), $namespace);
				$session->set('time', $sesstime, $namespace);
			}

			// Security Hidden Field
			if ($this->p->get('enable_hidden_field_submit_item', 0) == 1) {

				$this->p->set('hidden_field_position', rand(1, 5));
				$session->set('hidden_field_id', 'hf' . PhocacartUtils::getRandomString(mt_rand(6, 10)), $namespace);
				$session->set('hidden_field_name', 'hf' . PhocacartUtils::getRandomString(mt_rand(6, 10)), $namespace);
				$session->set('hidden_field_class', 'pc' . PhocacartUtils::getRandomString(mt_rand(6, 10)), $namespace);

				$this->p->set('hidden_field_id', $session->get('hidden_field_id', '', $namespace));
				$this->p->set('hidden_field_name', $session->get('hidden_field_name', '', $namespace));
				$this->p->set('hidden_field_class', $session->get('hidden_field_class', '', $namespace));

				$document->addCustomTag('<style type="text/css"> .' . $this->p->get('hidden_field_class') . ' { ' . "\n\t" . 'display: none !important;' . "\n" . '}</style>');
			} else {
				$this->p->set('hidden_field_position', -1);
			}

			$tmpl = $app->input->get('tmpl', '', 'string');

			if ($tmpl == 'component') {

				$buffer = JFactory::getApplication()->sendHeaders();

				$document->addCustomTag("<style type=\"text/css\"> \n"
					. " #ph-pc-question-box {
					margin: 20px
				} \n"
					. " </style> \n");
			}

			$this->form = $this->get('Form');
		}

		$media = new PhocacartRenderMedia();
		$media->loadBase();
		$media->loadChosen();
		//$media->loadFileInput();

		$media->loadSpec();
		$this->_prepareDocument();

		parent::display($tpl);
	}

	protected function _prepareDocument() {

		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, JText::_('COM_PHOCACART_SUBMIT_ITEM'));
	}
}
?>
