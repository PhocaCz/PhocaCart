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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class PhocaCartControllerItem extends FormController
{
	public function review() {

		Session::checkToken() or jexit( 'Invalid Token' );
		//$paramsC 			= PhocacartUtils::getComponentParameters();
		$app				= Factory::getApplication();
		$paramsC 			= $app->getParams();
		$approve_review 	= $paramsC->get( 'approve_review',0 );
		$u					= PhocacartUser::getUser();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['catid']		= $this->input->get( 'catid', 0, 'int' );
		$item['rating']		= $this->input->get( 'rating', 0, 'int'  );
		$item['name']		= $this->input->get( 'name', 0, 'string'  );
		$item['review']		= $this->input->get( 'review', 0, 'string'  );
		$item['return']		= $this->input->get( 'return', '', 'string'  );

		$errMsg = array();// Error message in this controller
		if ((int)$item['rating'] < 1) {
			$errorMsg[] = Text::_('COM_PHOCACART_PLEASE_ADD_RATING');
		}
		if ($item['name'] == '') {
			$errorMsg[] = Text::_('COM_PHOCACART_PLEASE_ADD_YOUR_NAME');
		}
		if ($item['review'] == '') {
			$errorMsg[] = Text::_('COM_PHOCACART_PLEASE_ADD_YOUR_REVIEW');
		}
		if (!empty($errorMsg)) {
			$app->enqueueMessage(implode( '<br />', $errorMsg ), 'warning');
			$app->redirect(base64_decode($item['return']));
		}

		$error = 0;// Error message from database
		$added = PhocacartReview::addReview($error, $approve_review, $item['id'], $u->id, $item['name'], $item['rating'], $item['review']);

		if ($added) {
			$msg = Text::_('COM_PHOCACART_THANK_YOU_FOR_YOUR_REVIEW');
			if ($approve_review == 1) {
				$msg .= '. '. Text::_('COM_PHOCACART_REVIEW_NEED_TO_BE_APPROVED_BEFORE_DISPLAYING').'.';
			}
			$app->enqueueMessage($msg, 'message');
		} else {
			if ($error == 1) {
				$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_YOU_HAVE_ALREADY_REVIEWED_THIS_PRODUCT'), 'warning');
			} else {
				$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_REVIEW_NOT_ADDED'), 'warning');
			}
		}
		//$app->redirect(Route::_('index.php?option=com_phocacart&view=checkout'));
		$app->redirect(base64_decode($item['return']));
	}
}
?>
