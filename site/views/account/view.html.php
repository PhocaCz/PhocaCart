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
	protected $s;
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
		$this->u							= PhocacartUser::getUser();
		$uri 								= \Joomla\CMS\Uri\Uri::getInstance();
		$document							= JFactory::getDocument();
		$this->p 							= $app->getParams();
		$this->s                            = PhocacartRenderStyle::getStyles();
		$model								= $this->getModel();

		$this->t['action']							= $uri->toString();
		$this->t['actionbase64']					= base64_encode($this->t['action']);
		$this->t['linkaccount']						= JRoute::_(PhocacartRoute::getAccountRoute());
		$this->t['linkcheckout']					= JRoute::_(PhocacartRoute::getCheckoutRoute());
		$this->t['display_edit_profile']			= $this->p->get( 'display_edit_profile', 1 );
		$this->t['display_reward_points_total_info']= $this->p->get( 'display_reward_points_total_info', 0 );

		$lang = JFactory::getLanguage();
		//$lang->load('com_users.sys');
		$lang->load('com_users');


		if ((int)$this->u->id > 0) {
			// Checkout Model
			jimport('joomla.application.component.model');
			JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_phocacart/models');
			$modelCheckout 				= JModelLegacy::getInstance( 'Checkout', 'PhocaCartModel' );

			// Check if all form items are filled out by user, if yes, don't load the form and save some queries
			$this->fields2				= $modelCheckout->getFields(0,0,1); // Fields will be loaded in every case
			$this->data2				= $modelCheckout->getData();
			$this->form2				= $modelCheckout->getForm();
			$this->t['dataaddressform']	= PhocacartUser::getAddressDataForm($this->form2, $this->fields2['array'], $this->u);


			// USER PROFILE - USER MODULE
			jimport('joomla.application.component.model');
			//JLoader::import('user',JPATH_SITE.'/components/com_users/models');
			JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_users/models');
			$modelUsers 			= JModelLegacy::getInstance( 'Profile', 'UsersModel' );
			$this->data	            = $modelUsers->getData();
			$loadformpath 			= JPATH_SITE.'/components/com_users/models';
			JForm::addFormPath($loadformpath.'/forms');
			JForm::addFieldPath($loadformpath.'/fields');
			$this->form	  			= $modelUsers->getForm();

			$this->state            = $modelUsers->getState();
			$this->params           = $this->state->get('params');
			$this->twofactorform    = $modelUsers->getTwofactorform();
			$this->twofactormethods = UsersHelper::getTwoFactorMethods();
			$this->otpConfig        = $modelUsers->getOtpConfig();
			$this->data->tags 		= new JHelperTags;
			$this->data->tags->getItemTags('com_users.user.', $this->data->id);

			// REWARD POINTS
			$reward = new PhocacartReward();
			$this->t['rewardpointstotal'] = $reward->getTotalPointsByUserId((int)$this->u->id);


		}

		$media = new PhocacartRenderMedia();
		$media->loadBase();
		$media->loadChosen();
		PhocacartRenderJs::renderBillingAndShippingSame();
        $media->loadSpec();

		$this->_prepareDocument();
		parent::display($tpl);

	}

	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, JText::_('COM_PHOCACART_ACCOUNT'));
	}
}
?>
