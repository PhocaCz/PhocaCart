<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocaCartItem extends PhocaCartCpControllerPhocaCartCommon
{


	public function batch($model = null) {
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$model	= $this->getModel('phocacartitem', '', array());
		$this->setRedirect(Route::_('index.php?option=com_phocacart&view=phocacartitems'.$this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	public function copyattributes($model = null) {
		Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));
		$model	= $this->getModel('phocacartitem', '', array());
		$app	= Factory::getApplication();
		$cid 		= Factory::getApplication()->input->get( 'cid', array(),'array' );
		$idSource 	= Factory::getApplication()->input->get( 'copy_attributes', 0, 'int' );
		ArrayHelper::toInteger($cid);


		if(!$model->copyattributes($cid, $idSource)) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_ATTRIBUTES_NOT_COPIED'), 'error');
		} else {
			$app->enqueueMessage(Text::_('COM_PHOCACART_SUCCESS_ATTRIBUTES_COPIED'), 'message');
		}
		$this->setRedirect(Route::_('index.php?option=com_phocacart&view=phocacartitems'.$this->getRedirectToListAppend(), false));
	}


	function recreate() {
		$app	= Factory::getApplication();
		$cid 	= Factory::getApplication()->input->get( 'cid', array(), '', 'array' );
		ArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			$message = Text::_( 'COM_PHOCACART_SELECT_ITEM_RECREATE' );
			$app->enqueueMessage($message, 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacartitems');
		}
		$message = '';
		$model = $this->getModel( 'phocacartitem' );
		if(!$model->recreate($cid, $message)) {
			$message = PhocacartUtils::setMessage($message, Text::_( 'COM_PHOCACART_ERROR_THUMBS_REGENERATING' ));
			$app->enqueueMessage($message, 'error');
		} else {
			//$message = Text::_( 'COM_PHOCACART_SUCCESS_THUMBS_REGENERATING' );
			$message = PhocacartUtils::setMessage($message, Text::_( 'COM_PHOCACART_SUCCESS_THUMBS_REGENERATING' ));
			$app->enqueueMessage($message, 'message');
		}

		$app->redirect('index.php?option=com_phocacart&view=phocacartitems');
	}


	/*
	function removeduplicates() {

		$app	= Factory::getApplication();
		if (PhocacartCategoryMultiple::removeDuplicates()) {
			$message = Text::_( 'COM_PHOCACART_SUCCESS_DUPLICATES_REMOVED' );
		} else {
			$message = Text::_( 'COM_PHOCACART_ERROR_DUPLICATES_NOT_REMOVED' );
		}
		$app->enqueueMessage($message, 'message');
		$app->redirect('index.php?option=com_phocacart&view=phocacartitems');
	}*/

}
?>
