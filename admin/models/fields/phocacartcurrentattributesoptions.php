<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

class JFormFieldPhocaCartCurrentAttributesOptions extends FormField
{
	protected $type 		= 'PhocaCartCurrentAttributes';

	protected function getInput() {


		$html 			= array();
		$typeView		= $this->element['typeview'] ? (string)$this->element['typeview'] : 'attribute';

		$textButton = 'COM_PHOCACART_CURRENT_ATTRIBUTES';
		if ($typeView == 'option') {
			$textButton = 'COM_PHOCACART_CURRENT_OPTIONS';
		}

		// Set variable {field-id} because of dynamically created items
		// {field-id} will be replaced by dynamically created ID of input closest to button which will fire the modal box
		$link 			= 'index.php?option=com_phocacart&amp;view=phocacarteditcurrentattributesoptions&amp;tmpl=component&amp;typeview='.$typeView.'&amp;field={ph-field-id}&amp;fieldparent={ph-field-parent-id}';

		$size     		= ($v = $this->element['size']) ? ' size="' . $v . '"' : '';
		$class    		= ($v = $this->element['class']) ? ' class="' . $v . '"' : 'class="form-select"';
		$required 		= ($v = $this->element['required']) ? ' required="required"' : '';


		$idA      = 'phCurrentAttributesOptions';
		$w          = 500;
		$h          = 400;

		HTMLHelper::_('jquery.framework');

		// Each Group is defined by its $idA
		// And can have event in phocacartform.js

		// 1) phocacartform.js - loads click event to run modal window - NO NEED TO DEFINE EVENT HERE
        // 2) phocacartform.js - creates modal window, then loads the iframe with url - NO NEED TO LOAD MODAL WINDOW AND TO BUILD IT HERE

		// jQuery(document).on("click", "a.phProductFileModalButton", function (e) {

		// No standard settings for modal, because we call modal from Javascript, not from button
		// ... href is not a link
		// ... there is nod data-bs-modal or data-bs-target

		$idAC = $idA.'ModalName'. $this->id;

		$html[] = '<div class="ph-attributes-options-link-box">';
		$html[] = '<input type="hidden" ' . $required . ' id="' . $this->id . '" name="' . $this->name . '"'
			. ' value="' . $this->value . '"' . $size . $class . ' />';
		// data-id does not work by dynamically added form fields so we need to get the id which is stored in input before the button
		$html[] = ' <a href="#'.$idAC.'" role="button" class="ph-link ph-attributes-options-link '.$idA.'ModalButton"  title="' . Text::_($textButton) . '" data-title="' . Text::_($textButton) . '" data-id="' . $this->id . '" data-src="'.$link.'"  data-height="'.$h.'" data-width="'.$w.'">'
		//	. '<span class="icon-list icon-white"></span> '
			. Text::_($textButton) . '</a>';
		//</span>';
		$html[] = '</div>'. "\n";
		return implode("\n", $html);

	}
}
?>
