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

if (! class_exists('PhocaCartLoader')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/loader.php');
}

class PhocacartRouter extends RouterView
{
	protected $noIDs = false;

  public function __construct($app = null, $menu = null)
  {
    $viewsNoId 		= array('checkout', 'comparison', 'download', 'terms', 'account', 'orders', 'payment', 'info', 'wishlist', 'pos', 'submit');
    $viewsId		= array('feed');
    $viewsNotOwnId	= array('question');

    $params = ComponentHelper::getParams('com_phocacart');;
    $this->noIDs = (bool)$params->get('remove_sef_ids');


    // Is the main menu type categories view or items view - does categories view exist?
    $component 		    = ComponentHelper::getComponent('com_phocacart');
    $attributes 	    = array('component_id');
    $values     	    = array($component->id);
    $items              = $menu->getItems($attributes, $values);
    $isCategoriesView   = false;
    $isItemsView        = false;
    if (!empty($items)) {
      foreach($items as $k => $v) {
        if(isset($v->query['view']) && $v->query['view'] == 'categories') {
          $isCategoriesView = true;
        } else if(isset($v->query['view']) && $v->query['view'] == 'items') {
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


    /*	$categoriesQ = new RouterViewConfiguration('categoriesq');
      $categoriesQ->setKey('id');
      $this->registerView($categoriesQ);

      $categoryQ = new RouterViewConfiguration('categoryq');
      $categoryQ->setKey('id')->setParent($categories, 'parent_id')->setNestable();
      $this->registerView($categoryQ);*/




    //$itemq = new RouterViewConfiguration('question');
    //$itemq->setKey('qid')->setParent($category, 'catid');//->setNestable();
    //$this->registerView($itemq);


    //$q = new RouterViewConfiguration('question2');
    //$this->registerView($q);

    //$question = new RouterViewConfiguration('question');
    //$question->setKey('id')->setParent($q)->setParent($category, 'catid');//->setNestable();
    //$question->setName('question');
    //$this->registerView($question);

    /*		$items = new RouterViewConfiguration('items');
        $items->setParent($categories, 'parent_id');
        $items->setName('items');
        $this->registerView($items);
    */
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

    /* foreach ($viewsNotOwnId as $k => $v) {
        $it = new RouterViewConfiguration($v);

     //$it->setParent($categories);
     //$it->setParent($category, 'catid');
         //$it->setParent($item, 'id');
     $it->setName($v);
         //$it->setKey('id');
         //$it->setKey('qid');

         $this->registerView($it);
     }*/

    parent::__construct($app, $menu);

    phocacartimport('phocacart.path.routerrules');
    phocacartimport('phocacart.category.category');

    $this->attachRule(new MenuRules($this));
    $this->attachRule(new PhocaCartRouterrules($this));
    $this->attachRule(new StandardRules($this));
    $this->attachRule(new NomenuRules($this));
  }


	/*public function getCategoriesqSegment($id, $query) {
		return $this->getCategorySegment($id, $query);
	}
	public function getCategoryqSegment($id, $query) {
		return $this->getCategorySegment($id, $query);
	}*/

  public function getCategoriesSegment($id, $query)
  {
    return $this->getCategorySegment($id, $query);
  }

	public function getItemsSegment($id, $query) {
		return $this->getCategorySegment($id, $query);
	}

  public function getCategorySegment($id, $query)
  {
    $category = PhocaCartCategory::getCategoryById($id);

    if ($category) {
      $path = PhocaCartCategory::getPathRouter(array(), (int)$category->id, $category->parent_id, $category->title, $category->alias);

      //$path = array_reverse($path, true);
      //$path = array_reverse($category->getPath(), true);
      $path[0] = '1:root';// we don't use root but it is needed when building urls with joomla methods

      if ($this->noIDs) {
        foreach ($path as &$segment){
          list($id, $segment) = explode(':', $segment, 2);
        }
      }
      return $path;
    }

    return [];
  }

	public function getItemSegment($id, $query) {
    if (!strpos($id, ':')) {
			$db = Factory::getDbo();
			$dbquery = $db->getQuery(true);
			$dbquery->select($dbquery->qn('alias'))
				->from($dbquery->qn('#__phocacart_products'))
				->where('id = ' . $dbquery->q($id));
			$db->setQuery($dbquery);
			$id .= ':' . $db->loadResult();
		}

		if ($this->noIDs) {
			list($void, $segment) = explode(':', $id, 2);
			return array($void => $segment);
		}

		return array((int) $id => $id);
	}

	public function getQuestionSegment($id, $query) {

        // Specidific case
        // catid - category id
        // productid - product id
        // id - question id ( will be always 'question')
        return array((int) $id => 'question');

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
        }


	    if ($this->noIDs)  {
	        $db = Factory::getDbo();
			$dbquery = $db->getQuery(true);
			$dbquery->select($db->quoteName('id'))
				->from($db->quoteName('#__phocacart_categories'))
				->where([
						$db->quoteName('alias') . ' = :alias',
						$db->quoteName('parent_id') . ' = :parent_id',
        ])
				->bind(':alias', $segment)
				->bind(':parent_id', $query['id'], ParameterType::INTEGER);
      if (Multilanguage::isEnabled()) {
        if (isset($query['lang'])) {
          $lang = $query['lang'];
        } else {
          $lang = Factory::getApplication()->getLanguage()->getTag();
        }

        $dbquery
          ->where([
            $db->quoteName('language') . ' in (:language, ' . $db->quote('*') . ')'
          ])
          ->bind(':language', $lang);
      }

			$db->setQuery($dbquery);
			return (int) $db->loadResult();
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
                $fullAlias = (int)$child->id . '-'.$child->alias;
                if ($fullAlias == $segment) {
                  return $child->id;
                }
              }
            }
          }
			}
		} else {

            // --- under test
            // We don't have query ID because of e.g. language
            // Should not happen because of modifications in build function here: administrator/components/com_phocacart/libraries/phocacart/path/routerrules.php
            /*if ((int)$segment > 0) {
		        $category = PhocaCartCategory::getCategoryById((int)$segment);
                if (isset($category->id) && (int)$category->id > 0 && $category->parent_id == 0) {
                    // We don't have root category with 0 so we need to start with segment one
                    return (int)$category->id;
                }
            }*/
            // under test
        }

		return false;
	}

	public function getCategoriesId($segment, $query) {
		return $this->getCategoryId($segment, $query);
	}

	public function getItemsId($segment, $query) {
		return $this->getCategoryId($segment, $query);
	}

	public function getItemId($segment, $query)
	{

		if ($this->noIDs) {
			$db = Factory::getDbo();
      $dbquery = $db->getQuery(true);
      $dbquery->select($db->quoteName('id'))
        ->from($db->quoteName('#__phocacart_products'))
        ->where([
          $db->quoteName('alias') . ' = :alias',
        ])
        ->bind(':alias', $segment);
      if (Multilanguage::isEnabled()) {
        if (isset($query['lang'])) {
          $lang = $query['lang'];
        } else {
          $lang = Factory::getApplication()->getLanguage()->getTag();
        }

        $dbquery
          ->where([
            $db->quoteName('language') . ' in (:language, ' . $db->quote('*') . ')'
          ])
          ->bind(':language', $lang);
      }

      $db->setQuery($dbquery);
			return (int) $db->loadResult();
		}

		return (int) $segment;
	}


	public function parse(&$segments){
		return parent::parse($segments);
	}

    public function build(&$query) {

		return parent::build($query);
	}
}

function PhocaCartBuildRoute(&$query) {

	$app = Factory::getApplication();
	$router = new PhocaCartRouter($app, $app->getMenu());
	return $router->build($query);
}

function PhocaCartParseRoute($segments) {

	$app = Factory::getApplication();
	$router = new PhocaCartRouter($app, $app->getMenu());


	return $router->parse($segments);
}
