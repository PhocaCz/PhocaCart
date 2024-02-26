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

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Phoca\PhocaCart\I18n\I18nHelper;

jimport('joomla.application.component.model');

final class PhocacartCategory
{
    public const TYPE_COMMON = 0;
    public const TYPE_SHOP_ONLY = 1;
    public const TYPE_POS_ONLY = 2;

    private static ?array $categoriesCache = null;
    private static $categoryA = array();
    private static $categoryF = array();
    private static $categoryP = array();

    public static function CategoryTreeOption($data, $tree, $id = 0, $text = '', $currentId = 0) {
        foreach ($data as $key) {
            $show_text = $text . $key->text;

            if ($key->parentid == $id && $currentId != $id && $currentId != $key->value) {
                $tree[$key->value] = (object)[
                    'text' => $show_text,
                    'value' => $key->value,
                ];
                $tree              = self::CategoryTreeOption($data, $tree, $key->value, $show_text . " - ", $currentId);
            }
        }

        return $tree;
    }

    public static function filterCategory($query, $active = null, $frontend = null, $onChange = true, $fullTree = null) {
        $db = Factory::getDBO();

        $form = 'adminForm';
        if ($frontend == 1) {
            $form = 'phocacartproductsform';
        }

        if ($onChange) {
            $onChO = 'class="form-control" size="1" onchange="document.' . $form . '.submit( );"';
        } else {
            $onChO = 'class="form-control" size="1"';
        }

        $categories[] = HTMLHelper::_('select.option', '0', '- ' . Text::_('COM_PHOCACART_SELECT_CATEGORY') . ' -');
        $db->setQuery($query);
        $catData = $db->loadObjectList();

        if ($fullTree) {
            // Start - remove in case there is a memory problem
            self::loadCategoriesCache();
            $catDataAll = self::$categoriesCache;
            array_walk($catDataAll, function ($category) {
                $category->text     = $category->title;
                $category->value    = $category->id;
                $category->parentid = $category->parent_id;
            });

            $catDataTree = PhocacartCategory::CategoryTreeOption($catDataAll, [], 0, '', -1);

            $catDataTreeRights = array();

            foreach ($catDataTree as $k => $v) {
                foreach ($catData as $v2) {
                    if ($v->value == $v2->value) {
                        $catDataTreeRights[$k]        = new StdClass();
                        $catDataTreeRights[$k]->text  = $v->text;
                        $catDataTreeRights[$k]->value = $v->value;
                    }
                }
            }

            $catDataTree = $catDataTreeRights;
            // End - remove in case there is a memory problem

            // Uncomment in case there is a memory problem
            //$catDataTree	= $catData;
        } else {
            $catDataTree = $catData;
        }

        $categories = array_merge($categories, $catDataTree);

        $category = HTMLHelper::_('select.genericlist', $categories, 'catid', $onChO, 'value', 'text', $active);

        return $category;
    }

    public static function options($type = 0) {
        self::loadCategoriesCache();

        $items = array_filter(self::$categoriesCache, function ($category) {
            return !!$category->published;
        });

        array_walk($items, function ($category) {
            $category->text     = $category->title;
            $category->value    = $category->id;
            $category->parentid = $category->parent_id;
        });

        return PhocacartCategory::CategoryTreeOption($items, [], 0, '', -1);
    }

    private static function loadCategoriesCache(): void {
        if (self::$categoriesCache === null) {
            $db = Factory::getDBO();

            $db->setQuery('SELECT a.*, ' . I18nHelper::sqlCoalesce(['title', 'alias']) . ', null AS children ' .
                'FROM #__phocacart_categories AS a ' .
                I18nHelper::sqlJoin('#__phocacart_categories_i18n') .
                'ORDER BY a.ordering, a.id');
            /*if (I18nHelper::useI18n()) {
                $db->setQuery('SELECT a.*, coalesce(i18n.title, a.title) as title, coalesce(i18n.alias, a.alias) as alias, null AS children ' .
                    'FROM #__phocacart_categories AS a ' .
                    I18nHelper::sqlJoin('#__phocacart_categories_i18n') .
                    'ORDER BY a.ordering, a.id');
            } else {
                $db->setQuery('SELECT a.*, null AS children FROM #__phocacart_categories AS a ORDER BY a.ordering, a.id');
            }*/
            $categories = $db->loadObjectList('id') ?? [];

            $i18nData = [];
            foreach (I18nHelper::getI18nLanguages() as $langCode => $language) {
                $i18nData[$langCode] = (object)[];
            }

            array_walk($categories, function ($category) use ($categories, $i18nData) {
                if (I18nHelper::isI18n()) {
                    $category->i18n = $i18nData;
                }

                if ($category->parent_id) {
                    if ($categories[$category->parent_id]->children === null)
                        $categories[$category->parent_id]->children = [];
                    $categories[$category->parent_id]->children[] = $category;
                }
            });

            if (I18nHelper::isI18n()) {
                $db->setQuery('SELECT i18n.* FROM #__phocacart_categories_i18n AS i18n');
                $i18n = $db->loadObjectList() ?? [];

                foreach ($i18n as $value) {
                    if (!array_key_exists($value->id, $categories)) {
                        continue;
                    }

                    if (!array_key_exists($value->language, $categories[$value->id]->i18n)) {
                        continue;
                    }

                    $categories[$value->id]->i18n[$value->language] = $value;
                }
            }

            self::$categoriesCache = $categories;
        }
    }

    public static function getCategories(): array {
        self::loadCategoriesCache();

        return self::$categoriesCache;
    }

    public static function getCategoryById($id) {
        self::loadCategoriesCache();

        return self::$categoriesCache[(int)$id] ?? null;
    }

    public static function getChildren($id) {
        self::loadCategoriesCache();
        if ($id) {
            $category = self::getCategoryById($id);
            return $category->children;
        }

        return [];
    }

    public static function getPath($path = array(), $id = 0, $parent_id = 0, $title = '', $alias = '') {
        if (empty(self::$categoryP[$id])) {
            self::$categoryP[$id] = self::getPathTree($path, $id, $parent_id, $title, $alias);
        }
        return self::$categoryP[$id];
    }

    public static function getPathTree($path = array(), $id = 0, $parent_id = 0, $title = '', $alias = '') {
        static $iCT = 0;

        if ((int)$id > 0) {
            $path[$iCT]['id']        = (int)$id;
            $path[$iCT]['catid']     = (int)$parent_id;
            $path[$iCT]['parent_id'] = (int)$parent_id;
            $path[$iCT]['title']     = $title;
            $path[$iCT]['alias']     = $alias;
        }

        if ((int)$parent_id > 0) {
            $category = self::getCategoryById($parent_id);

            if ($category) {
                $iCT++;

                $path = self::getPathTree($path, $category->id, $category->parent_id, $category->title, $category->alias);
            }
        }
        return $path;
    }

    public static function getPathRouter($path = array(), $id = 0, $parent_id = 0, $title = '', $alias = '', ?string $i18nLanguage = null) {
        return self::getPathTreeRouter($path, $id, $parent_id, $title, $alias, $i18nLanguage);
    }

    public static function getPathTreeRouter($path = array(), $id = 0, $parent_id = 0, $title = '', $alias = '', ?string $i18nLanguage = null) {
        if ((int)$id > 0) {
            $path[$id] = (int)$id . ':' . $alias;
        }

        if ((int)$parent_id > 0) {
            $category = self::getCategoryById($parent_id);
            if ($category) {
                $title = $category->title;
                $alias = $category->alias;
                if ($i18nLanguage !== null) {
                    $title = $category->i18n[$i18nLanguage]->title ?? $title;
                    $alias = $category->i18n[$i18nLanguage]->alias ?? $alias;
                }

                $path = self::getPathTreeRouter($path, $category->id, $category->parent_id, $title, $alias, $i18nLanguage);
            }
        }

        return $path;
    }

    public static function categoryTree($data, $root = 0, $parentKey = 'parent_id', $key = 'id', $childrenKey = 'children') {
        $tree = [];
        foreach ($data as $element) {
            isset($tree[$element[$parentKey]]) ?: $tree[$element[$parentKey]] = [];
            isset($tree[$element[$key]]) ?: $tree[$element[$key]] = [];
            $tree[$element[$parentKey]][] = array_merge($element, [$childrenKey => &$tree[$element[$key]]]);
        }

        //return $tree[$r][0]; // remove [0] if there could be more than one root nodes
        if (isset($tree[$root])) {
            return $tree[$root];
        }

        return 0;
    }

    public static function nestedToFlat(array $data, $level = 0): array {
        if (!$data)
            return [];

        $result = [];
        foreach ($data as $v) {
            $v['nested_level'] = $level;
            $result[]          = $v;
            $result            = array_merge($result, self::nestedToFlat($v['children'], $level + 1));
        }

        return $result;
    }

    public static function nestedToUl($data, $currentCatid = 0) {
        $result = [];

        if ($data) {
            $result[] = '<ul>';
            foreach ($data as $v) {
                $link = Route::_(PhocacartRoute::getCategoryRoute($v['id'], $v['alias']));

                $result[]
                    = '<li' . ($currentCatid == $v['id'] ? ' data-jstree=\'{"opened":true,"selected":true}\'' : '') . '>' .
                    '<a href="' . $link . '">' . $v['title'] . '</a>' .
                    self::nestedToUl($v['children'], $currentCatid) .
                    '</li>';
            }
            $result[] = '</ul>';
        }

        return implode("\n", $result);
    }

    public static function nestedToUlSimple($data, $currentCatid = 0): string {
        $result = [];

        if ($data) {
            $result[] = '<ul>';
            foreach ($data as $v) {
                $link = Route::_(PhocacartRoute::getCategoryRoute($v['id'], $v['alias']));
                $result[]
                      = '<li' . ($currentCatid == $v['id'] ? ' class="ph-active"' : '') . '>' .
                    '<a href="' . $link . '">' . $v['title'] . '</a>' .
                    self::nestedToUlSimple($v['children'], $currentCatid) .
                    '</li>';
            }
            $result[] = '</ul>';
        }

        return implode("\n", $result);
    }

    public static function nestedToCheckBox($data, $d, $currentCatid = 0, &$active = 0, $forceCategoryId = 0) {
        $s      = PhocacartRenderStyle::getStyles();
        $result = [];

        if ($data) {
            $result[] = '<ul class="ph-filter-module-category-tree">';
            foreach ($data as $v) {
                $checked = '';
                $value   = htmlspecialchars($v['alias']);
                if (isset($d['nrinalias']) && $d['nrinalias'] == 1) {
                    $value = (int)$v['id'] . '-' . htmlspecialchars($v['alias']);
                }

                if (in_array($value, $d['getparams'])) {
                    $checked = 'checked';
                    $active  = 1;
                }

                // This only can happen in category view (category filters are empty, id of category is larger then zero)
                // This is only marking the category as active in category list
                if (empty($d['getparams']) || (isset($d['getparams'][0]) && $d['getparams'][0] == '')) {
                    // Empty parameters, so we can set category id by id of category view
                    if ($forceCategoryId > 0 && (int)$forceCategoryId == (int)$v['id']) {
                        $checked = 'checked';
                        $active  = 1;
                    }
                }

                $count = '';
                // If we are in item view - one category is selected but if user click on filter to select other category, this one should be still selected - we go to items view with 2 selected
                // because force category is on
                if (isset($v['count_products']) && isset($d['params']['display_category_count']) && $d['params']['display_category_count'] == 1) {
                    $count = ' <span class="ph-filter-count">' . (int)$v['count_products'] . '</span>';
                }

                $icon = '';
                if ($v['icon_class'] != '') {
                    $icon = '<span class="' . PhocacartText::filterValue($v['icon_class'], 'text') . ' ph-filter-item-icon"></span> ';
                }

                $jsSet = '';

                if (isset($d['forcecategory']['idalias']) && $d['forcecategory']['idalias'] != '') {
                    // Category View - force the category parameter if set in parameters
                    $jsSet .= 'phChangeFilter(\'c\', \'' . $d['forcecategory']['idalias'] . '\', 1,  \'text\', 0, 1, 1);'; // ADD IS FIXED ( use "text" as formType - it cannot by managed by checkbox, it is fixed - always 1 - does not depends on checkbox, it is fixed 1
                }

                $jsSet .= 'phChangeFilter(\'' . $d['param'] . '\', \'' . $value . '\', this, \'' . $d['formtype'] . '\',\'' . $d['uniquevalue'] . '\', 0, 1);';// ADD OR REMOVE

                $result[] = '<li><div class="' . $s['c']['controls'] . '">';
                $result[] = '<label class="ph-checkbox-container"><input class="' . $s['c']['inputbox.checkbox'] . '" type="checkbox" name="tag" value="' . $value . '" ' . $checked . ' onchange="' . $jsSet . '" />' . $icon . $v['title'] . $count . '<span class="ph-checkbox-checkmark"></span></label>';
                $result[] = '</div></li>';
                $result[] = self::nestedToCheckBox($v['children'], $d, $currentCatid, $active, $forceCategoryId);
            }
            $result[] = '</ul>';
        }

        return implode("\n", $result);
    }

    /**
     * old syntax public static function getCategoryTreeFormat($ordering = 1, $display = '', $hide = '', $type = [self::TYPE_COMMON, self::TYPE_SHOP_ONLY], $lang = '', $format = 'js')
     *
     * @param $params
     * @return mixed|string
     */
    public static function getCategoryTreeFormat($params = []) {
        if (!is_array($params)) {
            @trigger_error(
                'Using separate parameters is deprecated, use array params instead.',
                E_USER_DEPRECATED
            );

            $args   = func_get_args();
            $params = [
                'ordering' => $args[0] ?? 1,
                'display' => $args[1] ?? null,
                'hide' => $args[2] ?? null,
                'type' => $args[3] ?? [self::TYPE_COMMON, self::TYPE_SHOP_ONLY],
                'lang' => $args[4] ?? null,
                'format' => $args[5] ?? 'js',
            ];
        }

        $params = array_merge([
            'ordering' => 1,
            'display' => null,                                   // comma separetd list of displayed category ids
            'hide' => null,                                      // comma separetd list of hidden category ids
            'type' => [self::TYPE_COMMON, self::TYPE_SHOP_ONLY], // Category type filter - Common, Shop only, POS only
            'lang' => null,                                      // language
            'limitCount' => -1,                                  // only categories with certain products count
            'featured' => null,                                  // null -> all categories, true -> only featured, false -> only not featured
            'category_type' => null,                             // null -> all category types, array -> only selected types
            'format' => 'js',                                    // output format
        ], $params);

        $cis = md5(serialize($params));
        if (empty(self::$categoryF[$cis])) {
            $itemOrdering = PhocacartOrdering::getOrderingText($params['ordering'], 1);

            if ($itemOrdering != '') {
                $itemOrdering = 'c.parent_id, ' . $itemOrdering;
            }

            $db         = Factory::getDBO();
            $wheres     = array();
            $user       = PhocacartUser::getUser();
            $userLevels = implode(',', $user->getAuthorisedViewLevels());
            $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));
            $wheres[]   = " c.access IN (" . $userLevels . ")";
            $wheres[]   = " (gc.group_id IN (" . $userGroups . ") OR gc.group_id IS NULL)";
            $wheres[]   = " c.published = 1";

            if ($params['lang'] && $params['lang'] != '*') {
                $wheres[] = PhocacartUtilsSettings::getLangQuery('c.language', $params['lang']);
            }

            if (!empty($params['type']) && is_array($params['type'])) {
                $wheres[] = " c.type IN (" . implode(',', $params['type']) . ")";
            }

            if ($params['display']) {
                $wheres[] = " c.id IN (" . $params['display'] . ")";
            }

            if ($params['hide']) {
                $wheres[] = " c.id NOT IN (" . $params['hide'] . ")";
            }

            if ($params['featured'] !== null) {
                $wheres[] = ' c.featured = ' . ($params['featured'] ? '1' : '0');
            }

            if ($params['category_type']) {
                $wheres[] = ' c.category_type in (' . implode(',', (array)$params['category_type']) . ')';
            }

            $columns    = 'c.id, c.parent_id, c.ordering';
            $groupsFull = $columns . ', c.title, c.alias';

            $columns .= I18nHelper::sqlCoalesce(['title', 'alias'], 'c', '', '', ',');

            /*if (I18nHelper::useI18n()) {
                $groupsFull = $columns . ', coalesce(i18n_c.title, c.title), coalesce(i18n_c.alias, c.alias)';
                $columns .= ', coalesce(i18n_c.title, c.title) as title, coalesce(i18n_c.alias, c.alias) as  alias';
            } else {
                $columns   .= ', c.title, c.alias';
                $groupsFull = $columns;
            }*/


            $groupsFast = 'c.id';
            $groups     = PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

            $query = 'SELECT ' . $columns
                . ' FROM #__phocacart_categories AS c'
                . I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'c')
                . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
                . ' WHERE ' . implode(' AND ', $wheres)
                . ' GROUP BY ' . $groups
                . ' ORDER BY ' . $itemOrdering;

            $db->setQuery($query);

            $items = $db->loadAssocList();
            $tree  = self::categoryTree($items);

            $currentCatid = self::getActiveCategoryId();
            switch ($params['format']) {
                case 'simple':
                    self::$categoryF[$cis] = self::nestedToUlSimple($tree, $currentCatid);
                break;
                default:
                    self::$categoryF[$cis] = self::nestedToUl($tree, $currentCatid);
                break;
            }
        }

        return self::$categoryF[$cis];
    }


    /**
     * old syntax public static function getCategoryTreeArray($ordering = 1, $display = '', $hide = '', $type = array(0,1), $lang = '', $limitCount = -1)
     *
     * @param array $params
     * @return array|int|mixed
     */
    public static function getCategoryTreeArray($params = []) {
        if (!is_array($params)) {
            @trigger_error(
                'Using separate parameters is deprecated, use array params instead.',
                E_USER_DEPRECATED
            );

            $args   = func_get_args();
            $params = [
                'ordering' => $args[0] ?? 1,
                'display' => $args[1] ?? null,
                'hide' => $args[2] ?? null,
                'type' => $args[3] ?? [self::TYPE_COMMON, self::TYPE_SHOP_ONLY],
                'lang' => $args[4] ?? null,
                'limitCount' => $args[5] ?? -1,
            ];
        }

        $params = array_merge([
            'ordering' => 1,
            'display' => null,                                   // comma separetd list of displayed category ids
            'hide' => null,                                      // comma separetd list of hidden category ids
            'type' => [self::TYPE_COMMON, self::TYPE_SHOP_ONLY], // Category type filter - Common, Shop only, POS only
            'lang' => null,                                      // language
            'limitCount' => -1,                                  // only categories with certain products count
            'featured' => null,                                  // null -> all categories, true -> only featured, false -> only not featured
        ], $params);

        $cis = md5(serialize($params));
        if (empty(self::$categoryA[$cis])) {
            $itemOrdering = PhocacartOrdering::getOrderingText($params['ordering'], 1);

            if ($itemOrdering != '') {
                $itemOrdering = 'c.parent_id, ' . $itemOrdering;
            }

            $db         = Factory::getDBO();
            $user       = PhocacartUser::getUser();
            $userLevels = implode(',', $user->getAuthorisedViewLevels());
            $userGroups = implode(',', PhocacartGroup::getGroupsById($user->id, 1, 1));
            $wheres     = [
                ' c.access IN (' . $userLevels . ')',
                ' (gc.group_id IN (' . $userGroups . ') OR gc.group_id IS NULL)',
                ' c.published = 1'
            ];

            if ($params['lang'] && $params['lang'] != '*') {
                $wheres[] = PhocacartUtilsSettings::getLangQuery('c.language', $params['lang']);
            }

            if ($params['type'] && is_array($params['type'])) {
                $wheres[] = ' c.type IN (' . implode(',', $params['type']) . ')';
            }

            if ($params['display']) {
                $wheres[] = ' c.id IN (' . $params['display'] . ')';
            }

            if ($params['hide'] != '') {
                $wheres[] = ' c.id NOT IN (' . $params['hide'] . ')';
            }

            if ((int)$params['limitCount'] > -1) {
                $wheres[] = ' c.count_products > ' . (int)$params['limitCount'];
            }

            if ($params['featured'] !== null) {
                $wheres[] = ' c.featured = ' . ($params['featured'] ? '1' : '0');
            }

            $columns = 'c.id, c.parent_id, c.ordering';
            /* if (I18nHelper::useI18n()) {
                 $columns .= ', coalesce(i18n_c.title, c.title) as title, coalesce(i18n_c.alias, c.alias) as  alias';
             } else {
                 $columns   .= ', c.title, c.alias';
             }*/
            $columns .= I18nHelper::sqlCoalesce(['title', 'alias'], 'c', '', '', ',');
            $columns .= ', c.icon_class, c.image, c.description, c.count_products';
            $query   = 'SELECT ' . $columns
                . ' FROM #__phocacart_categories AS c'
                . I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'c')
                . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
                . ' WHERE ' . implode(' AND ', $wheres)
                . ' ORDER BY ' . $itemOrdering;

            $db->setQuery($query);
            $items = $db->loadAssocList();

            self::$categoryA[$cis] = self::categoryTree($items);
        }

        return self::$categoryA[$cis];
    }

    public static function getActiveCategoryId() {
        $app    = Factory::getApplication();
        $option = $app->input->get('option', '', 'string');
        $view   = $app->input->get('view', '', 'string');
        $catid  = $app->input->get('catid', '', 'int'); // ID in items view is category id
        $id     = $app->input->get('id', '', 'int');
        $c      = $app->input->get('c', '', 'string');// Category ID in items view - filter options (does not work with ajax)

        if ($option == 'com_phocacart' && ($view == 'items' || $view == 'category') && (int)$id > 0) {
            return $id;
        }

        if ($option == 'com_phocacart' && $view == 'item' && (int)$catid > 0) {
            return $catid;
        }

        // If in filtering only one category is selected, make active the selected category in e.g. tree (not working in ajax)
        if ($option == 'com_phocacart' && $view == 'items' && $c != '') {
            $cA = explode(',', $c);
            if (isset($cA[0]) && count($cA) == 1) {
                return (int)$cA[0];
            }
        }

        return 0;
    }

    public static function getActiveCategories($items, $ordering) {
        if (!$items) {
            return [];
        }

        $db       = Factory::getDbo();
        $ordering = PhocacartOrdering::getOrderingText($ordering, 1);//c
      /*  $q        = 'SELECT DISTINCT c.title, CONCAT(c.id, \'-\', c.alias) AS alias, \'c\' AS parameteralias, \'category\' AS parametertitle FROM #__phocacart_categories AS c'
            . ' WHERE c.id IN (' . $items . ')'
            . ' GROUP BY c.alias, c.title'
            . ' ORDER BY ' . $ordering;
*/

        $q = 'SELECT DISTINCT '
            . I18nHelper::sqlCoalesce(['alias'], 'c', '', 'concatid') . ', '
            . I18nHelper::sqlCoalesce(['title'], 'c') . ', '
            .'\'c\' AS parameteralias, \'category\' AS parametertitle FROM #__phocacart_categories AS c'
            . I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'c')
            . ' WHERE c.id IN (' . $items . ')'
            . ' GROUP BY c.alias, c.title'
            . ' ORDER BY ' . $ordering;


        $db->setQuery($q);
        return $db->loadAssocList();
    }

    public static function getCategoryTitleById($id) {
        $category = self::getCategoryById($id);

        return $category ? $category->title : null;
    }

}
