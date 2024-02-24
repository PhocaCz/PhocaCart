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
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$r          = $this->r;
$app        = Factory::getApplication();
$input      = $app->input;
$isModal    = $input->get('layout') == 'modal' ? true : false;
$layout     = $isModal ? 'modal' : 'edit';
$tmpl       = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? 'component' : '';
$assoc      = Associations::isEnabled();

echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm', '', $layout, $tmpl);

echo LayoutHelper::render('joomla.edit.title_alias', $this);

echo '<div class="main-card">';

$skipFieldsets = ['title', 'publish', 'item_associations'];

echo $r->startTabs();
foreach ($this->form->getFieldSets() as $fieldset) {
	if (in_array($fieldset->name, $skipFieldsets)) {
		continue;
	}

	if (($fieldset->group ?? null) == 'pcf') {
        // Legacy Feed plugins
		continue;
	}

	echo $r->startTab($fieldset->name, Text::_($fieldset->label));
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
	} elseif ($fieldset->name == 'feed') {
        // Legacy Feed plugins
		echo $this->loadTemplate('feed');
	}
	echo $r->endTab();
}

if (!$isModal && $assoc) {
	echo $r->startTab('associations', Text::_('COM_PHOCACART_ASSOCIATIONS'));
	echo $this->loadTemplate('associations');
	echo $r->endTab();
} else if ($isModal && $assoc) {
	echo '<div class="hidden">'. $this->loadTemplate('associations').'</div>';
}

echo $r->endTabs();
echo '</div>';

echo $r->formInputs($this->t['task']);
if ($forcedLanguage = Factory::getApplication()->input->getCmd('forcedLanguage')) {
	echo '<input type="hidden" name="forcedLanguage" value="' . $forcedLanguage . '" />';
}

echo $r->endForm();
