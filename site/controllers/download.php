<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartControllerDownload extends JControllerForm
{
	public function download() {
	
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$app				= JFactory::getApplication();
		$item				= array();
		$item['id']			= $this->input->get( 'id', 0, 'int' );
		$item['return']		= $this->input->get( 'return', '', 'string'  );
		
		if ($item['id'] > 0) {
			$download = PhocaCartDownload::download($item['id']);
			if (!$download) {
				$app->enqueueMessage(JText::_('COM_PHOCACART_FILE_CANNOT_BE_DOWNLOADED'), 'error');
			}
		} else {
			$app->enqueueMessage(JText::_('COM_PHOCACART_NO_FILE_FOUND'), 'error');
		}
		
		$app->redirect(base64_decode($item['return']));
	}
}
?>