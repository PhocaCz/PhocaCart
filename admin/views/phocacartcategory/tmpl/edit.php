<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Associations;

// ASSOCIATION
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');



$app        = Factory::getApplication();
$input      = $app->input;
$class		= $this->t['n'] . 'RenderAdminview';
$r 			=  new PhocacartRenderAdminview();

// phocacartcategory-form => adminForm
// phocacart security stop loop
$js = '
let phRequestActive = null;

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
		if (task == "'. $this->t['task'] .'.cancel" || task == "phocacartwizard.backtowizard" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
			Joomla.submitform(task, document.getElementById("adminForm"));
		} else {
			Joomla.renderMessages({"error": ["'. Text::_('JGLOBAL_VALIDATION_FORM_FAILED', true).'"]});
		}
	}
}
Joomla.submitbutton = function(task) {
	phCheckRequestStatus(0, task);
}
';

JFactory::getDocument()->addScriptDeclaration($js);

// ASSOCIATION
$assoc = Associations::isEnabled();
// In case of modal
$isModal    = $input->get('layout') == 'modal' ? true : false;
$layout     = $isModal ? 'modal' : 'edit';
$tmpl       = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? 'component' : '';

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('details', 'item_associations', 'jmetadata');

// phocacartcategory-form => adminForm
echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm', '', $layout, $tmpl);
// First Column
echo '<div class="col-xs-12 col-sm-12 col-md-12 form-horizontal">';
$tabs = array (
'general' 		=> Text::_($this->t['l'].'_GENERAL_OPTIONS'),
'publishing' 	=> Text::_($this->t['l'].'_PUBLISHING_OPTIONS'),
'feed'           => Text::_($this->t['l'] . '_FEED_OPTIONS'),
'metadata'		=> Text::_($this->t['l'].'_METADATA_OPTIONS'));
if (!$isModal && $assoc) {
    $tabs['associations']          = Text::_($this->t['l'].'_ASSOCIATIONS');
}

echo $r->navigation($tabs);

$formArray = array ('title', 'alias');
echo $r->groupHeader($this->form, $formArray);

echo $r->startTabs();

echo $r->startTab('general', $tabs['general'], 'active');
$formArray = array ('title_long', 'image', 'icon_class', 'parent_id', 'type', 'ordering', 'access', 'group', 'title_feed', 'type_feed');
echo $r->group($this->form, $formArray);
$formArray = array('description');
echo $r->group($this->form, $formArray, 1);
$formArray = array ('special_parameter', 'special_image');
echo $r->group($this->form, $formArray);

// ASSOCIATION
$this->form->setFieldAttribute('id', 'type', 'hidden');
$formArray = array ('id');
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

// FEED
echo $r->startTab('feed', $tabs['feed']);
echo $this->loadTemplate('feed');
echo $r->endTab();

echo $r->startTab('metadata', $tabs['metadata']);
echo $this->loadTemplate('metadata');
echo $r->endTab();

// ASSOCIATION
$assoc = Associations::isEnabled();

if (!$isModal && $assoc) {
	echo $r->startTab('associations', $tabs['associations']);
    echo $this->loadTemplate('associations');
    echo $r->endTab();
} else if ($isModal && $assoc) {
    echo '<div class="hidden">'. $this->loadTemplate('associations').'</div>';
}


echo $r->endTabs();
echo '</div>';//end span10
// Second Column
//echo '<div class="col-xs-12 col-sm-2 col-md-2"></div>';//end span2
echo $r->formInputs($this->t['task']);

if ($forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'CMD')) {
    echo '<input type="hidden" name="forcedLanguage" value="' . $forcedLanguage . '" />';
}

echo $r->endForm();

?>
