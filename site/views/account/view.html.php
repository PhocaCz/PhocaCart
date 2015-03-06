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

class PhocaCartViewAccount extends JViewLegacy
{
	protected $t;
	protected $p;
	protected $u;
	protected $fields2;
	protected $data2;
	protected $form2;
	
	// User profile
	protected $params;
	protected $fields;
	protected $data;
	protected $form;
	protected $state;
	protected $twofactorform;
	protected $twofactormethods;
	protected $otpConfig;

	function display($tpl = null)
	{		
		$app								= JFactory::getApplication();
		$this->u							= JFactory::getUser();
		$uri 								= JFactory::getURI();
		$document							= JFactory::getDocument();
		$this->p 							= $app->getParams();
		$model								= $this->getModel();
		
		$this->t['action']					= $uri->toString();
		$this->t['actionbase64']			= base64_encode($this->t['action']);
		$this->t['linkaccount']				= JRoute::_(PhocaCartRoute::getAccountRoute());
		$this->t['linkcheckout']			= JRoute::_(PhocaCartRoute::getCheckoutRoute());
		
		$this->t['load_bootstrap']			= $this->p->get( 'load_bootstrap', 0 );
		$this->t['display_edit_profile']	= $this->p->get( 'display_edit_profile', 1 );
		
		JHTML::stylesheet('media/com_phocacart/css/main.css' );
		if ($this->t['load_bootstrap'] == 1) {
			JHTML::stylesheet('media/com_phocacart/bootstrap/css/bootstrap.min.css' );
			$document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
		}
		
		$lang = JFactory::getLanguage();
		//$lang->load('com_users.sys');
		$lang->load('com_users');
		
		
		if ((int)$this->u->id > 0) {
			// Checkout Model
			jimport('joomla.application.component.model');
			JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_phocacart/models');
			$modelCheckout = JModelLegacy::getInstance( 'Checkout', 'PhocaCartModel' );

			// Check if all form items are filled out by user, if yes, don't load the form and save some queries
			$this->fields2					= $modelCheckout->getFields(0,0,1); // Fields will be loaded in every case
			$this->data2					= $modelCheckout->getData();
			$this->form2					= $modelCheckout->getForm();
			$this->t['dataaddressform']	= PhocaCartUser::getAddressDataForm($this->form2, $this->fields2['array'], $this->u);
			
		
			// USER PROFILE - USER MODULE
			jimport('joomla.application.component.model');
			//JLoader::import('user',JPATH_SITE.DS.'components'.DS.'com_users'.DS .'models');
			JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_users/models');
			$modelUsers = JModelLegacy::getInstance( 'Profile', 'UsersModel' );
			$this->data	            = $modelUsers->getData();
			$loadformpath = JPATH_SITE.'/components/com_users/models';
			JForm::addFormPath($loadformpath.'/forms');
			JForm::addFieldPath($loadformpath.'/fields');
			$this->form	  = $modelUsers->getForm();
		
			
			
			$this->state            = $modelUsers->getState();
			$this->params           = $this->state->get('params');
			$this->twofactorform    = $modelUsers->getTwofactorform();
			$this->twofactormethods = UsersHelper::getTwoFactorMethods();
			$this->otpConfig        = $modelUsers->getOtpConfig();
			$this->data->tags 		= new JHelperTags;
			$this->data->tags->getItemTags('com_users.user.', $this->data->id);
			
		}
		
		//CHOSEN
		$document->addScript(JURI::root(true).'/media/com_phocacart/bootstrap/js/bootstrap.min.js');
		$document->addScript(JURI::root(true).'/media/com_phocacart/js/chosen/chosen.jquery.min.js');
		$js = "\n". 'jQuery(document).ready(function(){';
		$js .= '   jQuery(".chosen-select").chosen({disable_search_threshold: 10});'."\n"; // Set chosen, created hidden will be required
		$js .= '   jQuery(".chosen-select").attr(\'style\',\'display:visible; position:absolute; clip:rect(0,0,0,0)\');'."\n";
		$js .= '});'."\n";
		$document->addScriptDeclaration($js);
		JHTML::stylesheet( 'media/com_phocacart/js/chosen/chosen.css' );
		JHTML::stylesheet( 'media/com_phocacart/js/chosen/chosen-bootstrap.css' );
		
		PhocaCartRenderJs::renderBillingAndShippingSame();
		
		$this->_prepareDocument();
		parent::display($tpl);
		
	}
	
	protected function _prepareDocument() {
		PhocaCartRenderFront::prepareDocument($this->document, $this->p);
	}
}
?>