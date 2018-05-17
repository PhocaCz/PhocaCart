<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

class PhocacartManufacturer
{	
	public static function getAllManufacturers($ordering = 1, $onlyAvailableProducts = 0) {
	
		$db 			= JFactory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 4);
		
		$wheres		= array();
		$lefts		= array();
		
		$columns		= 'm.id, m.title, m.image, m.alias';
		/*$groupsFull		= $columns;
		$groupsFast		= 'm.id';
		$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;*/
		
		$wheres[]	= ' m.published = 1';
		
		if ($onlyAvailableProducts == 1) {
			$lefts[] = ' #__phocacart_products AS p ON m.id = p.manufacturer_id';
			$rules = PhocacartProduct::getOnlyAvailableProductRules();
			$wheres = array_merge($wheres, $rules['wheres']);
			$lefts	= array_merge($lefts, $rules['lefts']);
		}
		
		$q = ' SELECT DISTINCT '.$columns
			.' FROM  #__phocacart_manufacturers AS m'
			. (!empty($lefts) ? ' LEFT JOIN ' . implode( ' LEFT JOIN ', $lefts ) : '')
			. (!empty($wheres) ? ' WHERE ' . implode( ' AND ', $wheres ) : '')
			//.' GROUP BY '.$groups
			.' ORDER BY '.$orderingText;

		$db->setQuery($q);
		
		$items = $db->loadObjectList();	
	
		return $items;
	}

}
?>