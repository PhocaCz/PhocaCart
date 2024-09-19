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
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Phoca\PhocaCart\Constants\TagType;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocacartTag
{
    private static function getRelatedTable($type) {
        switch ($type) {
            case TagType::Tag:
            default:
                return '#__phocacart_tags_related';
            case TagType::Label:
                return '#__phocacart_taglabels_related';
        }
    }

    private static function getProductTags($type, $itemId, $select = 0, $checkPublish = 0) {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if ($select == 1) {
            $query = 'SELECT r.tag_id';
        } else if ($select == 2) {
            $query = 'SELECT a.id, ' . I18nHelper::sqlCoalesce(['alias']);
        } else {
            $query = 'SELECT a.id, ' . I18nHelper::sqlCoalesce(['title', 'alias']) . ', a.type, a.display_format, a.link_ext, a.link_cat, a.icon_class, a.params';
        }

        $query .= ' FROM #__phocacart_tags AS a'
            . ' LEFT JOIN ' . self::getRelatedTable($type) . ' AS r ON a.id = r.tag_id';

        $query .= I18nHelper::sqlJoin('#__phocacart_tags_i18n');
        //$query .= ' WHERE a.type in (' . implode(', ', $type) . ')'
            $query .= ' WHERE a.type in (' . (int)$type. ')'
            . ' AND r.item_id = ' . (int)$itemId;



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

    private static function getSubmittedProductTags($type, $itemId) {
        $db    = Factory::getDBO();
        $query = 'SELECT a.items_item';
        $query .= ' FROM #__phocacart_submit_items AS a'
            . ' WHERE a.id = ' . (int)$itemId;
        $db->setQuery($query);
        $items = $db->loadResult();

        if (!empty($items)) {
            $itemsA = json_decode($items, true);
            switch ($type) {
                case TagType::Tag:
                default:
                    $fieldName = 'tags';
                break;
                case TagType::Label:
                    $fieldName = 'taglabels';
                break;
            }

            if (isset($itemsA[$fieldName])) {
                return $itemsA[$fieldName];
            }
        }

        return [];
    }

    private static function getProductsTags($type, $cids) {
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
    public static function getTags($itemId, $select = 0, $checkPublish = 0) {
        return self::getProductTags(TagType::Tag, $itemId, $select, $checkPublish);
    }

    /*
     * TAGS (stored in submitted items) Field JFormFieldPhocaTagsSubmitItems
     */
    public static function getTagsSubmitItems($itemId) {
        return self::getSubmittedProductTags(TagType::Tag, $itemId);
    }

    public static function getTagsByIds($cids) {
        return self::getProductsTags(TagType::Tag, $cids);
    }

    /**
     * Labels - are displayed at the top
     * @param int $itemId
     * @param number $select
     * @return mixed|void|mixed[]
     */
    public static function getTagLabels($itemId, $select = 0, $checkPublish = 0) {
        return self::getProductTags(TagType::Label, $itemId, $select, $checkPublish);
    }

    /*
     * TAGLABELS (stored in submitted items) Field JFormFieldPhocaTaglabelsSubmitItems
     */
    public static function getTagLabelsSubmitItems($itemId) {
        return self::getSubmittedProductTags(TagType::Label, $itemId);
    }

    public static function getTagsLabelsByIds($cids) {
        return self::getProductsTags(TagType::Label, $cids);
    }

    /**
     * @param int $ordering
     * @param int $onlyAvailableProducts
     * @param int $type 0 ... tag, 1 ... label
     * @param string $lang
     * @return mixed
     */
    public static function getAllTags($ordering = 1, $onlyAvailableProducts = 0, $type = TagType::Tag, $lang = '', $filterProducts = array(), $limitCount = -1) {
        $wheres = array();
        $lefts  = array();

        $wheres[] = ' t.type = ' . (int)$type;

        $orderingText = PhocacartOrdering::getOrderingText($ordering, 3);

        $columns = 't.id, t.type, t.count_products';
        /*if (I18nHelper::useI18n()) {
            $columns .= ', coalesce(i18n_t.title, t.title) as title, coalesce(i18n_t.alias, t.alias) as  alias';
        } else {
            $columns   .= ', t.title, t.alias';
        }*/

        $columns .= I18nHelper::sqlCoalesce(['title', 'alias'], 't', '', '', ',');

        $wheres[] = ' t.published = 1';

        $productTableAdded = 0;

        if ($onlyAvailableProducts == 1) {
            if ($lang != '' && $lang != '*') {
                $wheres[] = PhocacartUtilsSettings::getLangQuery('p.language', $lang);
            }

            $lefts[]           = ' ' . self::getRelatedTable($type) . ' AS tr ON tr.tag_id = t.id';
            $lefts[]           = ' #__phocacart_products AS p ON tr.item_id = p.id';
            $productTableAdded = 1;
            $rules             = PhocacartProduct::getOnlyAvailableProductRules();
            $wheres            = array_merge($wheres, $rules['wheres']);
            $lefts             = array_merge($lefts, $rules['lefts']);

        } else {
            if ($lang != '' && $lang != '*') {
                $wheres[]          = PhocacartUtilsSettings::getLangQuery('p.language', $lang);
                $lefts[]           = ' ' . self::getRelatedTable($type) . ' AS tr ON tr.tag_id = t.id';
                $lefts[]           = ' #__phocacart_products AS p ON tr.item_id = p.id';
                $productTableAdded = 1;
            }
        }

        if (!empty($filterProducts)) {
            $productIds = implode(',', $filterProducts);
            $wheres[]   = 'p.id IN (' . $productIds . ')';
            if ($productTableAdded == 0) {
                $lefts[] = ' ' . self::getRelatedTable($type) . ' AS tr ON tr.tag_id = t.id';
                $lefts[] = ' #__phocacart_products AS p ON tr.item_id = p.id';
            }
        }

        if ((int)$limitCount > -1) {
            $wheres[] = " t.count_products > " . (int)$limitCount;
        }


        $q = ' SELECT DISTINCT ' . $columns
            . ' FROM  #__phocacart_tags AS t'
            . (!empty($lefts) ? ' LEFT JOIN ' . implode(' LEFT JOIN ', $lefts) : '')
            . I18nHelper::sqlJoin('#__phocacart_tags_i18n', 't')
            . (!empty($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '')
            . ' ORDER BY ' . $orderingText;

        $db = Factory::getDBO();
        $db->setQuery($q);

        return $db->loadObjectList();
    }

    private static function storeProductTags($type, $tagsArray, $itemId) {

        if ((int)$itemId <= 0) {
            return;
        }

        $db    = Factory::getDBO();
        $query = ' DELETE '
            . ' FROM ' . self::getRelatedTable($type)
            . ' WHERE item_id = ' . (int)$itemId;
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

    public static function storeTags($tagsArray, $itemId) {
        self::storeProductTags(TagType::Tag, $tagsArray, $itemId);
    }

    public static function storeTagLabels($tagsArray, $itemId) {
        self::storeProductTags(TagType::Label, $tagsArray, $itemId);
    }

    public static function getAllTagsList($order = 'id', $type = TagType::Tag) {
        $db    = Factory::getDBO();
        $query = 'SELECT a.id AS value, a.title AS text'
            . ' FROM #__phocacart_tags AS a'
            . ' WHERE a.type =' . (int)$type
            . ' ORDER BY ' . $order;
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getAllTagsSelectBox($name, $id, $activeArray, $javascript = null, $order = 'id', $type = TagType::Tag, $attributes = '') {
        return HTMLHelper::_('select.genericlist', self::getAllTagsList($order, $type), $name, $attributes, 'value', 'text', $activeArray, $id);
    }

    /**
     *
     * @param int $itemId
     * @param number $types 0 ... nothing, 1 ... tags only, 2 ... labels only, 3 ... tags and labels
     * @return string
     */
    public static function getTagsRendered($itemId, $types = 0, $separator = '')
    {
        if ($types == 1) {
            // Only tags
            $tags = self::getTags($itemId, 0, 1);
        } else if ($types == 2) {
            // Only labels
            $tags = self::getTagLabels($itemId, 0, 1);
        } else if ($types == 3) {
            // Tags and Labels together (they can be displayed as labels in category/items view)
            //$tags = self::getProductTags(TagType::Tag, $itemId, 0, 1);
            $t 	= self::getTags($itemId);
		    $l 	= self::getTagLabels($itemId);
		    $tags = array_merge($t, $l);
        } else {
            return '';
        }

        $tagsLinks = PhocacartUtils::getComponentParameters()->get('tags_links', 0);

        $html = [];
        $i = 0;

        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $tag->params = new Registry($tag->params);

                $class = ' ph-tag-' . $tag->alias;
                $style = '';
                if ($tag->params->get('background')) {
                    $style .= 'background-color: ' . $tag->params->get('background') . ' !important;';
                }
                if ($tag->params->get('foreground')) {
                    $style .= 'color: ' . $tag->params->get('foreground') . ' !important;';
                }
                if ($tag->params->get('class')) {
                    $class .= ' ' . $tag->params->get('class');
                }
                $style = $style ? ' style="' . $style . '"' : '';

                if ($types == 2 || $types == 3) {
                    $html[$i] = '<div class="ph-corner-icon-wrapper"><div class="ph-corner-icon ph-corner-icon-' . $tag->alias . $class . '"' . $style . '>';
                } else {
                    $html[$i] = '<span class="' . PhocacartRenderStyle::class('label.label-info') . $class . '"' . $style . '>';
                }

                $dO = htmlspecialchars($tag->title);

                if ($tag->display_format == 2) {
                    if ($tag->icon_class != '') {
                        $dO = '<span class="' . htmlspecialchars(strip_tags($tag->icon_class)) . '"></span>';
                    } else {
                        $dO = $tag->title;
                    }
                } else if ($tag->display_format == 3) {
                    if ($tag->icon_class != '') {
                        $dO = '<span class="' . htmlspecialchars(strip_tags($tag->icon_class)) . '"></span>';
                    }
                    $dO .= $tag->title;
                }

                if ($tagsLinks == 0) {
                    $html[$i] .= $dO;
                } else if ($tagsLinks == 1) {
                    if ($tag->link_ext != '') {
                        $html[$i] .= '<a href="' . $tag->link_ext . '">' . $dO . '</a>';
                    } else {
                        $html[$i] .= $dO;
                    }
                } else if ($tagsLinks == 2) {
                    if ($tag->link_cat != '') {
                        $category = PhocacartCategory::getCategoryById($tag->link_cat);
                        if ($category) {
                            $link  = PhocacartRoute::getCategoryRoute($category->id, $category->alias);
                            $html[$i] .= '<a href="' . $link . '">' . $dO . '</a>';
                        } else {
                            $html[$i] .= $dO;
                        }
                    } else {
                        $html[$i] .= $dO;
                    }
                } else if ($tagsLinks == 3) {
                    $link = PhocacartRoute::getItemsRoute();
                    //if ($types == 2 || $types == 3) {
                    // Even if the tag is displayed in label area, the link still needs to go to tag output, not label output
                    if ($tag->type == 1){
                        $link = $link . PhocacartRoute::getItemsRouteSuffix('label', $tag->id, $tag->alias);
                    } else {
                        $link = $link . PhocacartRoute::getItemsRouteSuffix('tag', $tag->id, $tag->alias);
                    }

                    $html[$i] .= '<a href="' . Route::_($link) . '">' . $dO . '</a>';
                }

                if ($types == 2 || $types == 3) {
                    $html[$i] .= '</div></div>';
                } else {
                    $html[$i] .= '</span>';
                }

                $i++;
            }

        }

        return implode($separator, $html);
    }


    public static function getTagType($type = TagType::Tag) {
        switch ($type) {
            case TagType::Label:
                return Text::_('COM_PHOCACART_LABEL');
            default:
                return Text::_('COM_PHOCACART_TAG');
        }
    }

    private static function getActiveItems($type, $ids, $ordering) {
        $db       = Factory::getDbo();
        $o        = array();
        $wheres   = array();
        $ordering = PhocacartOrdering::getOrderingText($ordering, 3);//t
        if ($ids) {
            $wheres[] = 't.id IN (' . $ids . ')';
            $wheres[] = 't.type = ' . (int)$type;
            /*$q = 'SELECT DISTINCT t.title, CONCAT(t.id, \'-\', t.alias) AS alias, \'tag\' AS parameteralias, \'tag\' AS parametertitle FROM #__phocacart_tags AS t'
              . (!empty($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '')
              . ' GROUP BY t.alias, t.title'
              . ' ORDER BY ' . $ordering;
      */
            // FULL GROUP BY GROUP_CONCAT(DISTINCT o.title) AS title
            $q = 'SELECT DISTINCT '
                . I18nHelper::sqlCoalesce(['alias'], 't', '', 'concatid') . ', '
                . I18nHelper::sqlCoalesce(['title'], 't') . ', ';

            if ($type == 1) {
                $q .= '\'label\' AS parameteralias, \'label\' AS parametertitle FROM #__phocacart_tags AS t';
            } else {
                $q .= '\'tag\' AS parameteralias, \'tag\' AS parametertitle FROM #__phocacart_tags AS t';
            }

            $q .= I18nHelper::sqlJoin('#__phocacart_tags_i18n', 't')
                . (!empty($wheres) ? ' WHERE ' . implode(' AND ', $wheres) : '')
                . ' GROUP BY t.alias, t.title'
                . ' ORDER BY ' . $ordering;

            $db->setQuery($q);
            $o = $db->loadAssocList();
        }
        return $o;
    }

    public static function getActiveTags($items, $ordering) {
        return self::getActiveItems(TagType::Tag, $items, $ordering);
    }

    public static function getActiveLabels($items, $ordering) {
        return self::getActiveItems(TagType::Label, $items, $ordering);
    }

    public static function deleteTagsLabelsRelated(array $ids): bool
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // The id of tags and labels is unique for both so we can delete IDs from both tables without asking their type
        $resultTag = false;

        $query = $db->getQuery(true)
            ->delete('#__phocacart_tags_related')
            ->whereIn($db->quoteName('tag_id'), $ids);

        $db->setQuery($query);
        if ($db->execute()) {
            $resultTag = true;
        }

        $resultLabel = false;

        $query = $db->getQuery(true)
            ->delete('#__phocacart_taglabels_related')
            ->whereIn($db->quoteName('tag_id'), $ids);

        $db->setQuery($query);
        if ($db->execute()) {
            $resultLabel = true;
        }
        if ($resultTag && $resultLabel) {
            return true;
        } else {
            return false;
        }
    }
}
