<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Form\Form;

class PhocaCartControllerAccount extends FormController
{

	public function saveprofile()
	{
		// Check for request forgeries.
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

		$app	= Factory::getApplication();
		$model	= $this->getModel('Profile', 'UsersModel');
		$user	= PhocacartUser::getUser();
		$userId	= (int) $user->get('id');

		// Get the user data.
		$data 		= $app->input->post->get('jform', array(), 'array');
		$returnUrl 	= $app->input->post->get('return', '', 'string');


		$lang = Factory::getLanguage();
		//$lang->load('com_users.sys');
		$lang->load('com_users');

		// PHOCAEDIT
		//jimport('joomla.application.component.model');
		//JLoader::import('user',JPATH_SITE.'/components/com_users/models');
		//BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_users/models');
		//$model = BaseDatabaseModel::getInstance( 'Profile', 'UsersModel' );
		//$this->data	  = $model->getData();
		//$loadformpath = JPATH_SITE.'/components/com_users/models';
		//Form::addFormPath($loadformpath.'/forms');
		//Form::addFieldPath($loadformpath.'/fields');
		//$this->form	  = $model->getForm();

		Form::addFormPath(JPATH_SITE .  '/components/com_users/forms');
		Form::addFieldPath(JPATH_SITE .  '/components/com_users/fields');
		$model = $app->bootComponent('com_users')->getMvcFactory()->createModel('Profile', 'Site', ['ignore_request' => false]);


		//BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_users/models');
		//$modelUsers 			= BaseDatabaseModel::getInstance( 'Profile', 'UsersModel' );

		$this->form = $modelUsers->getForm();

		$this->data	            = $modelUsers->getData();
		/*$this->state            = $modelUsers->getState();
		$this->params           = $this->state->get('params');
		$this->twofactorform    = $modelUsers->getTwofactorform();
		$this->twofactormethods = AuthenticationHelper::getTwoFactorMethods();
		$this->otpConfig        = $modelUsers->getOtpConfig();
		$this->data->tags 		= new TagsHelper;
		$this->data->tags->getItemTags('com_users.user.', $this->data->id);*/


		// Force the ID to this user.
		$data['id'] = $userId;

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			throw new Exception($model->getError(), 500);
			return false;
		}

		// Validate the posted data.
		$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_users.edit.profile.data', $data);

			// Redirect back to the edit screen.
			$userId = (int) $app->getUserState('com_users.edit.profile.id');
			$this->setRedirect(base64_decode($returnUrl));
			return false;
		}

		// Attempt to save the data.
		$return	= $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_users.edit.profile.data', $data);

			// Redirect back to the edit screen.
			$userId = (int) $app->getUserState('com_users.edit.profile.id');
			$this->setMessage(Text::sprintf('COM_USERS_PROFILE_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(base64_decode($returnUrl));
			return false;
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($this->getTask())
		{
			case 'apply':
				// Check out the profile.
				$app->setUserState('com_users.edit.profile.id', $return);
				$model->checkout($return);

				// Redirect back to the edit screen.
				$this->setMessage(Text::_('COM_USERS_PROFILE_SAVE_SUCCESS'));
				$this->setRedirect(base64_decode($returnUrl));
				break;

			default:
				// Check in the profile.
				$userId = (int) $app->getUserState('com_users.edit.profile.id');
				if ($userId)
				{
					$model->checkin($userId);
				}

				// Clear the profile id from the session.
				$app->setUserState('com_users.edit.profile.id', null);

				// Redirect to the list screen.
				$this->setMessage(Text::_('COM_USERS_PROFILE_SAVE_SUCCESS'));
				$this->setRedirect(base64_decode($returnUrl));
				break;
		}

		// Flush the data from the session.
		$app->setUserState('com_users.edit.profile.data', null);
	}

}
?>
