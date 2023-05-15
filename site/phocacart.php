<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

require_once( JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/bootstrap.php' );
require_once( JPATH_COMPONENT.'/controller.php' );
require_once( JPATH_COMPONENT.'/helpers/route.php' );

$controller = BaseController::getInstance('PhocaCart');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
