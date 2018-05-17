<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocaCartPaymentMethod extends JFormField
{
	protected $type 		= 'PhocacartPaymentMethod';

	protected function getInput() {
		
		$id 		= (int) $this->form->getValue('id');
		$type 		= (int) $this->element['typemethod'];
		$required 	= $this->element['required'];
		
		$attr = 'class="inputbox"';
		if ($required) {
			$attr		.= ' required aria-required="true" ';
		}

		$db =JFactory::getDBO();
		

		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_payment_methods AS a'
				.' WHERE a.type ='.(int)$type
				.' ORDER BY a.id';
		$db->setQuery($query);
		$methods = $db->loadObjectList();
		
		array_unshift($methods, JHtml::_('select.option', '', '- '.JText::_('COM_PHOCACART_SELECT_PAYMENT_METHOD').' -', 'value', 'text'));

		return JHtml::_('select.genericlist',  $methods,  $this->name, $attr, 'value', 'text', $this->value, $this->id );	
		
	}
}
?>