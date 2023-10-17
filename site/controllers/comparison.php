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

class PhocaCartControllerComparison extends FormController
{
	
	public function add() { 
		
		Session::checkToken() or jexit( 'Invalid Token' );
		$app				= Factory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['catid']		= $this->input->get( 'catid', 0, 'int' );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		
		$compare	= new PhocacartCompare();
		$added	= $compare->addItem((int)$item['id'], (int)$item['catid']);
		if ($added) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_ADDED_TO_COMPARISON_LIST'), 'message');
		} else {
			$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_ADDED_TO_COMPARISON_LIST'), 'error');
		}
		//$app->redirect(Route::_('index.php?option=com_phocacart&view=checkout'));
		$app->redirect(base64_decode($item['return']));
	}
	
		public function remove() {
		
		Session::checkToken() or jexit( 'Invalid Token' );
		$app				= Factory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		
		$compare	= new PhocacartCompare();
		$added	= $compare->removeItem((int)$item['id']);
		if ($added) {
			$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_REMOVED_FROM_COMPARISON_LIST'), 'message');
		} else {
			$app->enqueueMessage(Text::_('COM_PHOCACART_PRODUCT_NOT_REMOVED_FROM_COMPARISON_LIST'), 'error');
		}
		//$app->redirect(Route::_('index.php?option=com_phocacart&view=checkout'));
		$app->redirect(base64_decode($item['return']));
	}
	
}
?>
