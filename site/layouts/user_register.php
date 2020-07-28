<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
Joomla\CMS\HTML\HTMLHelper::_('behavior.keepalive');
Joomla\CMS\HTML\HTMLHelper::_('bootstrap.tooltip');

require_once JPATH_SITE.'/components/com_users/helpers/route.php';
jimport( 'joomla.application.module.helper' );
$module = JModuleHelper::getModule('mod_login');
$mP 	= new JRegistry();
$mP->loadString($module->params);

$lang = JFactory::getLanguage();
$lang->load('mod_login');

$d  = $displayData;


$usersConfig = JComponentHelper::getParams('com_users');
	//echo '<ul class="unstyled">'. "\n";
	if ($usersConfig->get('allowUserRegistration')) {
		echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_REGISTER').'</div>'. "\n";
		//echo '<li><a href="'. JRoute::_('index.php?option=com_users&view=registration').'">'.JText::_('MOD_LOGIN_REGISTER').'<span class="icon-arrow-right"></span></a></li>'. "\n";

		echo '<a class="'.$d['s']['c']['btn.btn-primary.btn-sm'].' ph-btn ph-checkout-btn-login" href="'. JRoute::_('index.php?option=com_users&view=registration').'"><span class="'.$d['s']['i']['user'].'"></span>  '.JText::_('MOD_LOGIN_REGISTER').'</a>'. "\n";

	}
	//echo '</ul>'. "\n";


?>
