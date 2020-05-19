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
			$query = 'SELECT a.id, a.title, a.alias, a.type, a.display_format, a.link_ext, a.link_cat';
		}
		$query .= ' FROM #__phocacart_tags AS a'
				//.' LEFT JOIN #__phocacart AS f ON f.id = r.item_id'
				.' LEFT JOIN #__phocacart_tags_related AS r ON a.id = r.tag_id'
			    .' WHERE a.type = 0'
				.' AND r.item_id = '.(int) $itemId
                .' ORDER BY a.id';
		$db->setQuery($query);

		if ($select == 1) {
			$tags = $db->loadColumn();
		} else {
			$tags = $db->loadObjectList();
		}

		return $tags;
	}

	/*
	 * TAGS (stored in submitted items) Field JFormFieldPhocaTagsSubmitItems
	 */
	public static function getTagsSubmitItems($itemId) {

		$db = JFactory::getDBO();
		$query = 'SELECT a.items_item';
		$query .= ' FROM #__phocacart_submit_items AS a'
				.' WHERE a.id = '.(int) $itemId
                .' ORDER BY a.id';
		$db->setQuery($query);
		$items = $db->loadResult();

		if (!empty($items)) {
			$itemsA = json_decode($items, true);
			if (isset($itemsA['tags'])){
				return $itemsA['tags'];
			}
		}

		return array();
	}

	public static function getTagsByIds($cids) {

		$db = JFactory::getDBO();
        if ($cids != '') {//cids is string separated by comma

            $query = 'SELECT r.tag_id FROM #__phocacart_tags AS a'
                //.' LEFT JOIN #__phocacart AS f ON f.id = r.item_id'
                . ' LEFT JOIN #__phocacart_tags_related AS r ON a.id = r.tag_id'
                . ' WHERE a.type = 0'
                . ' AND r.item_id IN (' . $cids . ')'
                . ' ORDER BY a.id';

            $db->setQuery($query);
            $tags = $db->loadColumn();
            $tags = array_unique($tags);

            return $tags;
        }
        return array();
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
			$query = 'SELECT a.id, a.title, a.alias, a.type, a.display_format, a.link_ext, a.link_cat';
		}
		$query .= ' FROM #__phocacart_tags AS a'
				//.' LEFT JOIN #__phocacart AS f ON f.id = r.item_id'
				.' LEFT JOIN #__phocacart_taglabels_related AS r ON a.id = r.tag_id'
			    .' WHERE a.type = 1'
				.' AND r.item_id = '.(int) $itemId
                .' ORDER BY a.id';
		$db->setQuery($query);

		if ($select == 1) {
			$tags = $db->loadColumn();
		} else {
			$tags = $db->loadObjectList();
		}

		return $tags;
	}

	/*
	 * TAGLABELS (stored in submitted items) Field JFormFieldPhocaTaglabelsSubmitItems
	 */
	public static function getTagLabelsSubmitItems($itemId) {

		$db = JFactory::getDBO();
		$query = 'SELECT a.items_item';
		$query .= ' FROM #__phocacart_submit_items AS a'
				.' WHERE a.id = '.(int) $itemId
                .' ORDER BY a.id';
		$db->setQuery($query);
		$items = $db->loadResult();

		if (!empty($items)) {
			$itemsA = json_decode($items, true);
			if (isset($itemsA['taglabels'])){
				return $itemsA['taglabels'];
			}
		}

		return array();
	}

	public static function getTagsLabelsByIds($cids) {

		$db = JFactory::getDBO();
        if ($cids != '') {//cids is string separated by comma

            $query = 'SELECT r.tag_id FROM #__phocacart_tags AS a'
                //.' LEFT JOIN #__phocacart AS f ON f.id = r.item_id'
                . ' LEFT JOIN #__phocacart_taglabels_related AS r ON a.id = r.tag_id'
                . ' WHERE a.type = 1'
                . ' AND r.item_id IN (' . $cids . ')'
                . ' ORDER BY a.id';

            $db->setQuery($query);
            $tags = $db->loadColumn();
            $tags = array_unique($tags);

            return $tags;
        }
        return array();
	}

    /**
     * @param int $ordering
     * @param int $onlyAvailableProducts
     * @param int $type 0 ... tag, 1 ... label
     * @param string $lang
     * @return mixed
     */
	public static function getAllTags($ordering = 1, $onlyAvailableProducts = 0, $type = 0, $lang = '', $filterProducts = array(), $limitCount = -1) {

	/*	$db 			= JFactory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 3);

		$query = 'SELECT t.id, t.title, t.alias FROM #__phocacart_tags AS t WHERE t.published = 1 ORDER BY '.$orderingText;
		$db->setQuery($query);
		$tags = $db->loadObjectList();

		return $tags;*/

        $wheres		= array();
        $lefts		= array();

	    switch($type) {
            case 1:
                $wheres[]	= ' t.type = 1';
                $related    = '#__phocacart_taglabels_related';
            break;

            case 0:
            default:
                $wheres[]	= ' t.type = 0';
                $related    = '#__phocacart_tags_related';
            break;

        }


		$db 			= JFactory::getDBO();
		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 3);



		$columns		= 't.id, t.title, t.alias, t.type, t.count_products';
		/*$groupsFull		= $columns;
		$groupsFast		= 'm.id';
		$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;*/

		$wheres[]	= ' t.published = 1';



		if ($onlyAvailableProducts == 1) {

			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
			}

			$lefts[] = ' '.$related.' AS tr ON tr.tag_id = t.id';
			$lefts[] = ' #__phocacart_products AS p ON tr.item_id = p.id';
			$rules = PhocacartProduct::getOnlyAvailableProductRules();
			$wheres = array_merge($wheres, $rules['wheres']);
			$lefts	= array_merge($lefts, $rules['lefts']);

		} else {

			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
				$lefts[] 	= ' '.$related.' AS tr ON tr.tag_id = t.id';
				$lefts[] 	= ' #__phocacart_products AS p ON tr.item_id = p.id';
			}
		}

		if (!empty($filterProducts)) {
			$productIds = implode (',', $filterProducts);
			$wheres[]	= 'p.id IN ('.$productIds.')';
		}

		if ((int)$limitCount > -1) {
		    $wheres[] = " t.count_products > ".(int)$limitCount;
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
					$valuesString = implode(',', $values);

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
					$valuesString = implode(',', $values);

					$query = ' INSERT INTO #__phocacart_taglabels_related (item_id, tag_id)'
								.' VALUES '.(string)$valuesString;

					$db->setQuery($query);
					$db->execute();
				}
			}
		}

	}

	public static function getAllTagsSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id', $type = 0, $class = 'inputbox') {

		$db = JFactory::getDBO();
		$query = 'SELECT a.id AS value, a.title AS text'
				.' FROM #__phocacart_tags AS a'
				.' WHERE a.type ='.(int)$type
				.' ORDER BY '. $order;
		$db->setQuery($query);
		$tags = $db->loadObjectList();

		$tagsO = JHtml::_('select.genericlist', $tags, $name, 'class="'.$class.'" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray, $id);

		return $tagsO;
	}

	/**
	 *
	 * @param int $itemId
	 * @param number $type 0 ... nothing, 1 ... tags only, 2 ... labels only, 3 ... tags and labels
	 * @return string
	 */

	public static function getTagsRendered($itemId, $type = 0, $separator = '') {

	    if ($type == 1) {
	        // Only tags
			$tags 	= self::getTags($itemId);
		} else if ($type == 2) {
		    // Only labels
			$tags 	= self::getTagLabels($itemId);
		} else if ($type == 3) {
		    // Tags and Labels together (they can be displayed as labels in category/items view)
		    $t 	= self::getTags($itemId);
		    $l 	= self::getTagLabels($itemId);
		    $tags = array_merge($t, $l);
        } else {
	        return '';
        }
		$db 	= JFactory::getDBO();
		$p 		= PhocacartUtils::getComponentParameters();
		$s      = PhocacartRenderStyle::getStyles();
		$tl		= $p->get( 'tags_links', 0 );

		$o 	= array();
		$i  = 0;
		if (!empty($tags)) {
			foreach($tags as $k => $v) {

				if ($type == 2 || $type == 3) {
					$o[$i] = '<div class="ph-corner-icon-wrapper"><div class="ph-corner-icon ph-corner-icon-'.htmlspecialchars(strip_tags($v->alias)).'">';
				} else {
					$o[$i] = '<span class="'.$s['c']['label.label-info'] .'">';
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
						$dO = '<span class="'.htmlspecialchars(strip_tags($v->icon_class)).'"></span>';
					}
					$dO .= $v->title;
				}

				if ($tl == 0) {
					$o[$i] .= $dO;
				} else if ($tl == 1) {
					if ($v->link_ext != '') {
						$o[$i] .= '<a href="'.$v->link_ext.'">'.$dO.'</a>';
					} else {
						$o[$i] .= $dO;
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
							$o[$i] .= '<a href="'.$link.'">'.$dO.'</a>';
						} else {
							$o[$i] .= $dO;
						}
					} else {
						$o[$i] .= $dO;
					}
				} else if ($tl == 3) {
					$link = PhocacartRoute::getItemsRoute();
                    if ($type == 2 || $type == 3) {
                        $link = $link . PhocacartRoute::getItemsRouteSuffix('label', $v->id, $v->alias);
                    } else {
                        $link = $link . PhocacartRoute::getItemsRouteSuffix('tag', $v->id, $v->alias);
                    }

					$o[$i] .= '<a href="'.JRoute::_($link).'">'.$dO.'</a>';
				}

				if ($type == 2 || $type == 3) {
					$o[$i] .= '</div></div>';
				} else {
					$o[$i] .= '</span>';
				}

				$i++;
			}

		}

		return implode($separator, $o);
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


	public static function getActiveTags($items, $ordering) {

	    $db     = JFactory::getDbo();
	    $o      = array();
        $wheres = array();
        $ordering = PhocacartOrdering::getOrderingText($ordering, 3);//t
        if ($items != '') {
            $wheres[] = 't.id IN (' . $items . ')';
            $wheres[] = 't.type = 0';
            $q = 'SELECT DISTINCT t.title, CONCAT(t.id, \'-\', t.alias) AS alias, \'tag\' AS parameteralias, \'tag\' AS parametertitle FROM #__phocacart_tags AS t'
                . (!empty($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '')
                . ' GROUP BY t.alias, t.title'
                . ' ORDER BY ' . $ordering;

            $db->setQuery($q);
            $o = $db->loadAssocList();
        }
        return $o;
    }

    public static function getActiveLabels($items, $ordering) {

	    $db     = JFactory::getDbo();
	    $o      = array();
        $wheres = array();
        $ordering = PhocacartOrdering::getOrderingText($ordering, 3);//t
        if ($items != '') {
            $wheres[] = 't.id IN (' . $items . ')';
            $wheres[] = 't.type = 1';
            $q = 'SELECT DISTINCT t.title, CONCAT(t.id, \'-\', t.alias) AS alias, \'tag\' AS parameteralias, \'tag\' AS parametertitle FROM #__phocacart_tags AS t'
                . (!empty($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '')
                . ' GROUP BY t.alias, t.title'
                . ' ORDER BY ' . $ordering;

            $db->setQuery($q);
            $o = $db->loadAssocList();
        }
        return $o;
    }
}
