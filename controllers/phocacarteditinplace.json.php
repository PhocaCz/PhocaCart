<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartCpControllerPhocacartEditinplace extends JControllerForm
{
	public function editinplacetext() {
		
		
		if (!JSession::checkToken('request')) {
			$response = array(
				'status' => '0',
				'result' => '<div class="alert alert-danger">' . JText::_('JINVALID_TOKEN') . '</div>');
			echo json_encode($response);
			exit;
		}

		$app		= JFactory::getApplication();
		$value		= $app->input->get('value', '', 'string');
		$id			= $app->input->get('id', '', 'string');
		
		$value 		= PhocacartEdit::store($id, $value);
	
		if ($value !== false) {
			$response = array(
				'status' => '1',
				'result' => htmlspecialchars(strip_tags($value)));
			echo json_encode($response);
			exit;
		}
		
		$response = array(
				'status' => '0',
				'result' => '<div class="alert alert-danger">' . JText::_('COM_PHOCACART_ERROR_ITEM_NOT_SAVED') . '</div>');
		echo json_encode($response);
		exit;
	}
}
?>