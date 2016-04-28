<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
jimport('joomla.application.component.model');

final class PhocaCartCategory
{
	private static $categoryA = array();
	private static $categoryF = array();
	private static $categoryP = array();
	
	public function __construct() {}
	
	
	public static function CategoryTreeOption($data, $tree, $id=0, $text='', $currentId) {		

		foreach ($data as $key) {	
			$show_text =  $text . $key->text;
			
			if ($key->parentid == $id && $currentId != $id && $currentId != $key->value) {
				$tree[$key->value] 			= new JObject();
				$tree[$key->value]->text 	= $show_text;
				$tree[$key->value]->value 	= $key->value;
				$tree = self::CategoryTreeOption($data, $tree, $key->value, $show_text . " - ", $currentId );	
			}	
		}
		return($tree);
	}

	public static function filterCategory($query, $active = NULL, $frontend = NULL, $onChange = TRUE, $fullTree = NULL ) {
		
		$db	= JFactory::getDBO();

		$form = 'adminForm';
		if ($frontend == 1) {
			$form = 'phocacartproductsform';
		}
		
		if ($onChange) {
			$onChO = 'class="inputbox" size="1" onchange="document.'.$form.'.submit( );"';
		} else {
			$onChO = 'class="inputbox" size="1"';
		}
		
		$categories[] = JHTML::_('select.option', '0', '- '.JText::_('COM_PHOCACART_SELECT_CATEGORY').' -');
		$db->setQuery($query);
		$catData = $db->loadObjectList();
		
		
		
		if ($fullTree) {
			
			// Start - remove in case there is a memory problem
			$tree = array();
			$text = '';
			
			$queryAll = ' SELECT cc.id AS value, cc.title AS text, cc.parent_id as parentid'
					.' FROM #__phocacart_categories AS cc'
					.' ORDER BY cc.ordering';
			$db->setQuery($queryAll);
			$catDataAll 		= $db->loadObjectList();

			$catDataTree	= PhocaCartCategory::CategoryTreeOption($catDataAll, $tree, 0, $text, -1);
			
			$catDataTreeRights = array();
			/*foreach ($catData as $k => $v) {
				foreach ($catDataTree as $k2 => $v2) {
					if ($v->value == $v2->value) {
						$catDataTreeRights[$k]->text 	= $v2->text;
						$catDataTreeRights[$k]->value = $v2->value;
					}
				}
			}*/
			
			foreach ($catDataTree as $k => $v) {
                foreach ($catData as $k2 => $v2) {
                   if ($v->value == $v2->value) {
						$catDataTreeRights[$k] = new StdClass();
						$catDataTreeRights[$k]->text  = $v->text;
						$catDataTreeRights[$k]->value = $v->value;
                   }
                }
             }

			
			
			$catDataTree = array();
			$catDataTree = $catDataTreeRights;
			// End - remove in case there is a memory problem
			
			// Uncomment in case there is a memory problem
			//$catDataTree	= $catData;
		} else {
			$catDataTree	= $catData;
		}	
	
		$categories = array_merge($categories, $catDataTree );

		$category = JHTML::_('select.genericlist',  $categories, 'catid', $onChO, 'value', 'text', $active);

		return $category;
	}
	
	public static function options($type = 0)
	{

		
		$db = JFactory::getDBO();

       //build the list of categories
		$query = 'SELECT a.title AS text, a.id AS value, a.parent_id as parentid'
		. ' FROM #__phocacart_categories AS a'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$items = $db->loadObjectList();
	
		$catId	= -1;
		
		$javascript 	= 'class="inputbox" size="1" onchange="submitform( );"';
		
		$tree = array();
		$text = '';
		$tree = PhocaCartCategory::CategoryTreeOption($items, $tree, 0, $text, $catId);
		
		return $tree;

	}
	
	public static function getCategoryById($id) {
		$db = JFactory::getDBO();
		$query = 'SELECT a.title, a.alias, a.id, a.parent_id'
		. ' FROM #__phocacart_categories AS a'
		. ' WHERE a.id = '.(int)$id
		. ' ORDER BY a.ordering'
		. ' LIMIT 1';
		$db->setQuery( $query );
		$category = $db->loadObject();
		return $category;
	}
	
	public static function getChildren($id) {
		$db = JFactory::getDBO();
		$query = 'SELECT a.title, a.alias, a.id'
		. ' FROM #__phocacart_categories AS a'
		. ' WHERE a.parent_id = '.(int)$id
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );
		$categories = $db->loadObjectList();
		return $categories;
	}
	
	public static function getPath($path = array(), $id = 0, $parent_id = 0, $title = '', $alias = '') {
		
		if( empty(self::$categoryA[$id])) { 
			self::$categoryP[$id]	= self::getPathTree($path, $id, $parent_id, $title, $alias);	
		}		
		return self::$categoryP[$id];
	}
	
	public static function getPathTree($path = array(), $id = 0, $parent_id = 0, $title, $alias = '') {
		
		static $iCT = 0;
		
		if ((int)$id > 0) {
			$path[$iCT]['id'] = (int)$id;
			$path[$iCT]['catid'] = (int)$parent_id;
			$path[$iCT]['title'] = $title;
			$path[$iCT]['alias'] = $alias;
		}
		
		if ((int)$parent_id > 0) {
			$db = JFactory::getDBO();
			$query = 'SELECT a.title, a.alias, a.id, a.parent_id'
			. ' FROM #__phocacart_categories AS a'
			. ' WHERE a.id = '.(int)$parent_id
			. ' ORDER BY a.ordering';
			$db->setQuery( $query );
			$category = $db->loadObject();
			
			if (isset($category->id)) {
				$id 	= (int)$category->id;
				$title 	= '';
				if (isset($category->title)) {
					$title = $category->title;
				}
				
				$alias 	= '';
				if (isset($category->alias)) {
					$alias = $category->alias;
				}
				
				$parent_id = 0;
				if (isset($category->parent_id)) {
					$parent_id = (int)$category->parent_id;
				}
				$iCT++;
				
				$path = self::getPathTree($path, (int)$id, (int)$parent_id, $title, $alias);
			}
		}
		return $path;
	}
	
	public static function categoryTree($d, $r = 0, $pk = 'parent_id', $k = 'id', $c = 'children') {
		$m = array();
		foreach ($d as $e) {
			isset($m[$e[$pk]]) ?: $m[$e[$pk]] = array();
			isset($m[$e[$k]]) ?: $m[$e[$k]] = array();
			$m[$e[$pk]][] = array_merge($e, array($c => &$m[$e[$k]]));
		}
		//return $m[$r][0]; // remove [0] if there could be more than one root nodes
		return $m[$r];
	}

	public static function nestedToUl($data, $currentCatid = 0) {
		$result = array();

		if (sizeof($data) > 0) {
			$result[] = '<ul>';
			foreach ($data as $k => $v) {
				$link 		= JRoute::_(PhocaCartRoute::getCategoryRoute($v['id'], $v['alias']));
				
				// Current Category is selected
				if ($currentCatid == $v['id']) {
					$result[] = sprintf(
						'<li data-jstree=\'{"opened":true,"selected":true}\' >%s%s</li>',
						'<a href="'.$link.'">' . $v['title']. '</a>',
						self::nestedToUl($v['children'], $currentCatid)
					);
				} else {
					$result[] = sprintf(
						'<li>%s%s</li>',
						'<a href="'.$link.'">' . $v['title']. '</a>',
						self::nestedToUl($v['children'], $currentCatid)
					);	
				}
			}
			$result[] = '</ul>';
		}

		return implode($result);
	}
	
	public static function nestedToCheckBox($data, $d, $currentCatid = 0) {
		$result = array();

		if (sizeof($data) > 0) {
			$result[] = '<ul class="ph-filter-module-category-tree">';
			foreach ($data as $k => $v) {
				
				$checked 	= '';
				$value		= htmlspecialchars($v['alias']);
				if (isset($d['nrinalias']) && $d['nrinalias'] == 1) {
					$value 		= (int)$v['id'] .'-'. htmlspecialchars($v['alias']);
				} 
				
				if (in_array($value, $d['getparams'])) {
					$checked 	= 'checked';
				}
				
				$result[] = '<li><div class="checkbox">';
				$result[] = '<label><input type="checkbox" name="tag" value="'.$value.'" '.$checked.' onchange="phChangeFilter(\''.$d['param'].'\', \''. $value.'\', this, \''.$d['formtype'].'\',\''.$d['uniquevalue'].'\');" />'.$v['title'].'</label>';
				$result[] = '</div></li>';
				$result[] = self::nestedToCheckBox($v['children'], $d, $currentCatid);
			}
			$result[] = '</ul>';
		}

		return implode($result);
	}
	
	public static function getCategoryTreeFormat($ordering = 1) {
		
		if( empty(self::$categoryF[$ordering])) {
			phocacartimport('phocacart.ordering.ordering');
			$itemOrdering 	= PhocaCartOrdering::getOrderingText($ordering,1);
			$db 			= JFactory::getDBO();
			$wheres			= array();
			$user 			= JFactory::getUser();
			$userLevels		= implode (',', $user->getAuthorisedViewLevels());
			$wheres[] 		= " c.access IN (".$userLevels.")";
			$wheres[] 		= " c.published = 1";
			
			$query = 'SELECT c.id, c.title, c.alias, c.parent_id'
			. ' FROM #__phocacart_categories AS c'
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' ORDER BY '.$itemOrdering;
			$db->setQuery( $query );
			$items 						= $db->loadAssocList();
			$tree 						= self::categoryTree($items);
			$currentCatid				= self::getActiveCategoryId();
			self::$categoryF[$ordering] = self::nestedToUl($tree, $currentCatid);	
		}		
		return self::$categoryF[$ordering];
	}
	
	public static function getCategoryTreeArray($ordering = 1) {
		
		if( empty(self::$categoryA[$ordering])) {
			phocacartimport('phocacart.ordering.ordering');
			$itemOrdering 	= PhocaCartOrdering::getOrderingText($ordering,1);
			$db 			= JFactory::getDBO();
			$wheres			= array();
			$user 			= JFactory::getUser();
			$userLevels		= implode (',', $user->getAuthorisedViewLevels());
			$wheres[] 		= " c.access IN (".$userLevels.")";
			$wheres[] 		= " c.published = 1";
			
			$query = 'SELECT c.id, c.title, c.alias, c.parent_id'
			. ' FROM #__phocacart_categories AS c'
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' ORDER BY '.$itemOrdering;
			$db->setQuery( $query );
			$items 						= $db->loadAssocList();
			self::$categoryA[$ordering]	= self::categoryTree($items);	
		}		
		return self::$categoryA[$ordering];
	}
	
	public static function getActiveCategoryId() {
		
		$app			= JFactory::getApplication();
		$option			= $app->input->get( 'option', '', 'string' );
		$view			= $app->input->get( 'view', '', 'string' );
		
		if ($option == 'com_phocacart' && ($view == 'items' || $view = 'category')) {
			$id		= $app->input->get( 'id', '', 'int' ); // ID in items view is category id
			if ((int)$id > 0) {
				return $id;
			}
		}
		return 0;
	}
	
	
	public final function __clone() {
		JError::raiseWarning(500, 'Function Error: Cannot clone instance of Singleton pattern');// No JText - for developers only
		return false;
	}
}
?>