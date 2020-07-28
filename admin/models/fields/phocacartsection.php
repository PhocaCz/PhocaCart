<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocacartSection extends JFormField
{
	protected $type 		= 'PhocacartSection';

	protected function getInput() {
		
		$attr = '';
        $attr .= $this->element['size'] ? ' size="' . (int)$this->element['size'] . '"' : '';
        $attr .= $this->element['maxlength'] ? ' maxlength="' . (int)$this->element['maxlength'] . '"' : '';
        $attr .= $this->element['class'] ? ' class="' . (string)$this->element['class'] . '"' : '';
        $attr .= ((string)$this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
        $attr .= ((string)$this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $attr .= $this->element['onchange'] ? ' onchange="' . (string)$this->element['onchange'] . '" ' : ' ';
		$required	= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;
		
		$db = JFactory::getDBO();

       //build the list of categories
		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_sections AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();
	
		
		if ($required) {
			$attr		.= ' required aria-required="true" ';
		}
		
		
		
		
		array_unshift($data, Joomla\CMS\HTML\HTMLHelper::_('select.option', '', '- '.JText::_('COM_PHOCACART_SELECT_SECTION').' -', 'value', 'text'));

		return Joomla\CMS\HTML\HTMLHelper::_('select.genericlist',  $data,  $this->name, $attr, 'value', 'text', $this->value, $this->id );
	}
}
?>
