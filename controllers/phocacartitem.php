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
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model	= $this->getModel('phocacartitem', '', array());
		$this->setRedirect(JRoute::_('index.php?option=com_phocacart&view=phocacartitems'.$this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}
	
	
	function recreate() {
		$app	= JFactory::getApplication();
		$cid 	= JRequest::getVar( 'cid', array(), '', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'COM_PHOCACART_SELECT_ITEM_RECREATE' ) );
		}
		$message = '';
		$model = $this->getModel( 'phocacartitem' );
		if(!$model->recreate($cid, $message)) {
			$message = PhocaCartUtils::setMessage($message, JText::_( 'COM_PHOCACART_ERROR_THUMBS_REGENERATING' ));
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
		if (PhocaCartCategoryMultiple::removeDuplicates()) {
			$message = JText::_( 'COM_PHOCACART_SUCCESS_DUPLICATES_REMOVED' );
		} else {
			$message = JText::_( 'COM_PHOCACART_ERROR_DUPLICATES_NOT_REMOVED' );
		}
		$app->enqueueMessage($message, 'message');
		$app->redirect('index.php?option=com_phocacart&view=phocacartitems');
	}*/
	
}
?>
