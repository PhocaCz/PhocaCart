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

		//$id = (int) $this->form->getValue('id');
		$attr = '';
		$activeAttributes = array();
		//if ((int)$id > 0) {
			$activeAttributes	= PhocacartSpecification::getGroupArray();
		//}

		array_unshift($activeAttributes, Joomla\CMS\HTML\HTMLHelper::_('select.option', '', '- '.JText::_('COM_PHOCACART_SELECT_GROUP').' -', 'value', 'text'));
		return Joomla\CMS\HTML\HTMLHelper::_('select.genericlist',  $activeAttributes,  $this->name, $attr, 'value', 'text', $this->value, $this->id );

	}
}
?>
