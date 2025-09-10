<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Phoca\PhocaCart\I18n\I18nHelper;

// ASSOCIATION
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
$assoc = I18nHelper::associationsEnabled();
$app = Factory::getApplication();
$input = $app->getInput();
$isModal = $input->get('layout') == 'modal' ? true : false;
$layout = $isModal ? 'modal' : 'edit';
$tmpl = $isModal || $input->getCmd('tmpl') === 'component' ? 'component' : '';

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

echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm', '', $layout, $tmpl);
echo LayoutHelper::render('joomla.edit.title_alias', $this);

echo '<div class="main-card">';

$skipFieldsets = ['title', 'publish'];

echo $r->startTabs();
$isActiveTab = true;
foreach ($this->form->getFieldSets() as $fieldset) {
    if (in_array($fieldset->name, $skipFieldsets)) {
        continue;
    }
    echo $r->startTab($fieldset->name, Text::_($fieldset->label), $isActiveTab ? 'active' : '');
    if ($fieldset->name == 'general') {
        echo '<div class="row">';
        echo '<div class="col-md-9">';
    }
    echo $this->form->renderFieldset($fieldset->name);
    if ($fieldset->name == 'general') {
        echo '</div>';
        echo '<div class="col-md-3">';
        echo $this->form->renderFieldset('publish');
        echo '</div>';
        echo '</div>';
    }
    echo $r->endTab();
    $isActiveTab = false;
}

echo $r->endTabs();
echo '</div>';

echo $r->formInputs($this->t['task']);
if ($forcedLanguage = Factory::getApplication()->getInput()->getCmd('forcedLanguage')) {
    echo '<input type="hidden" name="forcedLanguage" value="' . $forcedLanguage . '" />';
}
echo $r->endForm();
