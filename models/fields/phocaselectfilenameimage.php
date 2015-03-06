<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('JPATH_BASE') or die();
jimport('joomla.form.formfield');

class JFormFieldPhocaSelectFilenameImage extends JFormField
{
	public $type = 'PhocaSelectFileNameImage';

	protected function getInput() {
		$html 			= array();
		$managerOutput	= $this->element['manager'] ? '&amp;manager='.(string) $this->element['manager'] : '';
		$group 			= PhocaCartSettings::getManagerGroup((string) $this->element['manager']);
		$textButton		= 'COM_PHOCACART_FORM_SELECT_'.strtoupper($group['t']);
		$link 			= 'index.php?option=com_phocacart&amp;view=phocacartmanager'.$group['c'].$managerOutput.'&amp;field='.$this->id;

		$attr 		= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr 		.= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
		$onchange 	= (string) $this->element['onchange'];

		JHtml::_('behavior.modal', 'a.modal_'.$this->id);
		
		$html[] = '<div class="input-append">';
		$html[] = '<input class="imageCreateThumbs" type="text" id="'.$this->id.'" name="'.$this->name.'" value="'. $this->value.'"' .' '.$attr.' />';
		$html[] = '<a class="modal_'.$this->id.' btn" title="'.JText::_($textButton).'"'
				.' href="'.($this->element['readonly'] ? '' : $link).'"'
				.' rel="{handler: \'iframe\', size: {x: 780, y: 560}}">'
				. JText::_($textButton).'</a>';
		$html[] = '</div>'. "\n";

		return implode("\n", $html);
	}
}
?>