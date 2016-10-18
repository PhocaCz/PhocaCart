<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.client.helper');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class PhocaCartCpControllerPhocaCartUpload extends PhocaCartCpController
{
	function __construct() {
		parent::__construct();
	}

	function createfolder() {
		JSession::checkToken() or jexit( 'COM_PHOCADOWNLOAD_INVALID_TOKEN' );
		$app	= JFactory::getApplication();
		
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		
		$paramsC = JComponentHelper::getParams('com_phocacart');
		$folder_permissions = $paramsC->get( 'folder_permissions', 0755 );
		//$folder_permissions = octdec((int)$folder_permissions);

		$folderNew		= $app->input->get( 'foldername', '');
		$folderCheck	= $app->input->get( 'foldername', null, 'raw');
		$parent			= $app->input->get( 'folderbase', '', 'path' );
		$tab			= $app->input->get( 'tab', 0, 'string' );
		$field			= $app->input->get( 'field');
		$viewBack		= $app->input->get( 'viewback', 'phocacartmanager' );
		$manager		= $app->input->get( 'manager', 'file', 'string' );
		
		
		$link = '';
		if ($manager != '') {
			$group 	= PhocaCartSettings::getManagerGroup($manager);
			$link	= 'index.php?option=com_phocacart&view='.(string)$viewBack.'&manager='.(string)$manager
						 .str_replace('&amp;', '&', $group['c']).'&folder='.$parent.'&tab='.(string)$tab.'&field='.$field;
			$path	= PhocaCartPath::getPath($manager);// we use viewback to get right path
		} else {
			$app->redirect('index.php?option=com_phocacart', JText::_('COM_PHOCACART_ERROR_CONTROLLER_MANAGER_NOT_SET'));
			exit;
		}

		JFactory::getApplication()->input->set('folder', $parent);

		if (($folderCheck !== null) && ($folderNew !== $folderCheck)) {
			$app->redirect($link, JText::_('COM_PHOCACART_WARNING_DIRNAME'));
		}

		if (strlen($folderNew) > 0) {
			$folder = JPath::clean($path['orig_abs_ds'].$parent.'/'.$folderNew);
		
			if (!JFolder::exists($folder) && !JFile::exists($folder)) {
				//JFolder::create($path, $folder_permissions );
				
				switch((int)$folder_permissions) {
					case 777:
						JFolder::create($folder, 0777 );
					break;
					case 705:
						JFolder::create($folder, 0705 );
					break;
					case 666:
						JFolder::create($folder, 0666 );
					break;
					case 644:
						JFolder::create($folder, 0644 );
					break;				
					case 755:
					Default:
						JFolder::create($folder, 0755 );
					break;
				}
				if (isset($folder)) {
					$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
					JFile::write($folder."/index.html", $data);
				} else {
					$app->redirect($link, JText::_('COM_PHOCACART_ERROR_FOLDER_CREATING'));
				}
				
				$app->redirect($link, JText::_('COM_PHOCACART_SUCCESS_FOLDER_CREATING'));
			} else {
				$app->redirect($link, JText::_('COM_PHOCACART_ERROR_FOLDER_CREATING_EXISTS'));
			}
			//JFactory::getApplication()->input->set('folder', ($parent) ? $parent.'/'.$folder : $folder);
		}
		$app->redirect($link);
	}
	
	function multipleupload() {
		$result = PhocaCartFileUpload::realMultipleUpload();
		return true;	
	}
	
	function upload() {
		$result = PhocaCartFileUpload::realSingleUpload();
		return true;
	}
	
	
}