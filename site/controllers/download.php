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

class PhocaCartControllerDownload extends FormController
{
	public function download() {
	
		Session::checkToken() or jexit( 'Invalid Token' );
		$app				= Factory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		
		if ($item['id'] > 0) {
			$download = PhocacartDownload::download($item['id']);
			if (!$download) {
				$app->enqueueMessage(Text::_('COM_PHOCACART_FILE_CANNOT_BE_DOWNLOADED'), 'error');
			}
		} else {
			$app->enqueueMessage(Text::_('COM_PHOCACART_NO_FILE_FOUND'), 'error');
		}
		
		$app->redirect(base64_decode($item['return']));
	}
	
	public function downloadpublic() {
	
		Session::checkToken() or jexit( 'Invalid Token' );
		$app				= Factory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		
		if ($item['id'] > 0) {
			$download = PhocacartDownload::downloadPublic($item['id']);
			if (!$download) {
				$app->enqueueMessage(Text::_('COM_PHOCACART_FILE_CANNOT_BE_DOWNLOADED'), 'error');
			}
		} else {
			$app->enqueueMessage(Text::_('COM_PHOCACART_NO_FILE_FOUND'), 'error');
		}
		
		$app->redirect(base64_decode($item['return']));
	}
}
?>