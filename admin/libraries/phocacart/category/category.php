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
jimport('joomla.application.component.model');

final class PhocacartCategory
{

	private static $categoryA = array();
	private static $categoryF = array();
	private static $categoryP = array();
	private static $categoryI = array();

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

		$categories[] = JHtml::_('select.option', '0', '- '.JText::_('COM_PHOCACART_SELECT_CATEGORY').' -');
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

			$catDataTree	= PhocacartCategory::CategoryTreeOption($catDataAll, $tree, 0, $text, -1);

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

		$category = JHtml::_('select.genericlist',  $categories, 'catid', $onChO, 'value', 'text', $active);

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
		$tree = PhocacartCategory::CategoryTreeOption($items, $tree, 0, $text, $catId);

		return $tree;

	}

	public static function getCategoryById($id) {

		$id = (int)$id;
		if( empty(self::$categoryI[$id])) {

			$db = JFactory::getDBO();
			$query = 'SELECT a.title, a.alias, a.id, a.parent_id'
			. ' FROM #__phocacart_categories AS a'
			. ' WHERE a.id = '.(int)$id
			. ' ORDER BY a.ordering'
			. ' LIMIT 1';
			$db->setQuery( $query );
			self::$categoryI[$id] = $db->loadObject();
		}
		return self::$categoryI[$id];
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
		if (isset($m[$r])) {
			return $m[$r];
		}
		return 0;
	}

	public static function nestedToUl($data, $currentCatid = 0) {
		$result = array();

		if (!empty($data) && count($data) > 0) {
			$result[] = '<ul>';
			foreach ($data as $k => $v) {
				$link 		= JRoute::_(PhocacartRoute::getCategoryRoute($v['id'], $v['alias']));

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

	public static function nestedToCheckBox($data, $d, $currentCatid = 0, &$active, $forceCategoryId = 0) {
		$result = array();
		if (!empty($data) && count($data) > 0) {
			$result[] = '<ul class="ph-filter-module-category-tree">';
			foreach ($data as $k => $v) {

				$checked 	= '';
				$value		= htmlspecialchars($v['alias']);
				if (isset($d['nrinalias']) && $d['nrinalias'] == 1) {
					$value 		= (int)$v['id'] .'-'. htmlspecialchars($v['alias']);
				}

				if (in_array($value, $d['getparams'])) {
					$checked 	= 'checked';
					$active     = 1;
				}

				// This only can happen in category view (category filters are empty, id of category is larger then zero)
				// This is only marking the category as active in category list
				if (empty($d['getparams']) || (isset($d['getparams'][0]) && $d['getparams'][0] == '')) {
					// Empty parameters, so we can set category id by id of category view
					if ($forceCategoryId > 0 && (int)$forceCategoryId == (int)$v['id']) {
						$checked = 'checked';
						$active = 1;
					}
				}


				$result[] = '<li><div class="checkbox">';
				$result[] = '<label class="ph-checkbox-container"><input type="checkbox" name="tag" value="'.$value.'" '.$checked.' onchange="phChangeFilter(\''.$d['param'].'\', \''. $value.'\', this, \''.$d['formtype'].'\',\''.$d['uniquevalue'].'\');" />'.$v['title'].'<span class="ph-checkbox-checkmark"></span></label>';
				$result[] = '</div></li>';
				$result[] = self::nestedToCheckBox($v['children'], $d, $currentCatid, $active);
			}
			$result[] = '</ul>';
		}

		return implode($result);
	}

	public static function getCategoryTreeFormat($ordering = 1, $display = '', $hide = '', $type = array(0,1), $lang = '') {

		$cis = str_replace(',', '', 'o'.$ordering .'d'. $display .'h'. $hide. 'l'. $lang);
		if( empty(self::$categoryF[$cis])) {
			/* phocacart import('phocacart.ordering.ordering');*/
			$itemOrdering 	= PhocacartOrdering::getOrderingText($ordering,1);
			$db 			= JFactory::getDBO();
			$wheres			= array();
			$user 			= PhocacartUser::getUser();
			$userLevels		= implode (',', $user->getAuthorisedViewLevels());
			$userGroups 	= implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
			$wheres[] 		= " c.access IN (".$userLevels.")";
			$wheres[] 		= " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";
			$wheres[] 		= " c.published = 1";

			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
			}

			if (!empty($type) && is_array($type)) {
				$wheres[] = " c.type IN (".implode(',', $type).")";
			}

			if ($display != '') {
				$wheres[] = " c.id IN (".$display.")";
			}
			if ($hide != '') {
				$wheres[] = " c.id NOT IN (".$hide.")";
			}

			$columns		= 'c.id, c.title, c.alias, c.parent_id';
			$groupsFull		= $columns;
			$groupsFast		= 'c.id';
			$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

			$query = 'SELECT c.id, c.title, c.alias, c.parent_id'
			. ' FROM #__phocacart_categories AS c'
			. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' GROUP BY '.$groups
			. ' ORDER BY '.$itemOrdering;
			$db->setQuery( $query );

			$items 						= $db->loadAssocList();
			$tree 						= self::categoryTree($items);
			$currentCatid				= self::getActiveCategoryId();
			self::$categoryF[$cis] = self::nestedToUl($tree, $currentCatid);
		}

		return self::$categoryF[$cis];
	}

	public static function getCategoryTreeArray($ordering = 1, $display = '', $hide = '', $type = array(0,1), $lang = '') {

		$cis = str_replace(',', '', 'o'.$ordering .'d'. $display .'h'. $hide . 'l'. $lang);
		if( empty(self::$categoryA[$cis])) {
			/*phocacart import('phocacart.ordering.ordering');*/
			$itemOrdering 	= PhocacartOrdering::getOrderingText($ordering,1);
			$db 			= JFactory::getDBO();
			$wheres			= array();
			$user 			= PhocacartUser::getUser();
			$userLevels		= implode (',', $user->getAuthorisedViewLevels());
			$userGroups 	= implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
			$wheres[] 		= " c.access IN (".$userLevels.")";
			$wheres[] 		= " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";
			$wheres[] 		= " c.published = 1";

			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('c.language', $lang);
			}

			if (!empty($type) && is_array($type)) {
				$wheres[] = " c.type IN (".implode(',', $type).")";
			}

			if ($display != '') {
				$wheres[] = " c.id IN (".$display.")";
			}
			if ($hide != '') {
				$wheres[] = " c.id NOT IN (".$hide.")";
			}

			$query = 'SELECT c.id, c.title, c.alias, c.parent_id, c.image, c.description'
			. ' FROM #__phocacart_categories AS c'
			. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
			. ' WHERE ' . implode( ' AND ', $wheres )
			. ' ORDER BY '.$itemOrdering;
			$db->setQuery( $query );
			$items 						= $db->loadAssocList();
			self::$categoryA[$cis]	= self::categoryTree($items);
		}
		return self::$categoryA[$cis];
	}

	public static function getActiveCategoryId() {

		$app			= JFactory::getApplication();
		$option			= $app->input->get( 'option', '', 'string' );
		$view			= $app->input->get( 'view', '', 'string' );
		$catid			= $app->input->get( 'catid', '', 'int' ); // ID in items view is category id
		$id				= $app->input->get( 'id', '', 'int' );

		if ($option == 'com_phocacart' && ($view == 'items' || $view == 'category')) {

			if ((int)$id > 0) {
				return $id;
			}
		}
		if ($option == 'com_phocacart' && $view == 'item') {

			if ((int)$catid > 0) {
				return $catid;
			}
		}
		return 0;
	}


	public final function __clone() {
		throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
		return false;
	}
}
?>
