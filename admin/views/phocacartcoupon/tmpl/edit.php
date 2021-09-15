<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();


$r 			=  $this->r;
$js ='
Joomla.submitbutton = function(task) {
	if (task == "'. $this->t['task'] .'.cancel" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
		Joomla.submitform(task, document.getElementById("adminForm"));
	} else {
		Joomla.renderMessages({"error": ["'. JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true).'"]});
	}
}
';
JFactory::getDocument()->addScriptDeclaration($js);

echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');
// First Column
echo '<div class="col-xs-12 col-sm-10 col-md-10 form-horizontal">';
$tabs = array (
'general' 		=> JText::_($this->t['l'].'_GENERAL_OPTIONS'),
'rules' 		=> JText::_($this->t['l'].'_RULES'),
'gift' 		=> JText::_($this->t['l'].'_GIFT_VOUCHER_OPTIONS'),
'publishing' 	=> JText::_($this->t['l'].'_PUBLISHING_OPTIONS'));
echo $r->navigation($tabs);

echo $r->startTabs();

echo $r->startTab('general', $tabs['general'], 'active');
$formArray = array ('title', 'code', 'discount', 'calculation_type', 'type', 'access', 'group');
echo $r->group($this->form, $formArray);

$formArray = array('description');
echo $r->group($this->form, $formArray, 1);
echo $r->endTab();


echo $r->startTab('gift', $tabs['general']);
$formArray = array ('gift_title', 'gift_recipient_name', 'gift_recipient_email', 'gift_sender_name', 'gift_sender_message', 'gift_type', 'gift_class_name', 'gift_order_id', 'gift_product_id', 'gift_order_product_id');
echo $r->group($this->form, $formArray);

$formArray = array('gift_description');
echo $r->group($this->form, $formArray, 1);
echo $r->endTab();



echo $r->startTab('rules', $tabs['rules']);
$formArray = array ('total_amount', 'quantity_from', 'available_quantity', 'available_quantity_user', 'product_ids', 'product_filter',  'cat_ids', 'category_filter', 'free_shipping', 'free_payment');
echo $r->group($this->form, $formArray);
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
echo '<div class="col-xs-12 col-sm-2 col-md-2"></div>';//end span2
echo $r->formInputs();
echo $r->endForm();
?>

