<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartManufacturer
{	
	public static function getAllManufacturers() {
	
		$db = JFactory::getDBO();
		
		$query = 'SELECT a.id, a.title, a.alias FROM #__phocacart_manufacturers AS a WHERE a.published = 1 ORDER BY a.id';
		$db->setQuery($query);
		$tags = $db->loadObjectList();	
	
		return $tags;
	}

}
?>