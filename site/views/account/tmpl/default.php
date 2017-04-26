<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

echo '<div id="ph-pc-account-box" class="pc-account-view'.$this->p->get( 'pageclass_sfx' ).'">';

echo PhocacartRenderFront::renderHeader(array(JText::_('COM_PHOCACART_MY_ACCOUNT')));


/*if ( $this->t['description'] != '') {
	echo '<div class="ph-desc">'. $this->t['description']. '</div>';
}*/

if ((int)$this->u->id > 0) {
	
	// Header
	echo '<div class="col-sm-12 col-md-12 ph-account-box-row" >';
	echo '<div class="ph-account-box-header" id="phaccountaddressedit"><div class="pull-right"></span></div><h3>'.JText::_('COM_PHOCACART_BILLING_AND_SHIPPING_ADDRESS').'</h3></div>';
	echo '</div>';

	echo '<form action="'.$this->t['linkcheckout'].'" method="post" class="form-horizontal form-validate" role="form" id="phcheckoutAddress">';
	echo '<div class="ph-account-box-action">';
	echo '<div id="ph-request-message" style="display:none"></div>';
	echo '<div class="col-sm-6 col-md-6 ph-account-billing-row" id="phBillingAddress" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_BILLING_ADDRESS').'</div>';
	echo $this->t['dataaddressform']['b'];
	echo '</div>';// end row
	
	echo '<div class="col-sm-6 col-md-6 ph-account-shipping-row" id="phShippingAddress" >';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_SHIPPING_ADDRESS').'</div>';
	echo $this->t['dataaddressform']['s'];
	echo '</div>';// end row
	
	echo '<div class="ph-cb"></div>';
	
	echo '<div class="pull-right ph-account-check-box">';
	
	echo '<div class="checkbox">';
	echo '<label><input type="checkbox" id="phCheckoutBillingSameAsShipping" name="phcheckoutbsas" '.$this->t['dataaddressform']['bsch'].' > '.JText::_('COM_PHOCACART_DELIVERY_AND_BILLING_ADDRESSES_ARE_THE_SAME').'</label>';
	echo '</div>';

	echo '</div>';
	
	echo '<div class="ph-cb"></div>';
	
	echo '<div class="pull-right ph-account-address-save">';
	echo '<button class="btn btn-primary btn-sm ph-btn"><span class="glyphicon glyphicon-floppy-disk"></span> '.JText::_('COM_PHOCACART_SAVE').'</button>';
	//echo '<input type="submit" value="submit" />';
	echo '</div>';
	
	echo '<div class="ph-cb"></div>';
	echo '</div>'."\n";// end box action
	
	
	echo '<input type="hidden" name="tmpl" value="component" />';
	echo '<input type="hidden" name="option" value="com_phocacart" />'. "\n";
	echo '<input type="hidden" name="task" value="checkout.saveaddress" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo JHtml::_('form.token');
	echo '</form>'. "\n";
	
	if ($this->t['display_edit_profile'] == 1) {
		echo $this->loadTemplate('profile');
	}
	
} else {

	require_once JPATH_SITE.'/components/com_users/helpers/route.php';
	jimport( 'joomla.application.module.helper' ); 
	$module = JModuleHelper::getModule('mod_login');
	$mP 	= new JRegistry();
	$mP->loadString($module->params);

	$lang 	= JFactory::getLanguage();
	$lang->load('mod_login');
	
	echo '<div class="col-sm-12 col-md-12 ph-account-box-row" >';
	//echo '<div class="ph-account-box-header" id="phaccountloginedit"><div class="pull-right"><span class="glyphicon glyphicon-remove-circle ph-account-icon-not-ok"></span></div><h3>1. '.JText::_('COM_PHOCACART_LOGIN_REGISTER').'</h3></div>';
	echo '<div class="ph-account-box-header" id="phaccountloginedit"><div class="pull-right"></span></div><h3>'.JText::_('COM_PHOCACART_LOGIN_REGISTER').'</h3></div>';
	echo '</div>';
	
	
	echo '<div class="ph-account-box-action">';


	echo '<div class="col-sm-8 col-md-8 ph-right-border">';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_LOGIN').'</div>'. "\n";
	
	echo '<form action="'.JRoute::_('index.php', true, $mP->get('usesecure')).'" method="post" id="ph-login-form" class="form-inline" role="form">';

	echo '<div id="ph-form-login-username" class="control-group form-inline">'. "\n";
	echo '<div class="form-group">'. "\n";
	echo '<label class="sr-only" for="modlgn-username">'.JText::_('MOD_LOGIN_VALUE_USERNAME') .'</label>'. "\n";
	echo '<input id="ph-modlgn-username" type="text" name="username" class="form-control" tabindex="0" placeholder="'.JText::_('MOD_LOGIN_VALUE_USERNAME') .'" />'. "\n";
	echo ' </div>'. "\n";
	echo ' <div class="form-group">'. "\n";
	echo '<label class="sr-only" for="modlgn-passwd">'.JText::_('JGLOBAL_PASSWORD') .'</label>'. "\n";
	echo '<input id="ph-modlgn-passwd" type="password" name="password" class="form-control" tabindex="0" size="18" placeholder="'.JText::_('JGLOBAL_PASSWORD') .'" />'. "\n";
	echo ' </div>'. "\n";

	if (JPluginHelper::isEnabled('system', 'remember')) {
		echo '<div id="ph-form-login-remember" class="checkbox">'. "\n";
		echo '<label for="modlgn-remember" class="control-label">'. JText::_('MOD_LOGIN_REMEMBER_ME') .'</label> <input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>'. "\n";
		echo '</div>'. "\n";
	}
	echo '<button type="submit" tabindex="0" name="Submit" class="btn btn-primary">'. JText::_('JLOGIN') .'</button>'. "\n";
	echo '</div>'. "\n";// end form inline


	echo '<ul class="unstyled ph-li-inline">'. "\n";
	echo '<li><a href="'.JRoute::_('index.php?option=com_users&view=remind&Itemid='.UsersHelperRoute::getRemindRoute()).'">'.JText::_('MOD_LOGIN_FORGOT_YOUR_USERNAME').'</a></li>'. "\n";
	echo '<li><a href="'.JRoute::_('index.php?option=com_users&view=reset&Itemid='.UsersHelperRoute::getResetRoute()).'">'.JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD').'</a></li>'. "\n";
	echo '</ul>'. "\n";
	echo '<div class="ph-cb"></div>';
	
	echo '<input type="hidden" name="option" value="com_users" />'. "\n";
	echo '<input type="hidden" name="task" value="user.login" />'. "\n";
	echo '<input type="hidden" name="return" value="'.$this->t['actionbase64'].'" />'. "\n";
	echo JHtml::_('form.token');
	echo '</form>';
	
	
	echo '</div>'. "\n";// end columns
	

	echo '<div class="col-sm-4 col-md-4 ph-left-border">';
	echo '<div class="ph-box-header">'.JText::_('COM_PHOCACART_REGISTER').'</div>'. "\n";
	$usersConfig = JComponentHelper::getParams('com_users');
	//echo '<ul class="unstyled">'. "\n";
	if ($usersConfig->get('allowUserRegistration')) {
		//echo '<li><a href="'. JRoute::_('index.php?option=com_users&view=registration&Itemid='.UsersHelperRoute::getRegistrationRoute()).'">'.JText::_('MOD_LOGIN_REGISTER').'<span class="icon-arrow-right"></span></a></li>'. "\n";
		
		echo '<a class="btn btn-primary btn-sm ph-account-btn-login" href="'. JRoute::_('index.php?option=com_users&view=registration&Itemid='.UsersHelperRoute::getRegistrationRoute()).'"><span class="glyphicon glyphicon-user"></span>  '.JText::_('MOD_LOGIN_REGISTER').'</a>'. "\n";
		
	}
	//echo '</ul>'. "\n";
	


	echo '</div>'. "\n";// end columns
	echo '<div class="ph-cb"></div>';
	echo '</div>'. "\n";// end account box login

	echo '</form>'. "\n";

}


echo '</div>';
echo '<div>&nbsp;</div>';
echo '<div>&nbsp;</div>';
echo PhocacartUtils::getInfo();
?>