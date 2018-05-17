<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$class		= $this->t['n'] . 'RenderAdminview';
$r 			=  new $class();
?>
<script type="text/javascript">
Joomla.submitbutton = function(task) {

	if (task == '<?php echo $this->t['task'] ?>.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
	else {
		Joomla.renderMessages({"error": ["<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true);?>"]});
	}
}
</script><?php

echo '<div id="ph-request-message" style="display:none"></div>';

echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');
// First Column
echo '<div class="span10 form-horizontal">';
$tabs = array (
'billing' 		=> JText::_($this->t['l'].'_BILLING_OPTIONS'),
'shipping' 	=> JText::_($this->t['l'].'_SHIPPING_OPTIONS'),
'main' 	=> JText::_($this->t['l'].'_MAIN_OPTIONS'),
'groups' 	=> JText::_($this->t['l'].'_GROUP_OPTIONS'));
echo $r->navigation($tabs);

$data = PhocacartUser::getAddressDataForm($this->formspecific, $this->fields['array'], $this->u);

echo '<div class="tab-content">'. "\n";

echo '<div class="tab-pane active" id="billing">'."\n"; 
echo $data['b'];
echo '</div>';

echo '<div class="tab-pane" id="shipping">'."\n"; 
echo $data['s'];
echo '</div>';

echo '<div class="tab-pane" id="main">'."\n"; 
$formArray = array ('loyalty_card_number');
echo $r->group($this->form, $formArray);
echo '</div>';

echo '<div class="tab-pane" id="groups">'."\n"; 

$formArray = array ('group');
echo $r->group($this->form, $formArray);
echo '</div>';

echo '<input type="hidden" name="jform[user_id]" id="jform_user_id" value="'.(int)$this->u->id.'" />'; 

/*
echo '<div class="tab-pane" id="publishing">'."\n"; 
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
				
echo '</div>';//end tab content
echo '</div>';//end span10
// Second Column
echo '<div class="col-xs-12 col-sm-2 col-md-2"></div>';//end span2
echo $r->formInputs();
echo $r->endForm();
?>

