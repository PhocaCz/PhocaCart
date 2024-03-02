<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocaCartCpViewPhocaCartItem extends HtmlView
{
	protected $state;
	protected $item;
	protected $form;
	protected $t;
	protected $r;
	protected $p;
	protected $attributes;

	public function display($tpl = null) {

		$this->t		= PhocacartUtils::setVars('item');
		$this->r		= new PhocacartRenderAdminview();
		$this->state	= $this->get('State');
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');


		$params    = PhocacartUtils::getComponentParameters();
		$media = new PhocacartRenderAdminmedia();

		// AI
		$app = Factory::getApplication();
        $wa = $app->getDocument()->getWebAssetManager();
		$wa->registerAndUseScript('com_phocacart.phocacartai', 'media/com_phocacart/js/phoca/phocacartai.js', array('version' => 'auto'));
		$config = Factory::getConfig();
		$editor = $config->get('editor');

		$oVars['editor'] = $editor;

		$oLang['COM_PHOCACART_ERROR_YOU_DID_NOT_PROVIDE_API_KEY'] = Text::_('COM_PHOCACART_ERROR_YOU_DID_NOT_PROVIDE_API_KEY');
		$oLang['COM_PHOCACART_SUCCESS_CONTENT_INSERTED'] = Text::_('COM_PHOCACART_SUCCESS_CONTENT_INSERTED');
		$oLang['COM_PHOCACART_DESTINATION_FIELD_NOT_EMPTY_PRESS_OK_TO_OVERWRITE_CONTENTS_IN_DESTINATION_FIELD'] = Text::_('COM_PHOCACART_DESTINATION_FIELD_NOT_EMPTY_PRESS_OK_TO_OVERWRITE_CONTENTS_IN_DESTINATION_FIELD');

		$oParams['aiApiKey'] = $params->get('ai_apikey', '');
		$oParams['aiModel'] = $params->get('ai_model', 'text-davinci-003');

		$oParams['ai_parameters_description'] = $params->get('ai_parameters_description', '{"max_tokens": 300, "temperature": 0.5}');
		$oParams['ai_premade_question_description'] = $params->get('ai_premade_question_description', 'Create a product description based on the following information');

		$oParams['ai_parameters_description_long'] = $params->get('ai_parameters_description_long', '{"max_tokens": 2048, "temperature": 0.5}');
		$oParams['ai_premade_question_description_long'] = $params->get('ai_premade_question_description_long', 'Create a very comprehensive description of the product based on the following information');

		$oParams['ai_parameters_features'] = $params->get('ai_parameters_features', '{"max_tokens": 2048, "temperature": 0.5}');
		$oParams['ai_premade_question_features'] = $params->get('ai_premade_question_features', 'Create a very comprehensive feature list of the product based on the following information');

		$oParams['ai_parameters_metadesc'] = $params->get('ai_parameters_metadesc', '{"max_tokens": 50, "temperature": 0.5}');
		$oParams['ai_premade_question_metadesc'] = $params->get('ai_premade_question_metadesc', 'Create a very short description of the product based on the following information');

        $this->document->addScriptOptions('phLangPC', $oLang);
        $this->document->addScriptOptions('phVarsPC', $oVars);
        $app->getDocument()->addScriptOptions('phParamsPC', $oParams);




		//$url = 'index.php?option=com_phocacart&view=phocacartthumba&format=json&tmpl=component&'. JSession::getFormToken().'=1';

		// FIELD: IMAGE, ADDITIONAL IMAGE
		// EVENT - ONCHANGE - 1) create thumbnails
		//PhocacartRenderAdminjs::phEventCreateImageThumbnail($url, Text::_('COM_PHOCACART_CHECKING_IMAGE_THUMBNAIL_PLEASE_WAIT'), 'productimage', 'imageCreateThumbs');
		// EVENT - ONCLICK - 1) paste selected image 2) create thumbnails
		//PhocacartRenderAdminjs::phAddValueImage($url, Text::_('COM_PHOCACART_CHECKING_IMAGE_THUMBNAIL_PLEASE_WAIT'), 'productimage');

		// FIELD: FILE (DOWNLOAD), ADDITIONAL FILES (DOWNLOAD), PUBLIC DOWNLOAD FILE
		// EVENT - ONCLICK - 1) paste selected image 2) create thumbnails
		//

		// Attribute Option
		if ((int)$this->item->id > 0) {
			//$this->attributes					= PhocacartAttribute::getAttributesById((int)$this->item->id);
			//$this->specifications				= PhocacartSpecification::getSpecificationsById((int)$this->item->id);
			//$this->discounts					= PhocacartDiscountProduct::getDiscountsById((int)$this->item->id);
			//$this->additional_images 			= PhocacartImageAdditional::getImagesByProductId((int)$this->item->id);

		}

		// ASSOCIATION
		// If we are forcing a language in modal (used for associations).
		if ($this->getLayout() === 'modal' && $forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'cmd')) {
			// Set the language field to the forcedLanguage and disable changing it.
			$this->form->setValue('language', null, $forcedLanguage);
			$this->form->setFieldAttribute('language', 'readonly', 'true');

			// Only allow to select categories with All language or with the forced language.
			$this->form->setFieldAttribute('catid_multiple', 'language', '*,' . $forcedLanguage);

			// Possible FR - add tags (including modifying tag field - to filter language)
			// Only allow to select tags with All language or with the forced language.
			//$this->form->setFieldAttribute('tags', 'language', '*,' . $forcedLanguage);
		}


		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {

		require_once JPATH_COMPONENT.'/helpers/'.$this->t['tasks'].'.php';
		Factory::getApplication()->input->set('hidemainmenu', true);
		$bar 		= Toolbar::getInstance('toolbar');
		$user		= Factory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$class		= ucfirst($this->t['tasks']).'Helper';
		$canDo		= $class::getActions($this->t, $this->state->get('filter.category_id'));

		$text = $isNew ? Text::_( $this->t['l'] . '_NEW' ) : Text::_($this->t['l'] . '_EDIT');
		ToolbarHelper::title(   Text::_( $this->t['l'] . '_PRODUCT' ).': <small><small>[ ' . $text.' ]</small></small>' , 'archive');

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit')){
			ToolbarHelper::apply($this->t['task'] . '.apply', 'JTOOLBAR_APPLY');
			ToolbarHelper::save($this->t['task'] . '.save', 'JTOOLBAR_SAVE');
			ToolbarHelper::addNew($this->t['task'] . '.save2new', 'JTOOLBAR_SAVE_AND_NEW');

		}
		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			//JToolbarHelper::custom($this->t.'.save2copy', 'copy.png', 'copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}


		if (!$isNew && I18nHelper::associationsEnabled() && ComponentHelper::isEnabled('com_associations')) {
			ToolbarHelper::custom($this->t['task'] . '.editAssociations', 'contract', 'contract', 'JTOOLBAR_ASSOCIATIONS', false, false);
		}

		if (empty($this->item->id))  {
			ToolbarHelper::cancel($this->t['task'] . '.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			ToolbarHelper::cancel($this->t['task'] . '.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
		ToolbarHelper::inlinehelp();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );

		PhocacartRenderAdminview::renderWizardButton('back');
	}
}
