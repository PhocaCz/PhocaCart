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
use Joomla\CMS\Factory;

class PhocacartUnit
{
	
	public static function existsUnit($unitId, $sectionId) {
		
		$db 	= Factory::getDBO();
		$query = ' SELECT id FROM #__phocacart_units'
				.' WHERE id = '.(int)$unitId
				.' AND section_id = '.(int)$sectionId
				.' AND published = 1';
		$db->setQuery($query);
		$result = $db->loadResult();
		if (isset($result) && (int)$result > 0) {
			return $result;
		}
		return false;
	}
	
	public static function getUnits($sectionId, $limit = 0) {
		
		$db 	= Factory::getDBO();
		$query = ' SELECT a.id, a.title FROM #__phocacart_units AS a'
				.' WHERE a.published = 1'
				.' AND a.section_id ='.(int)$sectionId
				.' ORDER BY a.ordering';
				if ((int)$limit > 0) {
					$query .= ' LIMIT '.(int)$limit;
				}
		$db->setQuery($query);
		$units = $db->loadObjectList();
		
		return $units;
	}
	
	public static function getUnitById($unitId) {
		
		$db 	= Factory::getDBO();
		$query = ' SELECT id, title FROM #__phocacart_units'
				.' WHERE id = '.(int)$unitId
				.' AND published = 1';
		$db->setQuery($query);
		$result = $db->loadObject();
		return $result;
	}

}