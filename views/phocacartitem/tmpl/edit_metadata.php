<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$fieldSets = $this->form->getFieldsets('metadata'); 

foreach ($fieldSets as $name => $fieldSet) :
	?>
	<fieldset class="panelform">
	<div class="adminform">
		<?php if ($name == 'metadata') : // Include the real fields in this panel. ?>
			<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('metatitle'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('metatitle'); ?></div></div>
			<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('metadesc'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('metadesc'); ?></div></div>
			<div class="control-group">
			<div class="control-label"><?php echo $this->form->getLabel('metakey'); ?></div>
			<div class="controls"><?php echo $this->form->getInput('metakey'); ?></div></div>
		<?php endif; ?>
		<?php foreach ($this->form->getFieldset($name) as $field) : ?>
			<div class="control-group">
			<div class="control-label"><?php echo $field->label; ?></div>
			<div class="controls"><?php echo $field->input; ?></div></div>
		<?php endforeach; ?>
		</div>
	</fieldset>
<?php endforeach; ?>