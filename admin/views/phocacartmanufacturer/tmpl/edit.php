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
JFactory::getDocument()->addScriptDeclaration($js);

echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');
// First Column
echo '<div class="col-xs-12 col-sm-12 col-md-12 form-horizontal">';
$tabs = [];
foreach($this->form->getFieldSets() as $name => $fieldset) {
	if (in_array($name, ['header'])) {
		continue;
	}
	$tabs[$name] = Text::_($fieldset->label);
}
echo $r->navigation($tabs);

$formArray = [];
foreach ($this->form->getFieldSet('header') as $field) {
	$formArray[] = $field->fieldname;
}
echo $r->groupHeader($this->form, $formArray);

echo $r->startTabs();
foreach($this->form->getFieldSets() as $name => $fieldset) {
	if (in_array($name, ['header'])) {
		continue;
	}
	echo $r->startTab($name, Text::_($fieldset->label), 'active');
	echo $this->form->renderFieldSet($name);
	echo $r->endTab();
}

echo $r->endTabs();
echo '</div>';//end span10

echo $r->formInputs();
echo $r->endForm();
?>
