<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');

require_once JPATH_SITE.'/components/com_users/src/Service/Router.php';
jimport( 'joomla.application.module.helper' );
$module = ModuleHelper::getModule('mod_login');
$mP 	= new Registry();
$mP->loadString($module->params);

$lang = Factory::getLanguage();
$lang->load('mod_login');

$d  = $displayData;


$usersConfig = ComponentHelper::getParams('com_users');
	//echo '<ul class="unstyled">'. "\n";
	if ($usersConfig->get('allowUserRegistration')) {
		echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_REGISTER').'</div>'. "\n";
		//echo '<li><a href="'. JRoute::_('index.php?option=com_users&view=registration').'">'.JText::_('MOD_LOGIN_REGISTER').'<span class="icon-arrow-right"></span></a></li>'. "\n";

		echo '<a class="'.$d['s']['c']['btn.btn-primary.btn-sm'].' ph-btn ph-checkout-btn-login" href="'. Route::_('index.php?option=com_users&view=registration').'">'.PhocacartRenderIcon::icon($d['s']['i']['user'], '', ' ') .Text::_('MOD_LOGIN_REGISTER').'</a>'. "\n";

	}
	//echo '</ul>'. "\n";


?>
