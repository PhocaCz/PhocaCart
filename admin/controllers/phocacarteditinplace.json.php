<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

class PhocaCartCpControllerPhocacartEditinplace extends JControllerForm
{



    public function editinplacetext() {


		if (!JSession::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . JText::_('JINVALID_TOKEN') . '</div>',
				'result' => '');
			echo json_encode($response);
			exit;
		}

		$app		= JFactory::getApplication();
		$value		= $app->input->get('value', '', 'raw');
		$id			= $app->input->get('id', '', 'string');



		$options = array();
		$options['id'] = $id;
		$options['msg'] = '';
		$options['value'] = $value;
		$options['valuecombined'] = '';
		$options['idcombined'] = '';

		$saved = PhocacartEdit::store($options);

		if ($saved !== false) {

			$response = array();
			$response['status'] = 1;


			$response['result'] = htmlspecialchars($options['value']);
			if ($options['valuecombined'] != '') {
				$response['resultcombined'] = htmlspecialchars($options['valuecombined']);
			}
			if ($options['idcombined'] != '') {
				// will be replaced in JS
				//$response['idcombined'] = strip_tags(str_replace(':', '\\:', $options['idcombined']));
				$response['idcombined'] = htmlspecialchars($options['idcombined']);
			}

			echo json_encode($response);
			exit;
		}

		$options['msg'] = $options['msg'] != '' ? '<br />' . $options['msg'] : '';

		$response = array(
				'status' => '0',
				'error' => '<div class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_ERROR_ITEM_NOT_SAVED') . $options['msg'] . '</div>',
				'result' => '');
		echo json_encode($response);
		exit;
	}
}
?>
