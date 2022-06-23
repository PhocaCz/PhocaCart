<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
require_once JPATH_COMPONENT.'/controllers/phocacartcommons.php';
class PhocaCartCpControllerPhocaCartEditTax extends PhocaCartCpControllerPhocaCartCommons
{
	public function &getModel($name = 'PhocaCartEditTax', $prefix = 'PhocaCartCpModel', $config = array()) {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	function edittax() {
	
		if (!Session::checkToken('request')) {
			$app->enqueueMessage('Invalid Token', 'message');
			return false;
		}
		
		$app	= Factory::getApplication();
		//$id		= $app->input->get('id', 0, 'int');
		$jform	= $app->input->get('jform', array(), 'array');
		
		if (!isset($jform['type'])) {
			$jform['type'] = 1;// country
		}
		
		if ((int)$jform['id'] > 0) {
			$model = $this->getModel( 'phocacartedittax' );
			
			if(!$model->editTax($jform)) {
				$message = Text::_( 'COM_PHOCACART_ERROR_UPDATE_TAX_INFORMATION' );
				$app->enqueueMessage($message, 'error');
			} else {
				$message = Text::_( 'COM_PHOCACART_SUCCESS_UPDATE_TAX_INFORMATION' );
				$app->enqueueMessage($message, 'message');
			}
			$app->redirect('index.php?option=com_phocacart&view=phocacartedittax&type='.(int)$jform['type'].'&tmpl=component&id='.(int)$jform['id']);
		} else {
		
			$app->enqueueMessage(Text::_('COM_PHOCACART_NO_ITEM_FOUND'), 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacartedittax&type='.(int)$jform['type'].'&tmpl=component');
		}
	}

	function emptyinformation() {
		$app	= Factory::getApplication();
		$jform	= $app->input->get('jform', array(), 'array');
		
		if (!isset($jform['type'])) {
			$jform['type'] = 1;// country
		}
		if ((int)$jform['id'] > 0) {
			$model = $this->getModel( 'phocacartedittax' );
			
			if(!$model->emptyInformation($jform['id'], $jform['type'])) {
				$message = Text::_( 'COM_PHOCACART_ERROR_EMPTY_TAX_INFORMATION' );
				$app->enqueueMessage($message, 'error');
			} else {
				$message = Text::_( 'COM_PHOCACART_SUCCESS_EMPTY_TAX_INFORMATION' );
				$app->enqueueMessage($message, 'message');
			}
			$app->redirect('index.php?option=com_phocacart&view=phocacartedittax&type='.(int)$jform['type'].'&tmpl=component&id='.(int)$jform['id']);
		} else {
		
			$app->enqueueMessage(Text::_('COM_PHOCACART_NO_ITEM_FOUND'), 'error');
			$app->redirect('index.php?option=com_phocacart&view=phocacartedittax&type='.(int)$jform['type'].'&tmpl=component');
		}
	}
}
?>