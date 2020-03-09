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
class PhocaCartCpControllerPhocacartCategory extends PhocaCartCpControllerPhocaCartCommon
{
	public function batch($model = null) {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model	= $this->getModel('phocacartcategory', '', array());
		$this->setRedirect(JRoute::_('index.php?option=com_phocacart&view=phocacartcategories'.$this->getRedirectToListAppend(), false));
		return parent::batch($model);
	}

	function recreate() {
		$app	= JFactory::getApplication();
		$cid 	= JFactory::getApplication()->input->get( 'cid', array(), '', 'array' );
		\Joomla\Utilities\ArrayHelper::toInteger($cid);

		$message = '';

		if (count( $cid ) < 1) {
			$message = JText::_( 'COM_PHOCACART_SELECT_ITEM_RECREATE' );
			$app->enqueueMessage($message, 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacartcategories');
		}

		$model = $this->getModel( 'phocacartcategory' );
		if(!$model->recreate($cid, $message)) {
			$message = PhocacartUtils::setMessage($message, JText::_( 'COM_PHOCACART_ERROR_THUMBS_REGENERATING' ));
			$app->enqueueMessage($message, 'error');
		} else {
			$message = PhocacartUtils::setMessage($message, JText::_( 'COM_PHOCACART_SUCCESS_THUMBS_REGENERATING' ));
			$app->enqueueMessage($message, 'message');
		}

		$app->redirect('index.php?option=com_phocacart&view=phocacartcategories');
	}

	function countproducts() {
		$app	= JFactory::getApplication();
		$cid 	= JFactory::getApplication()->input->get( 'cid', array(), '', 'array' );
		\Joomla\Utilities\ArrayHelper::toInteger($cid);
		$redirect = 'index.php?option=com_phocacart&view=phocacartcategories';

		if (count( $cid ) < 1) {
			$app->enqueueMessage(JText::_( 'COM_PHOCACART_SELECT_ITEM_COUNT_PRODUCTS' ), 'error');
			$app->redirect($redirect);
		}

		PhocacartCount::setProductCount($cid, 'category');// Message set by Count Class
		$app->redirect($redirect);
	}
}

?>
