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

class PhocaCartViewQuestion extends JViewLegacy
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
		$this->t['question_description']	= $this->p->get( 'question_description', '' );
		$this->t['question_description']	= PhocacartRenderFront::renderArticle($this->t['question_description']);

		$this->t['enable_ask_question'] 	= $this->p->get('enable_ask_question', 0);
		if ($this->t['enable_ask_question'] == 0) {
			//throw new Exception(JText::_('COM_PHOCACART_ASK_QUESTION_DISABLED'), 403);
			$app->enqueueMessage(JText::_('COM_PHOCACART_ASK_QUESTION_DISABLED'), 'error');
			return false;
		}

		// Ask Question Privacy checkbox
		$this->t['display_question_privacy_checkbox']	= $this->p->get( 'display_question_privacy_checkbox', 0 );
		if ($this->t['display_question_privacy_checkbox'] > 0) {
			$this->t['question_privacy_checkbox_label_text']	= $this->p->get( 'question_privacy_checkbox_label_text', 0 );
			$this->t['question_privacy_checkbox_label_text'] 	= PhocacartRenderFront::renderArticle((int)$this->t['question_privacy_checkbox_label_text'], 'html', '');
		}

		// Security
		$namespace  = 'phccrt' . $this->p->get('session_suffix');
		$session->set('form_id', PhocacartUtils::getRandomString(mt_rand(6,10)), $namespace);

		if((int)$this->p->get('enable_time_check_question', 0) > 0) {
			$sesstime = $session->get('time', time(), $namespace);
			$session->set('time', $sesstime, $namespace);
		}

		// Security Hidden Field
		if ($this->p->get('enable_hidden_field_question', 0) == 1) {
			$this->p->set('hidden_field_position', PhocacartSecurity::setHiddenFieldPos($this->p->get('display_name_form'), $this->p->get('display_email_form'), $this->p->get('display_phone_form'), $this->p->get('display_message_form')));

			$session->set('hidden_field_id', 'hf'.PhocacartUtils::getRandomString(mt_rand(6,10)), $namespace);
			$session->set('hidden_field_name', 'hf'.PhocacartUtils::getRandomString(mt_rand(6,10)), $namespace);
			$session->set('hidden_field_class', 'pc'.PhocacartUtils::getRandomString(mt_rand(6,10)), $namespace);

			$this->p->set('hidden_field_id', $session->get('hidden_field_id', '', $namespace));
			$this->p->set('hidden_field_name', $session->get('hidden_field_name', '', $namespace));
			$this->p->set('hidden_field_class', $session->get('hidden_field_class', '', $namespace));

			$document->addCustomTag('<style type="text/css"> .'.$this->p->get('hidden_field_class').' { '."\n\t".'display: none !important;'."\n".'}</style>');
		} else {
			$this->p->set('hidden_field_position', -1);
		}


		$id						= $app->input->get('id', 0, 'int');
		$catid					= $app->input->get('catid', 0, 'int');
		$tmpl					= $app->input->get('tmpl', '', 'string');


		if ($id > 0 && $catid > 0) {
			//$modelP	= $this->getModel('Item', 'PhocaCartModel');
			jimport('joomla.application.component.model');
			JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_phocacart/models');
			$modelP = JModelLegacy::getInstance( 'Item', 'PhocaCartModel' );

			$this->category			= $modelP->getCategory($id, $catid);

			$this->item				= $modelP->getItem($id, $catid);
			$this->t['catid']		= 0;
			if (isset($this->category[0]->id)) {
				$this->t['catid']	= (int)$this->category[0]->id;
			}

		}

		if ($tmpl == 'component') {

			$buffer = JFactory::getApplication()->sendHeaders();

			$document->addCustomTag( "<style type=\"text/css\"> \n"
			." #ph-pc-question-box {
				margin: 20px
			} \n"
			." </style> \n");
		}

		$this->t['pathitem'] = PhocacartPath::getPath('productimage');

		$this->form		= $this->get('Form');

		if (!empty($this->form) && $id > 0) {
			$this->form->setValue('product_id', null, (int)$id);
		}
		if (!empty($this->form) && $catid > 0) {
			$this->form->setValue('category_id', null, (int)$catid);
		}


		$media = PhocacartRenderMedia::getInstance('main');
		$media->loadBase();
		$media->loadChosen();

		$media->loadSpec();
		$this->_prepareDocument();

		parent::display($tpl);
	}

	protected function _prepareDocument() {

		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, JText::_('COM_PHOCACART_ASK_A_QUESTION'));
	}
}
?>
