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
use Joomla\CMS\MVC\Controller\BaseController;
if (!Factory::getUser()->authorise('core.manage', 'com_phocacart')) {
	throw new Exception(Text::_('COM_PHOCACART_ERROR_ALERTNOAUTHOR'), 404);
}

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.application.component.controller');

require_once( JPATH_COMPONENT . '/libraries/bootstrap.php' );
require_once( JPATH_COMPONENT . '/controller.php' );

Text::script('COM_PHOCACART_MENU_BACK');
Text::script('COM_PHOCACART_MENU_PHOCACART');

$controller	= BaseController::getInstance('phocacartCp');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();

