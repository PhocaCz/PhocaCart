<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocacartManufacturer
{	
	public static function getAllManufacturers($ordering = 1) {
	
		$db 			= JFactory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 4);
		$query = 'SELECT m.id, m.title, m.alias FROM #__phocacart_manufacturers AS m WHERE m.published = 1 ORDER BY '.$orderingText;
		$db->setQuery($query);
		$tags = $db->loadObjectList();	
	
		return $tags;
	}

}
?>