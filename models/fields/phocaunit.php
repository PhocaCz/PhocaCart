<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocaUnit extends JFormField
{
	protected $type 		= 'PhocaUnit';

	protected function getInput() {
	
		$units = array();
		if ($this->element['table']) {
			$units = PhocacartUtilsSettings::getUnit(0, $this->element['table']);
		}
		
		$unitsA = array();
		foreach ($units as $k => $v) {
			$unitsA[$k] = JText::_($v[0]);
		}
		
		
		return JHTML::_('select.genericlist',  $unitsA,  $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->id );
	}
}
?>