<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
class PhocaCartOrdering
{
	public static function getOrderingText ($ordering, $type = 0) {
	
		switch ($type) {
			case 1:// CATEGORY
				switch ((int)$ordering) {
					case 2: $orderingOutput	= 'c.ordering DESC';break;
					case 3: $orderingOutput	= 'c.title ASC'; break;
					case 4:$orderingOutput	= 'c.title DESC';break;
					case 5:$orderingOutput	= 'c.date ASC';break;
					case 6:$orderingOutput	= 'c.date DESC';break;
					case 1: default: $orderingOutput = 'c.ordering ASC'; break;
				}
			break;
			
			case 2:// ORDERS
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 'o.ordering DESC';break;
					case 3:$orderingOutput	= 'o.title ASC';break;
					case 4:$orderingOutput	= 'o.title DESC';break;
					case 5:$orderingOutput	= 'o.price ASC';break;
					case 6:$orderingOutput	= 'o.price DESC';break;
					case 7:$orderingOutput	= 'o.date ASC';break;
					case 8:$orderingOutput	= 'o.date DESC';break;
					case 1:default:$orderingOutput = 'o.ordering ASC';break;
				}
			break;
			
			default://PRODUCTS
				switch ((int)$ordering) {
					case 2:$orderingOutput	= 'a.ordering DESC';break;
					case 3:$orderingOutput	= 'a.title ASC';break;
					case 4:$orderingOutput	= 'a.title DESC';break;
					case 5:$orderingOutput	= 'a.price ASC';break;
					case 6:$orderingOutput	= 'a.price DESC';break;
					case 7:$orderingOutput	= 'a.date ASC';break;
					case 8:$orderingOutput	= 'a.date DESC';break;
					case 9:$orderingOutput	= 'rating ASC';break;
					case 10:$orderingOutput	= 'rating DESC';break;
					case 1:default:$orderingOutput = 'a.ordering ASC';break;
				}
			break;
		}	
		return $orderingOutput;
	}
	
	public static function renderOrderingFront( $selected, $type = 1) {
		
		switch($type) {
			case 2:
				$typeOrdering 	= self::getOrderingCategoryArray();
				$ordering		= 'catordering';
			break;
			
			default:
				$typeOrdering 	= self::getOrderingItemArray();
				$ordering		= 'itemordering';
			break;
		}

		$html 	= JHTML::_('select.genericlist',  $typeOrdering, $ordering, 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', $selected);
		
		return $html;
	}
		
	public static function getOrderingItemArray() {
		$itemOrdering	= array(
				1 => JText::_('COM_PHOCACART_ORDERING_ASC'),
				2 => JText::_('COM_PHOCACART_ORDERING_DESC'),
				3 => JText::_('COM_PHOCACART_TITLE_ASC'),
				4 => JText::_('COM_PHOCACART_TITLE_DESC'),
				5 => JText::_('COM_PHOCACART_PRICE_ASC'),
				6 => JText::_('COM_PHOCACART_PRICE_DESC'),
				7 => JText::_('COM_PHOCACART_DATE_ASC'),
				8 => JText::_('COM_PHOCACART_DATE_DESC'),
				9 => JText::_('COM_PHOCACART_RATING_ASC'),
				10 => JText::_('COM_PHOCACART_RATING_DESC'));
		return $itemOrdering;
	}
	
	/*public static function getOrderingCategoryArray() {
		$itemOrdering	= array(
				1 => JText::_('COM_PHOCACART_ORDERING_ASC'),
				2 => JText::_('COM_PHOCACART_ORDERING_DESC'),
				3 => JText::_('COM_PHOCACART_TITLE_ASC'),
				4 => JText::_('COM_PHOCACART_TITLE_DESC'),
				5 => JText::_('COM_PHOCACART_DATE_ASC'),
				6 => JText::_('COM_PHOCACART_DATE_DESC'),
				//7 => JText::_('COM_PHOCACART_ID_ASC'),
				//8 => JText::_('COM_PHOCACART_ID_DESC'),
				11 => JText::_('COM_PHOCACART_COUNT_ASC'),
				12 => JText::_('COM_PHOCACART_COUNT_DESC'),
				13 => JText::_('COM_PHOCACART_AVERAGE_ASC'),
				14 => JText::_('COM_PHOCACART_AVERAGE_DESC'),
				15 => JText::_('COM_PHOCACART_HITS_ASC'),
				16 => JText::_('COM_PHOCACART_HITS_DESC'));
		return $itemOrdering;
	}*/
}
?>