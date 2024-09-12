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
use Joomla\CMS\Layout\LayoutHelper;

$r = $this->r;

echo $r->startForm($this->t['o'], $this->t['task'], $this->item->id, 'adminForm', 'adminForm');

echo LayoutHelper::render('joomla.edit.title_alias', $this);

echo '<div class="main-card">';

$skipFieldsets = ['title', 'publish'];

echo $r->startTabs();
foreach ($this->form->getFieldSets() as $fieldset) {
	if (in_array($fieldset->name, $skipFieldsets)) {
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
	}
	echo $r->endTab();
}

echo $r->endTabs();
echo '</div>';

echo $r->formInputs($this->t['task']);
echo $r->endForm();
