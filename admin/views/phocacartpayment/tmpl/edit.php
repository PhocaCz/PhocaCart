<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;


$r 			=  $this->r;
$js ='
Joomla.submitbutton = function(task) {
	if (task == "'. $this->t['task'] .'.cancel" || task == "phocacartwizard.backtowizard" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
		Joomla.submitform(task, document.getElementById("adminForm"));
	} else {
		Joomla.renderMessages({"error": ["'. Text::_('JGLOBAL_VALIDATION_FORM_FAILED', true).'"]});
	}
}
';
JFactory::getDocument()->addScriptDeclaration($js);
echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');
// First Column
echo '<div class="col-xs-12 col-sm-12 col-md-12 form-horizontal">';
$tabs = array (
'general' 		=> Text::_($this->t['l'].'_GENERAL_OPTIONS'),
'amount' 		=> Text::_($this->t['l'].'_AMOUNT_RULE'),
'zone' 			=> Text::_($this->t['l'].'_ZONE_RULE'),
'country' 		=> Text::_($this->t['l'].'_COUNTRY_RULE'),
'region' 		=> Text::_($this->t['l'].'_REGION_RULE'),
'shipping' 		=> Text::_($this->t['l'].'_SHIPPING_RULE'),
'method' 		=> Text::_($this->t['l'].'_PAYMENT_METHOD_OPTIONS'),
'publishing' 	=> Text::_($this->t['l'].'_PUBLISHING_OPTIONS'));
echo $r->navigation($tabs);

$formArray = array ('title');
echo $r->groupHeader($this->form, $formArray);

echo $r->startTabs();

echo $r->startTab('general', $tabs['general'], 'active');
$formArray = array ('cost', 'cost_additional', 'tax_id', 'code', 'calculation_type', 'default', 'type');
echo $r->group($this->form, $formArray);

$formArray = array ('method');
echo $r->group($this->form, $formArray);
echo '<div id="ph-extended-params-msg" class="ph-extended-params-msg"></div>';

$formArray = array ('image', 'ordering', 'access', 'group');
echo $r->group($this->form, $formArray);
$formArray = array('description');
echo $r->group($this->form, $formArray, 1);
$formArray = array('description_info');
echo $r->group($this->form, $formArray, 1);
echo $r->endTab();



echo $r->startTab('amount', $tabs['amount']);
$formArray = array ('lowest_amount', 'highest_amount', 'active_amount');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('zone', $tabs['zone']);
$formArray = array ('zone', 'active_zone');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('country', $tabs['country']);
$formArray = array ('country', 'active_country');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('region', $tabs['region']);
$formArray = array ('region', 'active_region');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('shipping', $tabs['shipping']);
$formArray = array ('shipping', 'active_shipping');
echo $r->group($this->form, $formArray);
echo $r->endTab();



echo $r->startTab('method', $tabs['method']);
echo '<div id="ph-sandbox-msg" class="ph-float-right ph-admin-additional-box ph-box-warning">'.Text::_('COM_PHOCACART_SANDBOX_ENABLED_NO_REAL_MONEY_WILL_BE_TRANSFERRED').'</div>';
echo '<div id="ph-extended-params" class="ph-extended-params">'.Text::_('COM_PHOCACART_SELECT_PAYMENT_METHOD_TO_DISPLAY_PARAMETERS').'</div>';
echo $r->endTab();

echo $r->startTab('publishing', $tabs['publishing']);
foreach($this->form->getFieldset('publish') as $field) {
	echo '<div class="control-group">';
	if (!$field->hidden) {
		echo '<div class="control-label">'.$field->label.'</div>';
	}
	echo '<div class="controls">';
	echo $field->input;
	echo '</div></div>';
}
echo $r->endTab();

echo $r->endTabs();
echo '</div>';//end span10
// Second Column
//echo '<div class="col-xs-12 col-sm-2 col-md-2">';
//echo '<div id="ph-sandbox-msg" class="alert alert-danger">'.Text::_('COM_PHOCACART_SANDBOX_ENABLED_NO_REAL_MONEY_WILL_BE_TRANSFERRED').'</div>';
//echo '</div>';//end span2
echo $r->formInputs($this->t['task']);
echo $r->endForm();

?>
