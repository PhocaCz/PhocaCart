<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
require_once JPATH_COMPONENT.'/controllers/phocacartcommons.php';
class PhocaCartCpControllerPhocaCartEditStatus extends PhocaCartCpControllerPhocaCartCommons
{
	public function &getModel($name = 'PhocaCartEditStatus', $prefix = 'PhocaCartCpModel', $config = array()) {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	function editstatus() {
	
		if (!JRequest::checkToken('request')) {
			$app->enqueueMessage('Invalid Token', 'message');
			return false;
		}
		
		$app	= JFactory::getApplication();
		//$id		= $app->input->get('id', 0, 'int');
		$jform	= $app->input->get('jform', array(), 'array');
		

		if ((int)$jform['id'] > 0 && $jform['status_id']) {
			$model = $this->getModel( 'phocacarteditstatus' );
			
			if(!$model->editStatus($jform)) {
				$message = JText::_( 'COM_PHOCACART_ERROR_UPDATE_STATUS' );
				$app->enqueueMessage($message, 'error');
			} else {
				$message = JText::_( 'COM_PHOCACART_SUCCESS_UPDATE_STATUS' );
				$app->enqueueMessage($message, 'message');
			}
			$app->redirect('index.php?option=com_phocacart&view=phocacarteditstatus&tmpl=component&id='.(int)$jform['id']);
		} else {
		
			$app->enqueueMessage(JText::_('COM_PHOCACART_NO_ITEM_FOUND'), 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacarteditstatus&tmpl=component');
		}
	}

	function emptyhistory() {
		$app	= JFactory::getApplication();
		$jform	= $app->input->get('jform', array(), 'array');
		
		if ((int)$jform['id'] > 0) {
			$model = $this->getModel( 'phocacarteditstatus' );
			
			if(!$model->emptyHistory($jform['id'])) {
				$message = JText::_( 'COM_PHOCACART_ERROR_EMPTY_STATUSES' );
				$app->enqueueMessage($message, 'error');
			} else {
				$message = JText::_( 'COM_PHOCACART_SUCCESS_EMPTY_STATUSES' );
				$app->enqueueMessage($message, 'message');
			}
			$app->redirect('index.php?option=com_phocacart&view=phocacarteditstatus&tmpl=component&id='.(int)$jform['id']);
		} else {
		
			$app->enqueueMessage(JText::_('COM_PHOCACART_NO_ITEM_FOUND'), 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacarteditstatus&tmpl=component');
		}
	}
}
?>