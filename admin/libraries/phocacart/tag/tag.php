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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

class PhocacartTag
{
  /**
   * Standard Tags - are displayed at the bottom
   */
  public const TYPE_TAG = 0;
  /**
   * Labels - are displayed at the top
   */
  public const TYPE_LABEL = 1;

  private static function getRelatedTable($type)
  {
    switch ($type) {
      case self::TYPE_TAG:
      default:
        return '#__phocacart_tags_related';
      case self::TYPE_LABEL:
        return '#__phocacart_taglabels_related';
    }
  }

  private static function getProductTags($type, $itemId, $select = 0, $checkPublish = 0)
  {
    $db = Factory::getDBO();

    if ($select == 1) {
      $query = 'SELECT r.tag_id';
    } else if ($select == 2){
      $query = 'SELECT a.id, a.alias ';
    } else {
      $query = 'SELECT a.id, a.title, a.alias, a.type, a.display_format, a.link_ext, a.link_cat, a.icon_class';
    }

    $query .= ' FROM #__phocacart_tags AS a'
      .' LEFT JOIN ' . self::getRelatedTable($type) . ' AS r ON a.id = r.tag_id'
      .' WHERE a.type = ' . (int)$type
      .' AND r.item_id = '.(int) $itemId;
    if ($checkPublish == 1) {
        $query .= ' AND a.published = 1';
    }

    $query .= ' ORDER BY a.id';
    $db->setQuery($query);

    if ($select == 1) {
      $tags = $db->loadColumn();
    } else {
      $tags = $db->loadObjectList();
    }

    return $tags;
  }


  private static function getSubmittedProductTags($type, $itemId)
  {
    $db = Factory::getDBO();
    $query = 'SELECT a.items_item';
    $query .= ' FROM #__phocacart_submit_items AS a'
      .' WHERE a.id = '.(int) $itemId;
    $db->setQuery($query);
    $items = $db->loadResult();

    if (!empty($items)) {
      $itemsA = json_decode($items, true);
      switch ($type) {
        case self::TYPE_TAG:
        default:
          $fieldName = 'tags';
          break;
        case self::TYPE_LABEL:
          $fieldName = 'taglabels';
          break;
      }

      if (isset($itemsA[$fieldName])){
        return $itemsA[$fieldName];
      }
    }

    return [];
  }

  private static function getProductsTags($type, $cids)
  {
    $db = Factory::getDBO();
    if ($cids) {//cids is string separated by comma
      $query = 'SELECT r.tag_id FROM #__phocacart_tags AS a'
        . ' LEFT JOIN ' . self::getRelatedTable($type) . ' AS r ON a.id = r.tag_id'
        . ' WHERE a.type = ' . (int)$type
        . ' AND r.item_id IN (' . $cids . ')'
        . ' ORDER BY a.id';

      $db->setQuery($query);
      $tags = $db->loadColumn();
      $tags = array_unique($tags);

      return $tags;
    }

    return [];
  }

	/**
	 * Standard Tags - are displayed at the bottom
	 * @param int $itemId
	 * @param number $select
	 * @return mixed|void|mixed[]
	 */
	public static function getTags($itemId, $select = 0, $checkPublish = 0)
  {
    return self::getProductTags(self::TYPE_TAG, $itemId, $select, $checkPublish);
	}

	/*
	 * TAGS (stored in submitted items) Field JFormFieldPhocaTagsSubmitItems
	 */
	public static function getTagsSubmitItems($itemId)
  {
    return self::getSubmittedProductTags(self::TYPE_TAG, $itemId);
	}

	public static function getTagsByIds($cids)
  {
    return self::getProductsTags(self::TYPE_TAG, $cids);
	}

	/**
	 * Labels - are displayed at the top
	 * @param int $itemId
	 * @param number $select
	 * @return mixed|void|mixed[]
	 */
	public static function getTagLabels($itemId, $select = 0, $checkPublish = 0)
  {
    return self::getProductTags(self::TYPE_LABEL, $itemId, $select, $checkPublish);
	}

	/*
	 * TAGLABELS (stored in submitted items) Field JFormFieldPhocaTaglabelsSubmitItems
	 */
	public static function getTagLabelsSubmitItems($itemId)
  {
    return self::getSubmittedProductTags(self::TYPE_LABEL, $itemId);
	}

	public static function getTagsLabelsByIds($cids) {
    return self::getProductsTags(self::TYPE_LABEL, $cids);
	}

    /**
     * @param int $ordering
     * @param int $onlyAvailableProducts
     * @param int $type 0 ... tag, 1 ... label
     * @param string $lang
     * @return mixed
     */
	public static function getAllTags($ordering = 1, $onlyAvailableProducts = 0, $type = self::TYPE_TAG, $lang = '', $filterProducts = array(), $limitCount = -1)
  {
    $wheres		= array();
    $lefts		= array();

    $wheres[]	= ' t.type = ' . (int)$type;

		$orderingText 	= PhocacartOrdering::getOrderingText($ordering, 3);

		$columns		= 't.id, t.title, t.alias, t.type, t.count_products';
		$wheres[]	= ' t.published = 1';

    $productTableAdded = 0;

		if ($onlyAvailableProducts == 1) {
			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
			}

			$lefts[] = ' ' . self::getRelatedTable($type) . ' AS tr ON tr.tag_id = t.id';
			$lefts[] = ' #__phocacart_products AS p ON tr.item_id = p.id';
            $productTableAdded = 1;
			$rules = PhocacartProduct::getOnlyAvailableProductRules();
			$wheres = array_merge($wheres, $rules['wheres']);
			$lefts	= array_merge($lefts, $rules['lefts']);

		} else {
			if ($lang != '' && $lang != '*') {
				$wheres[] 	= PhocacartUtilsSettings::getLangQuery('p.language', $lang);
				$lefts[] 	= ' ' . self::getRelatedTable($type) . ' AS tr ON tr.tag_id = t.id';
				$lefts[] 	= ' #__phocacart_products AS p ON tr.item_id = p.id';
                $productTableAdded = 1;
			}
		}

    if (!empty($filterProducts)) {
      $productIds = implode (',', $filterProducts);
      $wheres[]	= 'p.id IN (' . $productIds . ')';
      if ($productTableAdded == 0) {
        $lefts[] 	= ' ' . self::getRelatedTable($type) . ' AS tr ON tr.tag_id = t.id';
        $lefts[] 	= ' #__phocacart_products AS p ON tr.item_id = p.id';
      }
    }

		if ((int)$limitCount > -1) {
		    $wheres[] = " t.count_products > ".(int)$limitCount;
		}


		$q = ' SELECT DISTINCT '.$columns
			.' FROM  #__phocacart_tags AS t'
			. (!empty($lefts) ? ' LEFT JOIN ' . implode( ' LEFT JOIN ', $lefts ) : '')
			. (!empty($wheres) ? ' WHERE ' . implode( ' AND ', $wheres ) : '')
			.' ORDER BY '.$orderingText;

    $db = Factory::getDBO();
    $db->setQuery($q);

    return $db->loadObjectList();
  }

  private static function storeProductTags($type, $tagsArray, $itemId)
  {
    if ((int)$itemId <= 0) {
      return;
    }

    $db = Factory::getDBO();
    $query = ' DELETE '
      .' FROM ' . self::getRelatedTable($type)
      . ' WHERE item_id = '. (int)$itemId;
    $db->setQuery($query);
    $db->execute();

    if ($tagsArray) {
      $values = [];
      foreach ($tagsArray as $v) {
        $values[] = ' (' . (int)$itemId . ', ' . (int)$v . ')';
      }

      $query = ' INSERT INTO ' . self::getRelatedTable($type) . ' (item_id, tag_id)'
        . ' VALUES ' . implode(',', $values);

      $db->setQuery($query);
      $db->execute();
    }
  }

	public static function storeTags($tagsArray, $itemId)
  {
    self::storeProductTags(self::TYPE_TAG, $tagsArray, $itemId);
	}

  public static function storeTagLabels($tagsArray, $itemId)
  {
    self::storeProductTags(self::TYPE_LABEL, $tagsArray, $itemId);
  }

  public static function getAllTagsList($order = 'id', $type = self::TYPE_TAG)
  {
    $db = Factory::getDBO();
    $query = 'SELECT a.id AS value, a.title AS text'
      .' FROM #__phocacart_tags AS a'
      .' WHERE a.type ='.(int)$type
      .' ORDER BY '. $order;
    $db->setQuery($query);
    return $db->loadObjectList();
  }

	public static function getAllTagsSelectBox($name, $id, $activeArray, $javascript = NULL, $order = 'id', $type = self::TYPE_TAG, $attributes = '')
  {
		return HTMLHelper::_('select.genericlist', self::getAllTagsList($order, $type), $name, $attributes, 'value', 'text', $activeArray, $id);
	}

	/**
	 *
	 * @param int $itemId
	 * @param number $types 0 ... nothing, 1 ... tags only, 2 ... labels only, 3 ... tags and labels
	 * @return string
	 */
	public static function getTagsRendered($itemId, $types = 0, $separator = '') {

	    if ($types == 1) {
	        // Only tags
			$tags 	= self::getTags($itemId, 0, 1);
		} else if ($types == 2) {
		    // Only labels
			$tags 	= self::getTagLabels($itemId, 0, 1);
		} else if ($types == 3) {
		    // Tags and Labels together (they can be displayed as labels in category/items view)
		    $t 	= self::getTags($itemId, 0, 1);
		    $l 	= self::getTagLabels($itemId, 0, 1);
		    $tags = array_merge($t, $l);
        } else {
	        return '';
        }
		$db 	= Factory::getDBO();
		$p 		= PhocacartUtils::getComponentParameters();
		$s      = PhocacartRenderStyle::getStyles();
		$tl		= $p->get( 'tags_links', 0 );

		$o 	= array();
		$i  = 0;

		if (!empty($tags)) {
			foreach($tags as $k => $v) {

				if ($types == 2 || $types == 3) {
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
                    if ($types == 2 || $types == 3) {
                        $link = $link . PhocacartRoute::getItemsRouteSuffix('label', $v->id, $v->alias);
                    } else {
                        $link = $link . PhocacartRoute::getItemsRouteSuffix('tag', $v->id, $v->alias);
                    }

					$o[$i] .= '<a href="'.Route::_($link).'">'.$dO.'</a>';
				}

				if ($types == 2 || $types == 3) {
					$o[$i] .= '</div></div>';
				} else {
					$o[$i] .= '</span>';
				}

				$i++;
			}

		}

		return implode($separator, $o);
	}


  public static function getTagType($type = self::TYPE_TAG) {
    switch ($type) {
      case self::TYPE_LABEL:
        return Text::_('COM_PHOCACART_LABEL');
      default:
        return Text::_('COM_PHOCACART_TAG');
    }
  }

  private static function getActiveItems($type, $ids, $ordering)
  {
    $db     = Factory::getDbo();
    $o      = array();
    $wheres = array();
    $ordering = PhocacartOrdering::getOrderingText($ordering, 3);//t
    if ($ids) {
      $wheres[] = 't.id IN (' . $ids . ')';
      $wheres[] = 't.type = ' . (int)$type;
      $q = 'SELECT DISTINCT t.title, CONCAT(t.id, \'-\', t.alias) AS alias, \'tag\' AS parameteralias, \'tag\' AS parametertitle FROM #__phocacart_tags AS t'
        . (!empty($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '')
        . ' GROUP BY t.alias, t.title'
        . ' ORDER BY ' . $ordering;

      $db->setQuery($q);
      $o = $db->loadAssocList();
    }
    return $o;
  }

  public static function getActiveTags($items, $ordering) {
    return self::getActiveItems(self::TYPE_TAG, $items, $ordering);
  }

  public static function getActiveLabels($items, $ordering) {
    return self::getActiveItems(self::TYPE_LABEL, $items, $ordering);
  }
}
