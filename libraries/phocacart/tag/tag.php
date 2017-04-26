<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

class PhocacartTag
{
	public static function getTags($itemId, $select = 0) {
	
		$db = JFactory::getDBO();
		
		if ($select == 1) {
			$query = 'SELECT r.tag_id';
		} else if ($select == 2){
			$query = 'SELECT a.id, a.alias ';
		} else {
			$query = 'SELECT a.*';
		}
		$query .= ' FROM #__phocacart_tags AS a'
				//.' LEFT JOIN #__phocacart AS f ON f.id = r.item_id'
				.' LEFT JOIN #__phocacart_tags_related AS r ON a.id = r.tag_id'
			    .' WHERE r.item_id = '.(int) $itemId;
		$db->setQuery($query);

		if ($select == 1) {
			$tags = $db->loadColumn();
		} else {
			$tags = $db->loadObjectList();
		}	
	
		return $tags;
	}
	
	public static function getAllTags($ordering = 1) {
	
		$db 			= JFactory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 3);
		
		$query = 'SELECT t.id, t.title, t.alias FROM #__phocacart_tags AS t WHERE t.published = 1 ORDER BY '.$orderingText;
		$db->setQuery($query);
		$tags = $db->loadObjectList();	
	
		return $tags;
	}
	
	public static function storeTags($tagsArray, $itemId) {
	
	
		if ((int)$itemId > 0) {
			$db = JFactory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_tags_related'
					. ' WHERE item_id = '. (int)$itemId;
			$db->setQuery($query);
			$db->execute();
			if (!empty($tagsArray)) {
				
				$values 		= array();
				$valuesString 	= '';
				
				foreach($tagsArray as $k => $v) {
					$values[] = ' ('.(int)$itemId.', '.(int)$v.')';
				}
				
				if (!empty($values)) {
					$valuesString = implode($values, ',');
				
					$query = ' INSERT INTO #__phocacart_tags_related (item_id, tag_id)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	
	}
	
	public static function getAllTagsSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id' ) {
	
		$db = JFactory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_tags AS a'
				. ' ORDER BY '. $order;
		$db->setQuery($query);
		$tags = $db->loadObjectList();
		
		$tagsO = JHTML::_('select.genericlist', $tags, $name, 'class="inputbox" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);
		
		return $tagsO;
	}
	
	public static function getTagsRendered($itemId) {
		
		$tags 	= self::getTags($itemId);
		$db 	= JFactory::getDBO();
		$p 		= JComponentHelper::getParams('com_phocacart') ;
		$tl		= $p->get( 'tags_links', 0 );
		$o 	= '';
		if (!empty($tags)) {
			foreach($tags as $k => $v) {
				
				$o .= '<span class="label label-info">';
				if ($tl == 0) {
					$o .= $v->title;
				} else if ($tl == 1) {
					if ($v->link_ext != '') {
						$o .= '<a href="'.$v->link_ext.'">'.$v->title.'</a>';
					} else {
						$o .= $v->title;
					}
				} else if ($tl == 2) {
					
					if ($v->link_cat != '') {
						$query = 'SELECT a.id, a.alias'
						.' FROM #__phocacart_categories AS a'
						.' WHERE a.id = '.(int)$v->link_cat;

						$db->setQuery($query, 0, 1);
						$category = $db->loadObject();
						
						if (isset($category->id) && isset($category->alias)) {
							$link = PhocacartRoute::getCategoryRoute($category->id, $category->alias);
							$o .= '<a href="'.$link.'">'.$v->title.'</a>';
						} else {
							$o .= $v->title;
						}
					} else {
						$o .= $v->title;
					}
				} else if ($tl == 3) {
					$link = PhocacartRoute::getItemsRoute();
					$link = $link . PhocacartRoute::getItemsRouteSuffix('tag', $v->id, $v->alias);
					$o .= '<a href="'.$link.'">'.$v->title.'</a>';
				}
				
				$o .= '</span> ';
			}
		}		
		return $o;
	}
}