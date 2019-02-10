<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if (!JFactory::getUser()->authorise('core.manage', 'com_phocacart')) {
	throw new Exception(JText::_('COM_PHOCACART_ERROR_ALERTNOAUTHOR'), 404);
}

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
require_once( JPATH_COMPONENT.'/controller.php' );

JLoader::registerPrefix('Phocacart', JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart');

/*
if (! class_exists('PhocacartLoader')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/loader.php');
}

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
require_once( JPATH_COMPONENT.'/controller.php' );
phocacart import('phocacart.utils.utils');
*/


jimport('joomla.application.component.controller');
$controller	= JControllerLegacy::getInstance('phocacartCp');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
?>