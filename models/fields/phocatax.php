<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */
defined('_JEXEC') or die();
class JFormFieldPhocaTax extends JFormField
{
	protected $type 		= 'PhocaTax';
	
	protected function getInput() {
		$db = JFactory::getDBO();

		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_taxes AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();
		
		if (!empty($data)) {
			foreach($data as $k => $v) {
				$data[$k]->text = JText::_($v->text);
			}
		}
		
		array_unshift($data, JHTML::_('select.option', '', '- '.JText::_('COM_PHOCACART_SELECT_TAX').' -', 'value', 'text'));
		return JHTML::_('select.genericlist',  $data,  $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->id );
	}
}
?>