<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocacartSpecification
{
	public static function getSpecificationsById($productId, $returnArray = 0) {
	
		$db = JFactory::getDBO();
		
		$query = 'SELECT a.id, a.title, a.alias, a.value, a.alias_value, a.group_id'
				.' FROM #__phocacart_specifications AS a'
			    .' WHERE a.product_id = '.(int) $productId
				.' ORDER BY a.id';
		$db->setQuery($query);
		if ($returnArray) {
			$specifications = $db->loadAssocList();
		} else {
			$specifications = $db->loadObjectList();
		}
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
	
	
	public static function storeSpecificationsById($productId, $specsArray, $new = 0) {
	
		if ((int)$productId > 0) {
			$db =JFactory::getDBO();
			
		
			
			$notDeleteSpecs = array();
			
			if (!empty($specsArray)) {
				$values 	= array();
				foreach($specsArray as $k => $v) {
					
					// Don't store empty specification
					if ($v['title'] == '') {
						continue;
					}
					
					if(empty($v['alias'])) {
						$v['alias'] = $v['title'];
					}
					$v['alias'] = PhocacartUtils::getAliasName($v['alias']);
					
					if(empty($v['alias_value'])) {
						$v['alias_value'] = $v['value'];
					}
					
					// When no value, then no alias
					if ($v['alias_value'] != '') {
						$v['alias_value'] = PhocacartUtils::getAliasName($v['alias_value']);
					}
					
					if(empty($v['group_id'])) {
						$v['group_id'] = 0;
					}
					
					// correct simple xml
					if (empty($v['title'])) 		{$v['title'] 			= '';}
					if (empty($v['alias'])) 		{$v['alias'] 			= '';}
					if (empty($v['value'])) 		{$v['value'] 			= '';}
					if (empty($v['alias_value'])) 	{$v['alias_value'] 		= '';}
					if (empty($v['group_id'])) 		{$v['group_id'] 		= '';}
					
					$idExists = 0;
					
					if ($new == 0) {	
						if (isset($v['id']) && $v['id'] > 0) {
							
							// Does the row exist
							$query = ' SELECT id '
							.' FROM #__phocacart_specifications'
							. ' WHERE id = '. (int)$v['id']
							.' ORDER BY id';
							$db->setQuery($query);
							$idExists = $db->loadResult();
							
						}
					}
					
					if ((int)$idExists > 0) {
									
						$query = 'UPDATE #__phocacart_specifications SET'
						.' product_id = '.(int)$productId.','
						.' title = '.$db->quote($v['title']).','
						.' alias = '.$db->quote($v['alias']).','
						.' value = '.$db->quote($v['value']).','
						.' alias_value = '.$db->quote($v['alias_value']).','
						.' group_id = '.(int)$v['group_id']
						.' WHERE id = '.(int)$idExists;
						$db->setQuery($query);
						$db->execute();
						
						$newIdS 				= $idExists;
						
					} else {
						$values 	= '('.(int)$productId.', '.$db->quote($v['title']).', '.$db->quote($v['alias']).', '.$db->quote($v['value']).', '.$db->quote($v['alias_value']).', '.(int)$v['group_id'].')';
						
						$query = ' INSERT INTO #__phocacart_specifications (product_id, title, alias, value, alias_value, group_id)'
								.' VALUES '.$values;
						$db->setQuery($query);
						$db->execute();
						
						$newIdS = $db->insertid();
					}
					
					$notDeleteSpecs[]	= $newIdS;
				}
			}
			
			// Remove all specifications except the active
			if (!empty($notDeleteSpecs)) {
				$notDeleteSpecsString = implode($notDeleteSpecs, ',');
				$query = ' DELETE '
						.' FROM #__phocacart_specifications'
						.' WHERE product_id = '. (int)$productId
						.' AND id NOT IN ('.$notDeleteSpecsString.')';
				
			} else {
				$query = ' DELETE '
						.' FROM #__phocacart_specifications'
						.' WHERE product_id = '. (int)$productId;
			}
			$db->setQuery($query);
			$db->execute();

		}
	}
	
	
	/*
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
					
					// Don't store empty specification
					if ($v['title'] == '') {
						continue;
					}
					
					if(empty($v['alias'])) {
						$v['alias'] = $v['title'];
					}
					$v['alias'] = PhocacartUtils::getAliasName($v['alias']);
					
					if(empty($v['alias_value'])) {
						$v['alias_value'] = $v['value'];
					}
					
					// When no value, then no alias
					if ($v['alias_value'] != '') {
						$v['alias_value'] = PhocacartUtils::getAliasName($v['alias_value']);
					}
					
					if(empty($v['group_id'])) {
						$v['group_id'] = 0;
					}
					
					// correct simple xml
					if (empty($v['title'])) 		{$v['title'] 			= '';}
					if (empty($v['alias'])) 		{$v['alias'] 			= '';}
					if (empty($v['value'])) 		{$v['value'] 			= '';}
					if (empty($v['alias_value'])) 	{$v['alias_value'] 		= '';}
					if (empty($v['group_id'])) 		{$v['group_id'] 		= '';}
					
					$values[] 	= '('.(int)$productId.', '.$db->quote($v['title']).', '.$db->quote($v['alias']).', '.$db->quote($v['value']).', '.$db->quote($v['alias_value']).', '.(int)$v['group_id'].')';
				}
				
				if (!empty($values)) {
					$valuesString = implode($values, ',');
					$query = ' INSERT INTO #__phocacart_specifications (product_id, title, alias, value, alias_value, group_id)'
							.' VALUES '.(string)$valuesString;
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}
	*/
	
	public static function getSpecificationGroupsAndSpecifications($productId) {
		
		$db = JFactory::getDBO();
		
		$query = 'SELECT s.id, s.title, s.alias, s.value, s.alias_value, g.id as groupid, g.title as grouptitle'
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
	
	public static function getAllSpecificationsAndValues($ordering = 1) {
			
		$db 			= JFactory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 6);
		
		$query = 'SELECT s.id, s.title, s.alias, s.value, s.alias_value'
				.' FROM  #__phocacart_specifications AS s'
				.' GROUP BY s.alias, s.alias_value'
				.' ORDER BY '.$orderingText;
		$db->setQuery($query);
		$specifications = $db->loadObjectList();

		$a	= array();
		if (!empty($specifications)) {
			foreach($specifications as $k => $v) {
				if (isset($v->title) && $v->title != '' 
				&& isset($v->id) && $v->id != ''
				&& isset($v->alias) && $v->alias != '') {
					$a[$v->alias]['title']				= $v->title;
					$a[$v->alias]['id']					= $v->id;
					$a[$v->alias]['alias']				= $v->alias;
					if (isset($v->value) && $v->value != '' 
					&& isset($v->alias_value) && $v->alias_value != '') {	
						$a[$v->alias]['value'][$v->alias_value] = new stdClass();
						$a[$v->alias]['value'][$v->alias_value]->title	= $v->value;
						$a[$v->alias]['value'][$v->alias_value]->id		= $v->id;
						$a[$v->alias]['value'][$v->alias_value]->alias	= $v->alias_value;
					} else {
						$a[$v->alias]['value'] = array();
					}
				} 
			}
			
		}
		return $a;
		
	}
}
?>