<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
jimport( 'joomla.application.component.view');

class PhocaCartCpViewPhocaCartAttributeA extends HtmlView
{
	function display($tpl = null){

		if (!Session::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('JINVALID_TOKEN') . '</span>');
			echo json_encode($response);
			return;
		}

		$app	= Factory::getApplication();
		$task	= $app->input->get( 'task', '', 'string'  );



		if ($task == 'gettoken') {

			// Only tokens and names - don't create folders now they will be created when accessing manager
			$token 	= PhocacartUtils::getToken();
			$folder	= PhocacartUtils::getAndCheckToken('folder', PhocacartPath::getPath('attributefile'));
			//$folder	= PhocacartUtils::getToken('folder');


			$msg = '';// No message when all OK
			$response = array(
			'status' => '1',
			'error' => '',
			'message' => '<span class="ph-result-txt ph-success-txt">'.$msg.'</span>',
			'token' => $token,
			'folder' => $folder
			);
			echo json_encode($response);
			return;
		} else if ($task == 'removefolder') {

			$folderA	= $app->input->get( 'folder', '', 'array'  );

			$nrDeletedFolders = 0;
			$errorMsg = '';

			if (!empty($folderA)) {
				foreach($folderA as $k => $v) {

				    $path = PhocacartPath::getPath('attributefile');
				    if(Folder::exists($path['orig_abs_ds'] . $v)) {
                        if(Folder::delete($path['orig_abs_ds'] . $v)) {
                            $nrDeletedFolders++;
                        } else {
                           $errorMsg = Text::_('COM_PHOCACART_ERROR_REMOVE_ATTRIBUTE_OPTION_DOWNLOAD_FOLDER') . ': ' . $v;
                        }
                    }
				}
			}

			if ($nrDeletedFolders == 1) {

			    $errorMsg = $errorMsg != '' ? '<br>' . $errorMsg : '';
                $response = array(
                    'status' => '1',
                    'message' => '<span class="ph-result-txt ph-success-txt">' .Text::_('COM_PHOCACART_DOWNLOAD_FOLDER_OF_REMOVED_ATTRIBUTE_OPTION_DELETED') . $errorMsg . '</span>');
                echo json_encode($response);
                return;
            } else if ($nrDeletedFolders > 1) {

                $errorMsg = $errorMsg != '' ? '<br>' . $errorMsg : '';
                $response = array(
                    'status' => '1',
                    'message' => '<span class="ph-result-txt ph-success-txt">' .Text::_('COM_PHOCACART_DOWNLOAD_FOLDERS_OF_REMOVED_ATTRIBUTE_OPTIONS_DELETED') . $errorMsg . '</span>');
                echo json_encode($response);
                return;
            } else if ($errorMsg != '') {
                $response = array(
                    'status' => '0',
                    'error' => '<span class="ph-result-txt ph-error-txt">' . $errorMsg . '</span>');
                echo json_encode($response);
                return;
            } else {
			    // The attribute option does not include any folder yet - OK - no message
                $response = array(
                    'status' => '2',
                    'message' => '');
                echo json_encode($response);
                return;
            }
		}

		$response = array(
				'status' => '0',
				'error' => '<span class="ph-result-txt ph-error-txt">' . Text::_('COM_PHOCACART_NO_TASK_SELECTED') . '</span>');
		echo json_encode($response);
		return;
	}
}
?>
