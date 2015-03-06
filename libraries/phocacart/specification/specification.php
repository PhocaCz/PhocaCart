<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocaCartSpecification
{
	public static function getSpecificationsById($productId) {
	
		$db = JFactory::getDBO();
		
		$query = 'SELECT a.id, a.title, a.value, a.group_id'
				.' FROM #__phocacart_specifications AS a'
			    .' WHERE a.product_id = '.(int) $productId;
		$db->setQuery($query);
		$specifications = $db->loadObjectList();
		return $specifications;
	}
	
	public static function getGroupArray() {
	
		$db = JFactory::getDBO();
		
		$query = 'SELECT id, title'
				.' FROM #__phocacart_specification_groups'
			    .' ORDER by ordering';
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		$groupsA = array();
		if (!empty($groups)) {
			foreach($groups as $k => $v) {
				$groupsA[$v->id] = $v->title;
			}
		}
		
		return $groupsA;
	}
	
	
	public static function storeSpecificationsById($productId, $specsArray) {
	
		if ((int)$productId > 0) {
			$db =JFactory::getDBO();
			
			$query = ' DELETE '
					.' FROM #__phocacart_specifications'
					. ' WHERE product_id = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();
			
			if (!empty($specsArray)) {
				$values 	= array();
				foreach($specsArray as $k => $v) {
					$values[] 	= '('.(int)$productId.', '.$db->quote($v['title']).', '.$db->quote($v['value']).', '.(int)$v['group_id'].')';
				}
				
				if (!empty($values)) {
					$valuesString = implode($values, ',');
					$query = ' INSERT INTO #__phocacart_specifications (product_id, title, value, group_id)'
							.' VALUES '.(string)$valuesString;
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	
	public static function getSpecificationGroupsAndSpecifications($productId) {
		
		$db = JFactory::getDBO();
		
		$query = 'SELECT s.id, s.title, s.value, g.id as groupid, g.title as grouptitle'
				.' FROM #__phocacart_specifications AS s'
				.' LEFT JOIN #__phocacart_specification_groups AS g ON g.id = s.group_id'
				.' WHERE s.product_id = '.(int)$productId
			    .' ORDER by g.ordering';
		$db->setQuery($query);
		$specs = $db->loadObjectList();
		
		$specsA = array();
		if (!empty($specs)){
			foreach ($specs as $k => $v) {
				$specsA[$v->groupid][0] = $v->grouptitle;
				$specsA[$v->groupid][$v->id]['title'] = $v->title;
				$specsA[$v->groupid][$v->id]['value'] = $v->value;
			}
		}
		return $specsA;
	
	}
}
?>