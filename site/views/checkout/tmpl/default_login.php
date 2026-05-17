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
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$layoutI 		= new FileLayout('icon_checkout_status', null, array('component' => 'com_phocacart'));
$layoutUL 		= new FileLayout('user_login', null, array('component' => 'com_phocacart'));
$layoutUR 		= new FileLayout('user_register', null, array('component' => 'com_phocacart'));
$d				= array();
$d['s']			= $this->s;
$d['suffix']	= $this->t['icon_suffix'];
$d['number']	= $this->t['nl'];
$d['type']		= $this->t['checkout_icon_status'];

if($this->a->login == 0) {

	$d['status']	= 'pending';

	/*require_once JPATH_SITE.'/components/com_users/helpers/route.php';
	jimport( 'joomla.application.module.helper' );
	$module = ModuleHelper::getModule('mod_login');
	$mP 	= new Registry();
	$mP->loadString($module->params);*/

	$lang = Factory::getLanguage();
	$lang->load('mod_login');


	echo '<div class="ph-checkout-box-login ph-checkout-box-status-'.$d['status'].'">';

	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" >';
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-box-header" id="phcheckoutloginedit">'.$layoutI->render($d).'<h3>'.$this->t['nl'].'. '.Text::_('COM_PHOCACART_LOGIN_REGISTER').'</h3></div>';
	echo '</div>';


	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-action">';

	echo '<div class="'.$this->s['c']['col.xs12.sm8.md8'].' ph-right-border">';

	$d = array();
	$d['s'] = $this->s;
	$d['t'] = $this->t;
	echo $layoutUL->render($d);

	echo '</div>'. "\n";// end checkout_login_row_item_login


	echo '<div class="'.$this->s['c']['col.xs12.sm4.md4'].' ph-left-border">';

	$d = array();
	$d['s'] = $this->s;
	$d['t'] = $this->t;
	echo $layoutUR->render($d);



	if ($this->t['guest_checkout'] == 1) {
		echo '<div class="ph-box-header">'.Text::_('COM_PHOCACART_GUEST_CHECKOUT').'</div>'. "\n";

		echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="'.$this->s['c']['form-horizontal.form-validate'].' ph-checkout-add-guest" role="form" id="phCheckoutAddGuest">';
		echo '<button type="submit" tabindex="0" name="Submit" class="'.$this->s['c']['btn.btn-primary.btn-sm'].' ph-btn ph-checkout-btn-login">'.PhocacartRenderIcon::icon($d['s']['i']['user'], '', ' ') . Text::_('COM_PHOCACART_GUEST_CHECKOUT') .'</button>'. "\n";

		echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
		echo '<input type="hidden" name="task" value="checkout.setguest" />'. "\n";
		echo '<input type="hidden" name="id" value="1" />'. "\n";
		echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
		echo HTMLHelper::_('form.token');
		echo '</form>';

	}


	echo '</div>'. "\n";// end checkout_login_row_item_register
	echo '</div>'. "\n";// ph-checkout-box-action

	echo '</div>';// end box login



} else if($this->a->login == 1) {

	$d['status']	= 'finished';

	echo '<div class="ph-checkout-box-login ph-checkout-box-status-'.$d['status'].'">';

	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" >';
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-box-header" id="phcheckoutloginview">'.$layoutI->render($d).'<h3>'.$this->t['nl'].'. '.Text::_('COM_PHOCACART_LOGIN_REGISTER').'</h3></div>';
	echo '</div>';


	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-action">';
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].'">';
	echo '<div>'.Text::_('COM_PHOCACART_YOU_ARE_LOGGED_IN_AS').' <b>'.$this->u->name.'</b></div>';
	echo '</div>';
	echo '</div>';

	echo '</div>';// end box login

} else if($this->a->login == 2) {

	$d['status']	= 'finished';

	echo '<div class="ph-checkout-box-login ph-checkout-box-status-'.$d['status'].'">';

	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-row" >';
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].' ph-checkout-box-header">'.$layoutI->render($d).'<h3>'.$this->t['nl'].'. '.Text::_('COM_PHOCACART_GUEST_CHECKOUT').'</h3></div>';
	echo '</div>';


	echo '<div class="'.$this->s['c']['row'].' ph-checkout-box-action">';
	echo '<div class="'.$this->s['c']['col.xs12.sm12.md12'].'">';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="'.$this->s['c']['form-horizontal.form-validate'].' ph-checkout-add-guest" role="form" id="phCheckoutAddGuest">';

	echo '<div>'.Text::_('COM_PHOCACART_GUEST_CHECKOUT') . '</div>';


	if ($this->t['guest_checkout'] == 1 && $this->t['guest_checkout_auto_enable'] == 1) {
		// Automatic guest checkout cannot be canceled
	} else {
		echo '<div class="'.$this->s['c']['pull-right'].' ph-checkout-btn-login-box">';
		echo '<button type="submit" tabindex="0" name="Submit" class="'.$this->s['c']['btn.btn-primary.btn-sm'].' ph-btn ph-checkout-btn-login">'.PhocacartRenderIcon::icon($d['s']['i']['user'], '', ' ') . Text::_('COM_PHOCACART_CANCEL_GUEST_CHECKOUT') .'</button>';
		echo '</div>'. "\n";
	}



	//echo '<div class="ph-cb"></div>';

	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="task" value="checkout.setguest" />'. "\n";
	echo '<input type="hidden" name="id" value="0" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo HTMLHelper::_('form.token');
	echo '</form>';

	echo '</div>';
	echo '</div>';

	echo '</div>';// end box login

}
?>
