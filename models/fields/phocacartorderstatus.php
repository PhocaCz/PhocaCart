<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocaCartOrderStatus extends JFormField
{
	protected $type 		= 'PhocaCartOrderStatus';

	protected function getInput() {
		
		$id = (int) $this->form->getValue('status_id');
		
		if ($id < 1) {
			$id = 1;// set default "pending"
		}
		
		$status = PhocaCartOrderStatus::getStatus($id);
		
		//array_unshift($status['data'], JHTML::_('select.option', '', '- '.JText::_('COM_PHOCACART_SET_STATUS').' -', 'value', 'text'));

		return JHTML::_('select.genericlist',  $status['data'],  $this->name, 'class="inputbox"', 'value', 'text', $this->value, $this->id );
	
	}
}
?>