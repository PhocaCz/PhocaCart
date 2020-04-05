<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

// ASSOCIATION
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$app        = JFactory::getApplication();
$input      = $app->input;
$class		= $this->t['n'] . 'RenderAdminview';
$r 			=  new PhocacartRenderAdminview();
?>
<script type="text/javascript">
var phRequestActive = null;

function phCheckRequestStatus(i, task) {
	i++;
	if (i > 30) {
		/* Stop Loop */
		phRequestActive = null;
	}

	if (phRequestActive) {
		setTimeout(function(){
			phCheckRequestStatus(i, task);
		}, 1000);
	} else {
		if (task == '<?php echo $this->t['task'] ?>.cancel' || task == 'phocacartwizard.backtowizard' || document.formvalidator.isValid(document.getElementById('phocacartcategory-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('phocacartcategory-form'));
		} else {
			Joomla.renderMessages({"error": ["<?php echo JText::_('JGLOBAL_VALIDATION_FORM_FAILED', true);?>"]});
		}
	}
}
Joomla.submitbutton = function(task) {
	phCheckRequestStatus(0, task);
}
</script><?php

// ASSOCIATION
$assoc = JLanguageAssociations::isEnabled();
// In case of modal
$isModal    = $input->get('layout') == 'modal' ? true : false;
$layout     = $isModal ? 'modal' : 'edit';
$tmpl       = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? 'component' : '';

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('details', 'item_associations', 'jmetadata');

echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'phocacartcategory-form', 'adminForm', '', $layout, $tmpl);
// First Column
echo '<div class="span10 form-horizontal">';
$tabs = array (
'general' 		=> JText::_($this->t['l'].'_GENERAL_OPTIONS'),
'publishing' 	=> JText::_($this->t['l'].'_PUBLISHING_OPTIONS'),
'metadata'		=> JText::_($this->t['l'].'_METADATA_OPTIONS'));
if (!$isModal && $assoc) {
    $tabs['associations']          = JText::_($this->t['l'].'_ASSOCIATIONS');
}

echo $r->navigation($tabs);

echo '<div class="tab-content">'. "\n";

echo '<div class="tab-pane active" id="general">'."\n";
$formArray = array ('title', 'alias', 'image', 'icon_class', 'parent_id', 'type', 'ordering', 'access', 'group', 'title_feed', 'type_feed');
echo $r->group($this->form, $formArray);
$formArray = array('description');
echo $r->group($this->form, $formArray, 1);

// ASSOCIATION
$this->form->setFieldAttribute('id', 'type', 'hidden');
$formArray = array ('id');
echo $r->group($this->form, $formArray);

echo '</div>'. "\n";

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
echo '</div>';

echo '<div class="tab-pane" id="metadata">'. "\n";
echo $this->loadTemplate('metadata');
echo '</div>'. "\n";

// ASSOCIATION
$assoc = JLanguageAssociations::isEnabled();

if (!$isModal && $assoc) {
    echo '<div class="tab-pane" id="associations">' . "\n";
    echo $this->loadTemplate('associations');
    echo '</div>' . "\n";
} else if ($isModal && $assoc) {
    echo '<div class="hidden">'. $this->loadTemplate('associations').'</div>';
}


echo '</div>';//end tab content
echo '</div>';//end span10
// Second Column
echo '<div class="col-xs-12 col-sm-2 col-md-2"></div>';//end span2
echo $r->formInputs($this->t['task']);

if ($forcedLanguage = JFactory::getApplication()->input->get('forcedLanguage', '', 'CMD')) {
    echo '<input type="hidden" name="forcedLanguage" value="' . $forcedLanguage . '" />';
}

echo $r->endForm();
echo PhocacartRenderAdminjs::renderAjaxTopHtml();
?>
