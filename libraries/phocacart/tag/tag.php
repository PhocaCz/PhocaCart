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

class PhocacartTag
{
	/**
	 * Standard Tags - are displayed at the bottom
	 * @param int $itemId
	 * @param number $select
	 * @return mixed|void|mixed[]
	 */
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
			    .' WHERE a.type = 0'
				.' AND r.item_id = '.(int) $itemId;
		$db->setQuery($query);

		if ($select == 1) {
			$tags = $db->loadColumn();
		} else {
			$tags = $db->loadObjectList();
		}	
	
		return $tags;
	}
	
	/**
	 * Labels - are displayed at the top
	 * @param int $itemId
	 * @param number $select
	 * @return mixed|void|mixed[]
	 */
	public static function getTagLabels($itemId, $select = 0) {
	
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
				.' LEFT JOIN #__phocacart_taglabels_related AS r ON a.id = r.tag_id'
			    .' WHERE a.type = 1'
				.' AND r.item_id = '.(int) $itemId;
		$db->setQuery($query);

		if ($select == 1) {
			$tags = $db->loadColumn();
		} else {
			$tags = $db->loadObjectList();
		}	
	
		return $tags;
	}
	
	public static function getAllTags($ordering = 1, $onlyAvailableProducts = 0, $lang = '') {
	
	/*	$db 			= JFactory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 3);
		
		$query = 'SELECT t.id, t.title, t.alias FROM #__phocacart_tags AS t WHERE t.published = 1 ORDER BY '.$orderingText;
		$db->setQuery($query);
		$tags = $db->loadObjectList();	
	
		return $tags;*/
		
		
		$db 			= JFactory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 3);
		
		$wheres		= array();
		$lefts		= array();
		
		$columns		= 't.id, t.title, t.alias';
		/*$groupsFull		= $columns;
		$groupsFast		= 'm.id';
		$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;*/
		
		$wheres[]	= ' t.published = 1';
		
		
		
		if ($onlyAvailableProducts == 1) {
			
			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
			}
			
			$lefts[] = ' #__phocacart_tags_related AS tr ON tr.tag_id = t.id';
			$lefts[] = ' #__phocacart_products AS p ON tr.item_id = p.id';
			$rules = PhocacartProduct::getOnlyAvailableProductRules();
			$wheres = array_merge($wheres, $rules['wheres']);
			$lefts	= array_merge($lefts, $rules['lefts']);
			
		} else {
			
			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
				$lefts[] 	= ' #__phocacart_tags_related AS tr ON tr.tag_id = t.id';
				$lefts[] 	= ' #__phocacart_products AS p ON tr.item_id = p.id';
			}
		}
		
		$q = ' SELECT DISTINCT '.$columns
			.' FROM  #__phocacart_tags AS t'
			. (!empty($lefts) ? ' LEFT JOIN ' . implode( ' LEFT JOIN ', $lefts ) : '')
			. (!empty($wheres) ? ' WHERE ' . implode( ' AND ', $wheres ) : '')
			//.' GROUP BY '.$groups
			.' ORDER BY '.$orderingText;

		$db->setQuery($q);
		
		$items = $db->loadObjectList();	
	
		return $items;
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
	
	public static function storeTagLabels($tagsArray, $itemId) {
	
	
		if ((int)$itemId > 0) {
			$db = JFactory::getDBO();
			$query = ' DELETE '
					.' FROM #__phocacart_taglabels_related'
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
				
					$query = ' INSERT INTO #__phocacart_taglabels_related (item_id, tag_id)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	
	}
	
	public static function getAllTagsSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id', $type = 0 ) {
	
		$db = JFactory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_tags AS a'
				.' WHERE a.type ='.(int)$type
				.' ORDER BY '. $order;
		$db->setQuery($query);
		$tags = $db->loadObjectList();
		
		$tagsO = JHtml::_('select.genericlist', $tags, $name, 'class="inputbox" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);
		
		return $tagsO;
	}
	
	/**
	 * 
	 * @param int $itemId
	 * @param number $type 0 ... tag, 1 ... tag label
	 * @return string
	 */
	
	public static function getTagsRendered($itemId, $type = 0) {
		
		
		if ($type == 1) {
			$tags 	= self::getTagLabels($itemId);
		} else {
			$tags 	= self::getTags($itemId);
		}
		$db 	= JFactory::getDBO();
		$p 		= PhocacartUtils::getComponentParameters();
		$tl		= $p->get( 'tags_links', 0 );
		$o 	= '';
		
		if (!empty($tags)) {
			foreach($tags as $k => $v) {
				
				if ($type == 1) {
					$o .= '<div class="ph-corner-icon-wrapper"><div class="ph-corner-icon ph-corner-icon-'.htmlspecialchars(strip_tags($v->alias)).'">';
				} else {
					$o .= '<span class="label label-info">';
				}
				
				
				$dO = htmlspecialchars(strip_tags($v->title));
				
				if ($v->display_format == 2) {
					if ($v->icon_class != '') {
						$dO = '<span class="'.htmlspecialchars(strip_tags($v->icon_class)).'"></span>';
					} else {
						$dO = $v->title;
					}
				} else if ($v->display_format == 3) {
					if ($v->icon_class != '') {
						$dO = '<span class="'.htmlspecialchars(strip_tags($v->icon_class)).'"></span> ';
					}
					$dO .= $v->title;
				}
				
				if ($tl == 0) {
					$o .= $dO;
				} else if ($tl == 1) {
					if ($v->link_ext != '') {
						$o .= '<a href="'.$v->link_ext.'">'.$dO.'</a>';
					} else {
						$o .= $dO;
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
							$o .= '<a href="'.$link.'">'.$dO.'</a>';
						} else {
							$o .= $dO;
						}
					} else {
						$o .= $dO;
					}
				} else if ($tl == 3) {
					$link = PhocacartRoute::getItemsRoute();
					$link = $link . PhocacartRoute::getItemsRouteSuffix('tag', $v->id, $v->alias);
					$o .= '<a href="'.$link.'">'.$dO.'</a>';
				}
				
				if ($type == 1) {
					$o .= '</div></div>';
				} else {
					$o .= '</span>';
				}
			}
		}		
		return $o;
	}
	
	
	public static function getTagType($type = 0) {
		
		switch ($type) {
			
			case 1:
				return JText::_('COM_PHOCACART_TAG_LABEL');
			break;
			
			default:
				return JText::_('COM_PHOCACART_TAG');
			break;
			
		}
	}
}