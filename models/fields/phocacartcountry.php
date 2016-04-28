<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocaCartCountry extends JFormField
{
	protected $type 		= 'PhocaCartCountry';

	protected function getInput() {
		
		$db = JFactory::getDBO();

       //build the list of categories
		$query = 'SELECT a.title AS text, a.id AS value'
		. ' FROM #__phocacart_countries AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$data = $db->loadObjectList();
	
	
		
		
		$required	= ((string) $this->element['required'] == 'true') ? TRUE : FALSE;
		
		
		array_unshift($data, JHTML::_('select.option', '', '- '.JText::_('COM_PHOCACART_SELECT_COUNTRY').' -', 'value', 'text'));

		return JHTML::_('select.genericlist',  $data,  $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->id );
	}
}
?>