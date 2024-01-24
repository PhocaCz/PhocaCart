<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/** @var Form $form */
$form = $this->form;

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

echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');

$translatedTitle = $form->getValue('title') ? '<small>('.Text::_($form->getValue('title')).')</small>' : '';
$formArray = array ('title');
$formArraySuffix = array($translatedTitle);
echo $r->groupHeader($form, $formArray, '', $formArraySuffix, 1);

echo $r->startTabs();

$fieldsets = $form->getFieldsets();
$fieldset = $fieldsets['general'];
echo $r->startTab($fieldset->name, Text::_($fieldset->label), 'active');
?>
<div class="row">
	<div class="col-md-9">
		<?php echo $form->renderFieldset('general'); ?>
	</div>

	<div class="col-md-3">
		<?php echo $form->renderFieldset('publish'); ?>
	</div>
</div>
<?php
echo $r->endTab();

foreach ($fieldsets as $fieldset) {
	if (in_array($fieldset->name, ['header', 'general', 'publish'])) {
		continue;
	}

	echo $r->startTab($fieldset->name, Text::_($fieldset->label));
	echo $form->renderFieldset($fieldset->name);
	echo $r->endTab();
}
echo $r->endTabs();

echo $r->formInputs();
echo $r->endForm();
