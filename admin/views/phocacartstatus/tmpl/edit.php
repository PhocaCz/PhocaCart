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
'email' 		=> JText::_($this->t['l'].'_EMAIL_OPTIONS'),
'gift' 			=> JText::_($this->t['l'].'_GIFT_VOUCHER_OPTIONS'),
'publishing' 	=> JText::_($this->t['l'].'_PUBLISHING_OPTIONS'));
echo $r->navigation($tabs);

echo $r->startTabs();

echo $r->startTab('general', $tabs['general'], 'active');

$translatedTitle = $this->form->getValue('title') ? '<small>('.JText::_($this->form->getValue('title')).')</small>' : '';
echo $r->item($this->form, 'title', $translatedTitle, 1);

$formArray = array ( 'stock_movements', 'change_user_group', 'change_points_needed', 'change_points_received', 'download');
echo $r->group($this->form, $formArray);

$formArray = array ('ordering');
echo $r->group($this->form, $formArray);

$formArray = array('description');
echo $r->group($this->form, $formArray, 1);
echo $r->endTab();


echo $r->startTab('email', $tabs['email']);

$formArray = array ('email_subject', 'email_customer');
echo $r->group($this->form, $formArray);

$formArray = array('email_text');
echo $r->group($this->form, $formArray, 1);

$formArray = array( 'email_footer');
echo $r->group($this->form, $formArray, 1);

$formArray = array ( 'email_subject_others', 'email_others');
echo $r->group($this->form, $formArray);

$formArray = array( 'email_text_others');
echo $r->group($this->form, $formArray, 1);

$formArray = array ('email_send', 'email_send_format', 'orders_view_display', 'email_attachments');
echo $r->group($this->form, $formArray);
echo $r->endTab();



echo $r->startTab('gift', $tabs['gift']);

$formArray = array ( 'activate_gift', 'email_gift', 'email_subject_gift_sender');
echo $r->group($this->form, $formArray);

$formArray = array ( 'email_text_gift_sender');
echo $r->group($this->form, $formArray, 1);

$formArray = array ( 'email_subject_gift_recipient');
echo $r->group($this->form, $formArray);

$formArray = array ( 'email_text_gift_recipient');
echo $r->group($this->form, $formArray, 1);

$formArray = array ( 'email_gift_format');
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
//echo '</div>';
?>

