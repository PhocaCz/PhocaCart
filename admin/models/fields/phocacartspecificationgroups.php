<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocacartSpecificationGroups extends JFormField
{
	protected $type 		= 'PhocacartSpecificationGroups';

	protected function getInput() {

		$required		= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;
		$multiple		= ((string) $this->element['multiple'] == 'true') ? TRUE : FALSE;
		$class			= ((string) $this->element['class'] != '') ? 'class="'.$this->element['class'].'"' : 'class="inputbox"';

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
		//if ((int)$id > 0) {
			$activeAttributes	= PhocacartSpecification::getGroupArray();
		//}

		if (!$multiple) {
			array_unshift($activeAttributes, Joomla\CMS\HTML\HTMLHelper::_('select.option', '', '- ' . JText::_('COM_PHOCACART_SELECT_GROUP') . ' -', 'value', 'text'));
		}

		return Joomla\CMS\HTML\HTMLHelper::_('select.genericlist',  $activeAttributes,  $this->name, $attr, 'value', 'text', $this->value, $this->id );

	}
}
?>
