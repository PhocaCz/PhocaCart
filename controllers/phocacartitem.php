<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocaCartItem extends PhocaCartCpControllerPhocaCartCommon
{
	public function batch($model = null) {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model	= $this->getModel('phocacartitem', '', array());
		$this->setRedirect(JRoute::_('index.php?option=com_phocacart&view=phocacartitems'.$this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}
	
	public function copyattributes($model = null) {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model	= $this->getModel('phocacartitem', '', array());
		$app	= JFactory::getApplication();
		$cid 		= JFactory::getApplication()->input->get( 'cid', array(),'array' );
		$idSource 	= JFactory::getApplication()->input->get( 'copy_attributes', 0, 'int' );
		JArrayHelper::toInteger($cid);
		

		if(!$model->copyattributes($cid, $idSource)) {
			$app->enqueueMessage(JText::_('COM_PHOCACART_ERROR_ATTRIBUTES_NOT_COPIED'), 'error');
		} else {
			$app->enqueueMessage(JText::_('COM_PHOCACART_SUCCESS_ATTRIBUTES_COPIED'), 'message');
		}
		$this->setRedirect(JRoute::_('index.php?option=com_phocacart&view=phocacartitems'.$this->getRedirectToListAppend(), false));
	}
	
	
	function recreate() {
		$app	= JFactory::getApplication();
		$cid 	= JFactory::getApplication()->input->get( 'cid', array(), '', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			$message = JText::_( 'COM_PHOCACART_SELECT_ITEM_RECREATE' );
			$app->enqueueMessage($message, 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacartcategories');
		}
		$message = '';
		$model = $this->getModel( 'phocacartitem' );
		if(!$model->recreate($cid, $message)) {
			$message = PhocacartUtils::setMessage($message, JText::_( 'COM_PHOCACART_ERROR_THUMBS_REGENERATING' ));
			$app->enqueueMessage($message, 'error');
		} else {
			$message = JText::_( 'COM_PHOCACART_SUCCESS_THUMBS_REGENERATING' );
			$app->enqueueMessage($message, 'message');
		}

		$app->redirect('index.php?option=com_phocacart&view=phocacartitems');
	}
	
	/*
	function removeduplicates() {
		
		$app	= JFactory::getApplication();
		if (PhocacartCategoryMultiple::removeDuplicates()) {
			$message = JText::_( 'COM_PHOCACART_SUCCESS_DUPLICATES_REMOVED' );
		} else {
			$message = JText::_( 'COM_PHOCACART_ERROR_DUPLICATES_NOT_REMOVED' );
		}
		$app->enqueueMessage($message, 'message');
		$app->redirect('index.php?option=com_phocacart&view=phocacartitems');
	}*/
	
}
?>
