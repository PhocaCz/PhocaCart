<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocaFormRegion extends JFormField
{
	protected $type 		= 'PhocaFormRegion';

	protected function getInput() {
		
		$db = JFactory::getDBO();

		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_regions AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();
		
		
		$attr = '';
		$attr .= !empty($this->class) ? ' class="' . $this->class . ' form-control chosen-select ph-input-select-region"' : 'class="form-control chosen-select ph-input-select-region"';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';
		
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true' || (string) $this->disabled == '1'|| (string) $this->disabled == 'true') {
			$attr .= ' disabled="disabled"';
		}
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';
		
		
		array_unshift($data, JHtml::_('select.option', '', '-&nbsp;'.JText::_('COM_PHOCACART_SELECT_REGION').'&nbsp;-', 'value', 'text'));

		return JHtml::_('select.genericlist',  $data,  $this->name, trim($attr), 'value', 'text', $this->value, $this->id );
	}
}
?>