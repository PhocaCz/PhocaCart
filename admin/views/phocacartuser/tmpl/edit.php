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
	if (task == "'. $this->t['task'] .'.cancel" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
		Joomla.submitform(task, document.getElementById("adminForm"));
	} else {
		Joomla.renderMessages({"error": ["'. Text::_('JGLOBAL_VALIDATION_FORM_FAILED', true).'"]});
	}
}
';
Factory::getDocument()->addScriptDeclaration($js);

echo '<div id="ph-request-message" style="display:none"></div>';

echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');
// First Column
echo '<div class="col-xs-12 col-sm-12 col-md-12 form-horizontal">';
$tabs = array (
'billing' 		=> Text::_($this->t['l'].'_BILLING_OPTIONS'),
'shipping' 	=> Text::_($this->t['l'].'_SHIPPING_OPTIONS'),
'main' 	=> Text::_($this->t['l'].'_MAIN_OPTIONS'),
'groups' 	=> Text::_($this->t['l'].'_GROUP_OPTIONS'));
echo $r->navigation($tabs);

$data = PhocacartUser::getAddressDataForm($this->formspecific, $this->fields['array'], $this->u);

echo $r->startTabs();


echo $r->startTab('billing', $tabs['billing'], 'active');
echo $data['b'];
echo $r->endTab();


echo $r->startTab('shipping', $tabs['shipping']);
echo $data['s'];
echo $r->endTab();


echo $r->startTab('main', $tabs['main']);
$formArray = array ('loyalty_card_number');
echo $r->group($this->form, $formArray);
echo $r->endTab();


echo $r->startTab('groups', $tabs['groups']);
$formArray = array ('group');
echo $r->group($this->form, $formArray);

echo '<input type="hidden" name="jform[user_id]" id="jform_user_id" value="'.(int)$this->u->id.'" />';
echo $r->endTab();

/*
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
echo '</div>';*/

echo $r->endTabs();
echo '</div>';//end span10
// Second Column
//echo '<div class="col-xs-12 col-sm-2 col-md-2"></div>';//end span2
echo $r->formInputs();
echo $r->endForm();
?>

