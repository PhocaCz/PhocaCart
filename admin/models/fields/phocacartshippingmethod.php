<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class JFormFieldPhocaCartShippingMethod extends JFormField
{
	protected $type 		= 'PhocacartShippingMethod';

	protected function getInput() {

		$id 		= (int) $this->form->getValue('id');
		$type 		= (int)$this->element['typemethod'];
		$required 	= $this->element['required'];


		$attr = 'class="inputbox"';
		if ($required) {
			$attr		.= ' required aria-required="true" ';
		}

		switch ($type) {
			case 2:
				$typeIn = 'a.type IN (0,2)';
			break;

			case 1:
				$typeIn = 'a.type IN (0,1)';
			break;

			default:
				$typeIn = 'a.type IN (0)';
			break;
		}

		$db =JFactory::getDBO();


		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_shipping_methods AS a'
				.' WHERE '.$typeIn
                .' AND published = 1'
				.' ORDER BY a.id';
		$db->setQuery($query);
		$methods = $db->loadObjectList();

		array_unshift($methods, Joomla\CMS\HTML\HTMLHelper::_('select.option', '', '- '.JText::_('COM_PHOCACART_SELECT_SHIPPING_METHOD').' -', 'value', 'text'));

		return Joomla\CMS\HTML\HTMLHelper::_('select.genericlist',  $methods,  $this->name, $attr, 'value', 'text', $this->value, $this->id );

	}
}
?>
