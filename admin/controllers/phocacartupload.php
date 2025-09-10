<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die( 'Restricted access' );
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Path;
use Phoca\PhocaCart\Filesystem\Folder;
use Phoca\PhocaCart\Filesystem\File;
jimport('joomla.client.helper');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class PhocaCartCpControllerPhocaCartUpload extends PhocaCartCpController
{
	function __construct() {
		parent::__construct();
	}

	function createfolder() {
		Session::checkToken() or jexit( 'COM_PHOCADOWNLOAD_INVALID_TOKEN' );
		$app	= Factory::getApplication();

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		ClientHelper::setCredentialsFromRequest('ftp');

		$paramsC = PhocacartUtils::getComponentParameters();
		$folder_permissions = $paramsC->get( 'folder_permissions', 0755 );
		//$folder_permissions = octdec((int)$folder_permissions);

		$folderNew		= $app->getInput()->get( 'foldername', '');
		$folderCheck	= $app->getInput()->get( 'foldername', null, 'raw');
		$parent			= $app->getInput()->get( 'folderbase', '', 'path' );
		$tab			= $app->getInput()->get( 'tab', 0, 'string' );
		$field			= $app->getInput()->get( 'field');
		$viewBack		= $app->getInput()->get( 'viewback', 'phocacartmanager' );
		$manager		= $app->getInput()->get( 'manager', 'file', 'string' );


		$link = '';
		if ($manager != '') {
			$group 	= PhocacartUtilsSettings::getManagerGroup($manager);
			$link	= 'index.php?option=com_phocacart&view='.(string)$viewBack.'&manager='.(string)$manager
						 .str_replace('&amp;', '&', $group['c']).'&folder='.$parent.'&tab='.(string)$tab.'&field='.$field;
			$path	= PhocacartPath::getPath($manager);// we use viewback to get right path
		} else {
            $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_CONTROLLER_MANAGER_NOT_SET'), 'error');
			$app->redirect('index.php?option=com_phocacart');
			exit;
		}

		Factory::getApplication()->getInput()->set('folder', $parent);

		if (($folderCheck !== null) && ($folderNew !== $folderCheck)) {
            $app->enqueueMessage(Text::_('COM_PHOCACART_WARNING_DIRNAME'), 'error');
			$app->redirect($link);
		}

		if (strlen($folderNew) > 0) {
			$folder = Path::clean($path['orig_abs_ds'].$parent.'/'.$folderNew);

			if (!Folder::exists($folder) && !File::exists($folder)) {
				//JFolder::create($path, $folder_permissions );

				switch((int)$folder_permissions) {
					case 777:
						Folder::create($folder, 0777 );
					break;
					case 705:
						Folder::create($folder, 0705 );
					break;
					case 666:
						Folder::create($folder, 0666 );
					break;
					case 644:
						Folder::create($folder, 0644 );
					break;
					case 755:
					Default:
						Folder::create($folder, 0755 );
					break;
				}
				if (isset($folder)) {
					$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
					File::write($folder."/index.html", $data);
				} else {
                    $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_FOLDER_CREATING'), 'error');
					$app->redirect($link);
				}

                $app->enqueueMessage(Text::_('COM_PHOCACART_SUCCESS_FOLDER_CREATING'), 'success');
				$app->redirect($link);
			} else {
                $app->enqueueMessage(Text::_('COM_PHOCACART_ERROR_FOLDER_CREATING_EXISTS'), 'error');
				$app->redirect($link);
			}
			//Factory::getApplication()->getInput()->set('folder', ($parent) ? $parent.'/'.$folder : $folder);
		}
		$app->redirect($link);
	}

	function multipleupload() {
		$result = PhocacartFileUpload::realMultipleUpload();
		return true;
	}

	function upload() {
		$result = PhocacartFileUpload::realSingleUpload();
		return true;
	}


}
