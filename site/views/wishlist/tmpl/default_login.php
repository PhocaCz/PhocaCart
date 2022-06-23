<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;

$layoutUL 	= new FileLayout('user_login', null, array('component' => 'com_phocacart'));
$layoutAl 	= new FileLayout('alert', null, array('component' => 'com_phocacart'));


if((int)$this->u->id >  0) {
	// User is logged in
} else {

	echo '<p>&nbsp</p>';

	echo $layoutAl->render(array('type' => 'error', 'text' => Text::_('COM_PHOCACART_YOUR_WISHLIST_WILL_BE_SAVED_ONLY_IF_YOU_LOGIN')));

	echo '<div class="'.$this->s['c']['row'].' ph-account-box-row" >';
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-account-box-header" id="phaccountloginedit"><h3>'.Text::_('COM_PHOCACART_LOGIN_REGISTER').'</h3></div>';
	echo '</div>';

	echo '<div class="'.$this->s['c']['row'].' ph-account-box-action">';

	echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].' ph-wishlist-login-box-row  ph-right-border" >';

	$d = array();
	$d['s'] = $this->s;
	$d['t'] = $this->t;
	echo $layoutUL->render($d);

	echo '</div>'. "\n";// end columns

	echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-left-border">';

	$usersConfig = ComponentHelper::getParams('com_users');

	//echo '<ul class="unstyled">'. "\n";
	if ($usersConfig->get('allowUserRegistration')) {
		echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_REGISTER').'</div>'. "\n";
		//echo '<li><a href="'. JRoute::_('index.php?option=com_users&view=registration').'">'.JText::_('MOD_LOGIN_REGISTER').'<span class="icon-arrow-right"></span></a></li>'. "\n";

		echo '<a class="'.$this->s['c']['btn.btn-primary.btn-sm'].' ph-checkout-btn-login" href="'. Route::_('index.php?option=com_users&view=registration').'"><span class="'.$this->s['i']['user'].'"></span>  '.Text::_('MOD_LOGIN_REGISTER').'</a>'. "\n";

	}
	//echo '</ul>'. "\n";


	echo '</div>'. "\n";// end columns
	echo '<div class="ph-cb"></div>';

	echo '</div>'. "\n";// end row
}
?>
