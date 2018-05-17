<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
require_once JPATH_COMPONENT.'/controllers/phocacartcommon.php';
class PhocaCartCpControllerPhocacartExtension extends PhocaCartCpControllerPhocaCartCommon {
		
	public function refresh() {
		$app 	= JFactory::getApplication('administrator');
		$type 	= $app->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', 'modules');
		$app->setUserState('com_phocacart.getExtensions.'.$type, null);
		$app->setUserState('com_phocacart.getNews.news', null);
		$msg 	= JText::_('COM_PHOCACART_EXTENSION_LIST_REFRESHED');
		$app->enqueueMessage($msg, 'message');
		$app->redirect('index.php?option=com_phocacart&view=phocacartextensions');
	}
	
	
	public function install() {
		
		if (!JSession::checkToken('request')) {
			$app->enqueueMessage('Invalid Token', 'message');
			return false;
		}
		
		$app = JFactory::getApplication('administrator');
		$msg = '';
		
		try {
			
			$downloadUrl   = $app->input->getBase64('link');
			
			if (!$downloadUrl) {
				throw new Exception(JText::_('COM_PHOCACART_ERROR_EXTENSION_URL_NOT_FOUND'));
			}
			
			$file = JInstallerHelper::downloadPackage(base64_decode($downloadUrl));
			
			if (!$file) {
				throw new Exception(JText::_('COM_PHOCACART_ERROR_EXTENSION_FILE_NOT_FOUND'));
			}
			
			$tmpPath = $app->get('tmp_path');
			$package = JInstallerHelper::unpack($tmpPath . '/' . $file, true);
			
			if (!$package) {
				throw new Exception(JText::_('COM_PHOCACART_ERROR_EXTENSION_FILE_NOT_FOUND'));
			}
			
			$installer = new JInstaller;
			
			if ($installer->install($package['extractdir'])) {
				$msg = JText::sprintf('COM_PHOCACART_SUCCESS_EXTENSION_INSTALLED', $package['type']);
				JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
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