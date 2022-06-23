<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Installer\Installer;
require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocacartExtension extends PhocaCartCpControllerPhocaCartCommon {
		
	public function refresh() {
		$app 	= Factory::getApplication('administrator');
		$type 	= $app->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', 'modules');
		$app->setUserState('com_phocacart.getExtensions.'.$type, null);
		$app->setUserState('com_phocacart.getNews.news', null);
		$msg 	= Text::_('COM_PHOCACART_EXTENSION_LIST_REFRESHED');
		$app->enqueueMessage($msg, 'message');
		$app->redirect('index.php?option=com_phocacart&view=phocacartextensions');
	}
	
	
	public function install() {
		
		if (!Session::checkToken('request')) {
			$app->enqueueMessage('Invalid Token', 'message');
			return false;
		}
		
		$app = Factory::getApplication('administrator');
		$msg = '';
		
		try {
			
			$downloadUrl   = $app->input->getBase64('link');
			
			if (!$downloadUrl) {
				throw new Exception(Text::_('COM_PHOCACART_ERROR_EXTENSION_URL_NOT_FOUND'));
			}
			
			$file = InstallerHelper::downloadPackage(base64_decode($downloadUrl));
			
			if (!$file) {
				throw new Exception(Text::_('COM_PHOCACART_ERROR_EXTENSION_FILE_NOT_FOUND'));
			}
			
			$tmpPath = $app->get('tmp_path');
			$package = InstallerHelper::unpack($tmpPath . '/' . $file, true);
			
			if (!$package) {
				throw new Exception(Text::_('COM_PHOCACART_ERROR_EXTENSION_FILE_NOT_FOUND'));
			}
			
			$installer = new Installer;
			
			if ($installer->install($package['extractdir'])) {
				$msg = Text::sprintf('COM_PHOCACART_SUCCESS_EXTENSION_INSTALLED', $package['type']);
				InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
			}
		} catch (RuntimeException $e) {
			
			$app->enqueueMessage($e->getMessage(), 'message');
			$app->redirect('index.php?option=com_phocacart&view=phocacartextensions');
		}
		
		$app->enqueueMessage($msg, 'message');
		$app->redirect('index.php?option=com_phocacart&view=phocacartextensions');
	}
}
?>