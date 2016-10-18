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
		
		$onchange 		= (string) $this->element['onchange'];
		$size     		= ($v = $this->element['size']) ? ' size="' . $v . '"' : '';
		$class    		= ($v = $this->element['class']) ? ' class="' . $v . '"' : 'class="text_area"';
		$required 		= ($v = $this->element['required']) ? ' required="required"' : '';
		$idA			= 'phFileImageNameModal';

		JHtml::_('jquery.framework');
		
		$html[] = '<span class="input-append"><input type="text" ' . $required . ' id="' . $this->id . '" name="' . $this->name . '"'
			. ' value="' . $this->value . '"' . $size . $class . ' />';
		$html[] = '<a href="#'.$idA.'" role="button" class="btn btn-primary" data-toggle="modal" title="' . JText::_($textButton) . '">'
			. '<span class="icon-list icon-white"></span> '
			. JText::_($textButton) . '</a></span>';
		$html[] = JHtml::_(
			'bootstrap.renderModal',
			$idA,
			array(
				'url'    => $link,
				'title'  => JText::_($textButton),
				'width'  => '700px',
				'height' => '400px',
				'modalWidth' => '80',
				'bodyHeight' => '70',
				'footer' => '<button type="button" class="btn" data-dismiss="modal" aria-hidden="true">'
					. JText::_('COM_PHOCACART_CLOSE') . '</button>'
			)
		);

		return implode("\n", $html);
		
		// HIDDEN FIELDS
		//readonly="readonly"
		// We don't use hidden field name, we can edit it the filename form field, there are three ways of adding filename:
		// - manually typed
		// - selected by image select box
		// - added per YouTube import
		//
		// The name="' . $this->name . '" is used above in standard input form
		//
		//$html[] = '<input class="input-small" type="hidden" name="' . $this->name . '" value="'
		//	. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '" />';

		// MOOTOOLS
		/*JHtml::_('behavior.modal', 'a.modal_'.$this->id);
		
		$html[] = '<div class="input-append">';
		$html[] = '<input class="imageCreateThumbs" type="text" id="'.$this->id.'" name="'.$this->name.'" value="'. $this->value.'"' .' '.$attr.' />';
		$html[] = '<a class="modal_'.$this->id.' btn" title="'.JText::_($textButton).'"'
				.' href="'.($this->element['readonly'] ? '' : $link).'"'
				.' rel="{handler: \'iframe\', size: {x: 780, y: 560}}">'
				. JText::_($textButton).'</a>';
		$html[] = '</div>'. "\n";

		return implode("\n", $html);*/
	}
}
?>