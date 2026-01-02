<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

jimport( 'joomla.application.component.view');

class PhocaCartViewAccount extends HtmlView
{
	protected $t;
	protected $r;
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
		$app								= Factory::getApplication();
		$this->u							= PhocacartUser::getUser();
		$uri 								= Uri::getInstance();
		$document							= Factory::getDocument();
		$this->p 							= $app->getParams();
		$this->s                            = PhocacartRenderStyle::getStyles();
		$model								= $this->getModel();

		$this->t['action']							= $uri->toString();
		$this->t['actionbase64']					= base64_encode($this->t['action']);
		$this->t['linkaccount']						= Route::_(PhocacartRoute::getAccountRoute());
		$this->t['linkcheckout']					= Route::_(PhocacartRoute::getCheckoutRoute());
		$this->t['display_edit_profile']			= $this->p->get( 'display_edit_profile', 1 );
		$this->t['display_reward_points_total_info']= $this->p->get( 'display_reward_points_total_info', 0 );
		$this->t['delivery_billing_same_enabled']  = $this->p->get('delivery_billing_same_enabled', 0);

		$lang = Factory::getLanguage();
		//$lang->load('com_users.sys');
		$lang->load('com_users');

		$this->t['datauser'] = [];
		if ((int)$this->u->id > 0) {
			// Checkout Model
			jimport('joomla.application.component.model');
			BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_phocacart/models');
			$modelCheckout 				= BaseDatabaseModel::getInstance( 'Checkout', 'PhocaCartModel' );

			// Check if all form items are filled out by user, if yes, don't load the form and save some queries
			$this->fields2				= $modelCheckout->getFields(0,0,1); // Fields will be loaded in every case
			$this->data2				= $modelCheckout->getData();
			$this->form2				= $modelCheckout->getForm();
			$this->t['dataaddressform']	= PhocacartUser::getAddressDataForm($this->form2, $this->fields2['array'], $this->u);
			$this->t['datauser']		= $this->data2;

			// USER PROFILE - USER MODULE
		/*	jimport('joomla.application.component.model');
			//JLoader::import('user',JPATH_SITE.'/components/com_users/models');
			BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_users/models');
			$modelUsers 			= BaseDatabaseModel::getInstance( 'Profile', 'UsersModel' );

			$modelUsers 			=Factory::getApplication()->bootComponent('com_users')->getMVCFactory()->createModel('Profile', 'Site', ['ignore_request' => true]);


			$this->data	            = $modelUsers->getData();
			$loadformpath 			= JPATH_SITE.'/components/com_users';
			Form::addFormPath($loadformpath.'/forms');
			Form::addFieldPath($loadformpath.'/fields');
			$this->form	  			= $modelUsers->getForm();*/



			Form::addFormPath(JPATH_SITE .  '/components/com_users/forms');
			Form::addFieldPath(JPATH_SITE .  '/components/com_users/fields');
			$modelUsers = $app->bootComponent('com_users')->getMvcFactory()->createModel('Profile', 'Site', ['ignore_request' => false]);


			//BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_users/models');
			//$modelUsers 			= BaseDatabaseModel::getInstance( 'Profile', 'UsersModel' );

			$this->form = $modelUsers->getForm();

			$this->data	            = $modelUsers->getData();
			$this->state            = $modelUsers->getState();
			$this->params           = $this->state->get('params');
			$this->twofactorform    = $modelUsers->getTwofactorform();
			$this->twofactormethods = AuthenticationHelper::getTwoFactorMethods();
			$this->otpConfig        = $modelUsers->getOtpConfig();
			$this->data->tags 		= new TagsHelper;
			$this->data->tags->getItemTags('com_users.user.', $this->data->id);


			// REWARD POINTS
			$reward = new PhocacartReward();
			$this->t['rewardpointstotal'] = $reward->getTotalPointsByUserId((int)$this->u->id);


			// SUBSCRIPTIONS
			$this->t['subscriptions'] = PhocacartSubscription::getSubscriptions((int)$this->u->id);
            $this->t['subscription_description_account_view'] = '';
            // Get plugin parameters
            $plugin = PluginHelper::getPlugin('system', 'phocacartsubscription');
            if ($plugin && isset($plugin->params)) {
                $params = new Registry($plugin->params);
                $this->t['subscription_description_account_view'] = $params->get('subscription_description_account_view', '');
                $this->t['subscription_description_account_view'] = HTMLHelper::_('content.prepare', $this->t['subscription_description_account_view']);
            }
		}

		$media = PhocacartRenderMedia::getInstance('main');
		$media->loadBase();
		$media->loadChosen();
		//- PhocacartRenderJs::renderBillingAndShippingSame();
        $media->loadSpec();

		$this->_prepareDocument();
		parent::display($tpl);

	}

	protected function _prepareDocument() {
		PhocacartRenderFront::prepareDocument($this->document, $this->p, false, false, Text::_('COM_PHOCACART_ACCOUNT'));
	}
}
?>
