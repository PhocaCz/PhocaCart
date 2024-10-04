<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_phocagallery
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\ParameterType;
use Phoca\PhocaCart\I18n\I18nHelper;

require_once(JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/bootstrap.php');

class PhocacartRouter extends RouterView
{
    protected $noIDs = false;

    public function __construct($app = null, $menu = null) {
        $viewsNoId = array('checkout', 'comparison', 'download', 'terms', 'account', 'orders', 'payment', 'info', 'wishlist', 'pos', 'submit');
        $viewsId   = array('feed');

        $params = ComponentHelper::getParams('com_phocacart');;
        $this->noIDs = (bool)$params->get('remove_sef_ids');

        // Is the main menu type categories view or items view - does categories view exist?
        $component        = ComponentHelper::getComponent('com_phocacart');
        $attributes       = array('component_id');
        $values           = array($component->id);
        $items            = $menu->getItems($attributes, $values);
        $isCategoriesView = false;
        $isItemsView      = false;
        if (!empty($items)) {
            foreach ($items as $k => $v) {
                if (isset($v->query['view']) && $v->query['view'] == 'categories') {
                    $isCategoriesView = true;
                } else if (isset($v->query['view']) && $v->query['view'] == 'items') {
                    // TEST items view
                    $isItemsView = true;
                }
            }
        }

        if ($isItemsView && !$isCategoriesView) {
            // Items view exist but Categories view not
            // Then items view is the alternative to categories view
            $categories = new RouterViewConfiguration('items');
            $categories->setKey('id');
            $categories->setName('items');
            $this->registerView($categories);
        } else {
            // In all other cases standard router is done
            $categories = new RouterViewConfiguration('categories');
            $categories->setKey('id');
            $this->registerView($categories);

            $items = new RouterViewConfiguration('items');
            //$items->setKey('id');
            $items->setParent($categories, 'parent_id');
            $items->setName('items');
            $this->registerView($items);
        }

        $category = new RouterViewConfiguration('category');
        $category->setKey('id')->setParent($categories, 'parent_id')->setNestable();
        $this->registerView($category);

        $item = new RouterViewConfiguration('item');
        $item->setKey('id')->setParent($category, 'catid');//->setNestable();
        $this->registerView($item);


        $question = new RouterViewConfiguration('question');
        $question->setName('question');
        $question->setParent($categories, 'parent_id');
        $question->setParent($category, 'catid');
        $question->setParent($item, 'productid');
        $question->setKey('id');// ID is not used by question but we need it because of SEF url (id is transformed to suffix "question" and product id replaces the ID in process
        // Question is managed by product id, not by question id

        $this->registerView($question);

        foreach ($viewsId as $k => $v) {
            $it = new RouterViewConfiguration($v);
            $it->setParent($categories);
            $it->setName($v);
            $it->setKey('id');
            $this->registerView($it);
        }

        foreach ($viewsNoId as $k => $v) {
            $it = new RouterViewConfiguration($v);
            $it->setParent($categories, 'parent_id');
            $it->setName($v);
            $this->registerView($it);
        }

        parent::__construct($app, $menu);

        $this->attachRule(new MenuRules($this));
        $this->attachRule(new PhocacartRouterrules($this));
        $this->attachRule(new StandardRules($this));
        $this->attachRule(new NomenuRules($this));
    }

    public function getCategoriesSegment($id, $query) {
        return $this->getCategorySegment($id, $query);
    }

    public function getItemsSegment($id, $query) {
        return $this->getCategorySegment($id, $query);
    }

    public function getCategorySegment($id, $query) {
        $category = PhocacartCategory::getCategoryById($id);

        if ($category) {

            // We cannot use the same way like getItemSegment, because in getItemSegment, we get the alias of product
            // But in getCategorySegment, we also get alias of category BUT WE DON'T GET aliases (in not SEF link) of all parent categoryies
            // index.php?option=com_phocacart&view=item&id=1:productLang&catid=1:categoryLang&Itemid=X (we have alias of id, of category but not of parent cateogory, so we cannot work with received alias
            // if (!strpos($id, ':')) {
            $lang  = null;
            $alias = $category->alias;
            $title = $category->title;
            if (I18nHelper::isI18n()) {
                if (isset($query['lang'])) {
                    $lang = $query['lang'];
                } else {
                    $lang = Factory::getApplication()->getLanguage()->getTag();
                }
                $alias = $category->i18n[$lang]->alias ?? $alias;
                $title = $category->i18n[$lang]->title ?? $title;
            }
            $path = PhocacartCategory::getPathRouter(array(), (int)$category->id, $category->parent_id, $title, $alias, $lang);
            // } else {
            //              $path[(int)$id] = $id;
            // }

            $path[0] = '1:root';// we don't use root but it is needed when building urls with joomla methods

            if ($this->noIDs) {
                foreach ($path as &$segment) {
                    list($id, $segment) = explode(':', $segment, 2);
                }
            }

            return $path;
        }

        return [];
    }

    public function getItemSegment($id, $query) {

        static $cache = [];
        if (!strpos($id, ':')) {

            $lang = null;
            if (I18nHelper::isI18n()) {
                if (isset($query['lang'])) {
                    $lang = $query['lang'];
                } else {
                    $lang = Factory::getApplication()->getLanguage()->getTag();
                }
            }

            $cacheKey = $id . '-' . $lang;

            if (!array_key_exists($cacheKey, $cache)) {
                $db      = Factory::getDbo();
                $dbquery = $db->getQuery(true);
                $dbquery
                    ->from($dbquery->quoteName('#__phocacart_products', 'p'))
                    ->where('p.id = :id')
                    ->bind(':id', $id, ParameterType::INTEGER);
                if (I18nHelper::isI18n()) {
                    if (isset($query['lang'])) {
                        $lang = $query['lang'];
                    } else {
                        $lang = Factory::getApplication()->getLanguage()->getTag();
                    }

                    $dbquery
                        ->select('coalesce(' . $dbquery->quoteName('i18n_p.alias') . ', ' . $dbquery->quoteName('p.alias') . ')')
                        ->join('LEFT', $db->quoteName('#__phocacart_products_i18n', 'i18n_p'), 'i18n_p.id = p.id AND i18n_p.language = ' . $db->quote($lang));
                } else {
                    $dbquery->select($dbquery->quoteName('p.alias'));
                }
                $db->setQuery($dbquery);
                $cache[$cacheKey] = $db->loadResult();
            }

            $id .= ':' . $cache[$cacheKey];
        }

        if ($this->noIDs) {
            list($void, $segment) = explode(':', $id, 2);
            return array($void => $segment);
        }

        return array((int)$id => $id);
    }

    public function getQuestionSegment($id, $query) {

        // Specidific case
        // catid - category id
        // productid - product id
        // id - question id ( will be always 'question')
        return array((int)$id => 'question');

    }


    public function getQuestionId($segment, $query) {
        if ($segment == 'question') {
            // There is following url:
            // phoca-cart/category/product/question ... this seems to be question url for category and product
            // phoca-cart/category/product/abc ... this seems to be wrong URL ... 404 should be returned
            return $query['id'];// We need ID of product id
        }
        return false;
    }

    public function getCategoryId($segment, $query) {
        if (!isset($query['id']) && isset($query['view']) && $query['view'] == 'categories') {
            $query['id'] = 0;
        } else if (!isset($query['id']) && isset($query['view']) && $query['view'] == 'items') {
            $query['id'] = 0;
        }

        if ($this->noIDs) {
            $db      = Factory::getDbo();
            $dbquery = $db->getQuery(true);
            $dbquery->select($db->quoteName('c.id'))
                ->from($db->quoteName('#__phocacart_categories', 'c'))
                ->where($db->quoteName('c.parent_id') . ' = :parent_id')
                ->bind(':parent_id', $query['id'], ParameterType::INTEGER);

            if (isset($query['lang'])) {
                $lang = $query['lang'];
            } else {
                $lang = Factory::getApplication()->getLanguage()->getTag();
            }

            if (I18nHelper::isI18n()) {
                $dbquery
                    ->join('LEFT', $db->quoteName('#__phocacart_categories_i18n', 'i18n_c'), 'i18n_c.id = c.id AND i18n_c.language = ' . $db->quote($lang))
                    ->where('coalesce(' . $db->quoteName('i18n_c.alias') . ', ' . $db->quoteName('c.alias') . ')' . ' = :alias');
            } else {
                $dbquery->where($db->quoteName('c.alias') . ' = :alias');
            }

            $dbquery
                ->bind(':alias', $segment)
                ->bind(':parent_id', $query['id'], ParameterType::INTEGER);

            if (Multilanguage::isEnabled()) {
                $dbquery
                    ->where([
                        $db->quoteName('c.language') . ' in (:language, ' . $db->quote('*') . ')'
                    ])
                    ->bind(':language', $lang);
            }

            $db->setQuery($dbquery);

            return (int)$db->loadResult();
        }

        $category = false;
        if (isset($query['id'])) {
            if ((int)$query['id'] > 0) {
                $category = PhocacartCategory::getCategoryById($query['id']);
            } else if ((int)$segment > 0) {
                $category = PhocacartCategory::getCategoryById((int)$segment);
                if (isset($category->id) && (int)$category->id > 0 && $category->parent_id == 0) {
                    // We don't have root category with 0 so we need to start with segment one
                    return (int)$category->id;
                }
            }

            if ($category) {
                $subcategories = PhocacartCategory::getChildren($category->id);
                if ($subcategories) {
                    foreach ($subcategories as $child) {
                        if ($this->noIDs) {
                            if ($child->alias == $segment) {
                                return $child->id;
                            }
                        } else {
                            // We need to check full alias because ID can be same for Category and Item
                            $fullAlias = (int)$child->id . '-' . $child->alias;
                            if ($fullAlias == $segment) {
                                return $child->id;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    public function getCategoriesId($segment, $query) {
        return $this->getCategoryId($segment, $query);
    }

    public function getItemsId($segment, $query) {
        return $this->getCategoryId($segment, $query);
    }

    public function getItemId($segment, $query) {
        if ($this->noIDs) {
            if (isset($query['lang'])) {
                $lang = $query['lang'];
            } else {
                $lang = Factory::getApplication()->getLanguage()->getTag();
            }

            $db      = Factory::getDbo();
            $dbquery = $db->getQuery(true);
            $dbquery->select($db->quoteName('p.id'))
                ->from($db->quoteName('#__phocacart_products', 'p'));

            if (I18nHelper::isI18n()) {
                $dbquery
                    ->join('LEFT', $db->quoteName('#__phocacart_products_i18n', 'i18n_p'), 'i18n_p.id = p.id AND i18n_p.language = ' . $db->quote($lang))
                    ->where('coalesce(' . $db->quoteName('i18n_p.alias') . ', ' . $db->quoteName('p.alias') . ')' . ' = :alias');
            } else {
                $dbquery->where($db->quoteName('p.alias') . ' = :alias');
            }

            $dbquery->bind(':alias', $segment);

            if (Multilanguage::isEnabled()) {
                $dbquery
                    ->where([
                        $db->quoteName('p.language') . ' in (:language, ' . $db->quote('*') . ')'
                    ])
                    ->bind(':language', $lang);
            }

            $db->setQuery($dbquery);

            return (int)$db->loadResult();
        }

        return (int)$segment;
    }
}

function PhocaCartBuildRoute(&$query) {
    $app    = Factory::getApplication();
    $router = new PhocaCartRouter($app, $app->getMenu());
    return $router->build($query);
}

function PhocaCartParseRoute($segments) {
    $app    = Factory::getApplication();
    $router = new PhocaCartRouter($app, $app->getMenu());

    return $router->parse($segments);
}
