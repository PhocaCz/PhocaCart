<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocaManufacturer extends JFormField
{
	protected $type 		= 'PhocaManufacturer';

		protected function getInput() {
		
		$db = JFactory::getDBO();

		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_manufacturers AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();
		
		array_unshift($data, JHtml::_('select.option', '', '- '.JText::_('COM_PHOCACART_SELECT_MANUFACTURER').' -', 'value', 'text'));
		return JHtml::_('select.genericlist',  $data,  $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->id );
	}
}
?>