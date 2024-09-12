<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\Field\TextField;

class JFormFieldPhocaText extends TextField
{
	protected $type 		= 'PhocaText';

	protected function getInput() {

		if (!$this->hidden && ($this->form->getValue('version') == 1)) {
			// Initialize some field attributes.
			$size		= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';
			//$class		= $this->element['class'] ? ' class="'.(string) $this->element['class'].' form-control"' : 'class="form-select"';//Bootstrap3
			$class		= $this->element['class'] ? ' class="'.(string) $this->element['class'].' "' : '';
			$maxLength	= $this->element['maxlength'] ? ' maxlength="'.(int) $this->element['maxlength'].'"' : '';
			$readonly	= ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
			$disabled	= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
			$placeholder= htmlspecialchars($this->getTitle() . ($this->required ? ' ' . Text::_('COM_PHOCAGUESTBOOK_REQUIREDSIGN') : ''), ENT_COMPAT, 'UTF-8');


			// Initialize JavaScript field attributes.
			$onchange	= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';
			$value 		= htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
			$requInput	= $this->required ? ' required="required" aria-required="true"' : '';

			//prepend:
			$preIcon	= $this->element['preicon'] 	? '<i class="' . $this->element['preicon'] . '" title="' . $placeholder . '"></i>' : '';
			$postIcon	= $this->element['posticon'] 	? '<i class="' . $this->element['posticon'] . '"></i>' : '';
			if ($postIcon && $this->element['posthref']) {
				//$postIcon = '<a href="' . (string) $this->element['posthref'] . '" title="' . Text::_('COM_PHOCAGUESTBOOK_RELOAD_IMAGE') . '" class="btn hasTooltip" >' . $postIcon . '</a>';

				$postIcon = '<span class="add-on input-group-addon"><a href="' . (string) $this->element['posthref'] . '" title="' . Text::_('COM_PHOCAGUESTBOOK_RELOAD_IMAGE') . '" class="" >' . $postIcon . '</a></span>';
			}

			// Get the label text from the XML element, defaulting to the element name.
			$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
			$text = $this->translateLabel ? Text::_($text) : $text;

			// Add the opening label tag and main attributes attributes.
			$label = '<label id="' . $this->id . '-lbl" for="' . $this->id . '" class="element-invisible" title="' . $placeholder  . '">' . $text . '</label>';

			return '<span class="add-on input-group-addon">' . $preIcon . $label . '</span>'
		    . '<input'. $requInput. ' type="text" name="'.$this->name.'" id="'.$this->id.'" placeholder="'.$placeholder.'" value="'.$value.'"'
				   .$class.$size.$disabled.$readonly.$onchange.$maxLength.'/> ' . $postIcon;


		} else {
			$postIcon	= $this->element['posticon'] 	? '<i class="' . $this->element['posticon'] . '"></i>' : '';
			if ($postIcon && $this->element['posthref']) {
				$postIcon = '<a href="' . (string) $this->element['posthref'] . '" title="' . Text::_('COM_PHOCAGUESTBOOK_RELOAD_IMAGE') . '" class="btn " >' . $postIcon . '</a>';
			}
			return parent::getInput() . $postIcon;
		}

	}

	protected function getLabel() {

		if (!$this->hidden && ($this->form->getValue('version') == 1)) {
			return '';
		} else {
			return parent::getLabel();
		}
	}

}
?>
