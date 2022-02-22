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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldPhocacartSpecificationGroups extends FormField
{
	protected $type 		= 'PhocacartSpecificationGroups';

	protected function getInput() {

		$required		= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;
		$multiple		= ((string) $this->element['multiple'] == 'true') ? TRUE : FALSE;
		$class			= ((string) $this->element['class'] != '') ? 'class="'.$this->element['class'].'"' : 'class="form-select"';

		//$id = (int) $this->form->getValue('id');
		$attr		= '';
		$attr		.= $class . ' ';
		if ($multiple) {
			$attr		.= 'size="4" multiple="multiple" ';
		}
		if ($required) {
			$attr		.= 'required aria-required="true" ';
		}

		$attr 		.= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'" ' : ' ';

		$activeAttributes = array();
		$attributes = array();
		//if ((int)$id > 0) {
			$attributes	= PhocacartSpecification::getGroupArray();
		//}


		if (!$multiple) {
			array_unshift($attributes, HTMLHelper::_('select.option', '', '- ' . Text::_('COM_PHOCACART_SELECT_GROUP') . ' -', 'value', 'text'));
			return HTMLHelper::_('select.genericlist',  $attributes,  $this->name, $attr, 'value', 'text', $this->value, $this->id );
		} else {


			$data               = $this->getLayoutData();
			$data['options']    = (array)$attributes;
			$data['value']      = $this->value;

			return $this->getRenderer($this->layout)->render($data);
		}



	}
}
?>
