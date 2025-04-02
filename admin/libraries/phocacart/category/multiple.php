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
use Joomla\CMS\Language\Text;

require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/bootstrap.php');

class PhocacartCategoryMultiple
{
	public static function getCategories($productId, $select = 0) {

		$db = Factory::getDBO();

		if ($select == 1) {
			$query = 'SELECT c.category_id';
		} else if ($select == 2) {
			$query = 'SELECT a.id AS value, a.title AS text';
		} else if ($select == 3) {
			$query = 'SELECT a.id, a.title, a.alias';
		} else {
			$query = 'SELECT a.*';
		}
		$query .= ' FROM #__phocacart_categories AS a'
				.' LEFT JOIN #__phocacart_product_categories AS c ON a.id = c.category_id'
			    .' WHERE c.product_id = '.(int)$productId;
		$db->setQuery($query);

		if ($select == 1) {
			$tags = $db->loadColumn();
		} else {
			$tags = $db->loadObjectList();
		}

		return $tags;
	}

	public static function getCategoriesByIds($cids) {

		$db = Factory::getDBO();
        if ($cids != '') {//cids is string separated by comma

            $query = 'SELECT c.category_id FROM #__phocacart_categories AS a'
                //.' LEFT JOIN #__phocacart AS f ON f.id = r.item_id'
                . ' LEFT JOIN #__phocacart_product_categories AS c ON a.id = c.category_id'
                . ' WHERE c.product_id IN (' . $cids . ')'
                . ' ORDER BY a.id';

            $db->setQuery($query);
            $tags = $db->loadColumn();
            $tags = array_unique($tags);

            return $tags;
        }
        return array();
	}



	public static function getAllCategories($filter = 0, $type = array(0,1)) {

		$db 			= Factory::getDBO();
		$user			= PhocacartUser::getUser();
		$userLevels		= implode (',', $user->getAuthorisedViewLevels());
		$userGroups 	= implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));

		$wheres			= array();
		if ($filter == 1) {
			// POS FILTER
			$paramsC					= PhocacartUtils::getComponentParameters();
			$pos_categories	= $paramsC->get( 'pos_categories', array(-1) );

			if (in_array(-1, $pos_categories)) {
				// All categories selected
				$whereCat = '';
			} else if (in_array(0, $pos_categories)) {
				// No category selected
				return false;
			} else {
				// Only some selected
				$wheres[] = ' c.id IN ('.implode(',', $pos_categories).')';

			}
		}

		$wheres[] = " c.access IN (".$userLevels.")";
		$wheres[] = " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";
		$wheres[] = " c.published = 1";


		if (!empty($type) && is_array($type)) {
			$wheres[] = " c.type IN (".implode(',', $type).")";
		}


		$columns		= 'c.id, c.title, c.alias, c.parent_id';
		$groupsFull		= $columns;
		$groupsFast		= 'c.id';
		$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

		$query = 'SELECT c.id, c.title, c.alias'
		. ' FROM #__phocacart_categories AS c'
		. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
		. ' WHERE ' . implode( ' AND ', $wheres )
		. ' GROUP BY '.$groups;

		$db->setQuery($query);
		$categories = $db->loadObjectList();

		return $categories;
	}

	public static function deleteCategoriesFromProduct($categoryArray, $productId) {
		$db = Factory::getDBO();
		if (!empty($categoryArray)) {
			foreach($categoryArray as $k => $v) {
				$query = ' DELETE '
				.' FROM #__phocacart_product_categories'
				. ' WHERE product_id = '. (int)$productId
				. ' AND category_id = '. (int)$v;
				$db->setQuery($query);
				$db->execute();
			}
		}
		return true;
	}

	public static function storeCategories($storeArray, $productId, $categoryOrdering = array()) {


		if ((int)$productId > 0) {

			$db = Factory::getDBO();
			/*$query = ' DELETE '
					.' FROM #__phocacart_product_categories'
					. ' WHERE product_id = '. (int)$productId;
			$db->setQuery($query);
			$db->execute();*/

			// Select stored categories for this ID
			$query = 'SELECT a.category_id'
			.' FROM #__phocacart_product_categories AS a'
			.' WHERE a.product_id = '.(int)$productId.' ORDER BY a.product_id';
			$db->setQuery($query);
			$storedArray = $db->loadColumn();

			// ----------------------------------------------------------
			// Check if some category, or parent category is unpublished
            $storeString = implode(',', array_unique(array_map('intval', $storeArray)));


            $q = ' WITH RECURSIVE cte (id, title, parent_id, published) AS ('
                .'   SELECT id, title, parent_id, published FROM #__phocacart_categories'
                .'   WHERE id IN ('.$storeString.')'
                .'   UNION ALL SELECT p.id, p.title, p.parent_id, p.published'
                .'   FROM #__phocacart_categories AS p'
                .'   INNER JOIN cte ON p.id = cte.parent_id'
                .' )'
                .' SELECT * FROM cte WHERE published = 0;';

            try {
                $db->setQuery($q);
                $unpublishedCats = $db->loadAssocList('id');// with assoc list the categories will be unique
                if (!empty($unpublishedCats)) {
                    $count = count($unpublishedCats);
                    if ($count > 1) {
                        $msg = Text::_('COM_PHOCACART_BE_AWARE_FOLLOWING_SELECTED_CATEGORIES_OR_THEIR_PARENT_CATEGORIES_ARE_NOT_PUBLISHED') . ': ';
                    } else {
                        $msg = Text::_('COM_PHOCACART_BE_AWARE_FOLLOWING_SELECTED_CATEGORY_OR_ITS_PARENT_CATEGORY_IS_NOT_PUBLISHED'). ': ';
                    }
                    $msg .= '<ul>';
                    foreach ($unpublishedCats as $k => $v) {
                        $msg .= '<li><b>' . $v['title'] . '</b></li>';
                    }
                    $msg .= '</ul>';

                    Factory::getApplication()->enqueueMessage($msg, 'warning');
                }
            } catch (RuntimeException $e) {
                // No error, because this is just additional info
                //Factory::getApplication()->enqueueMessage('PROBABLY WITH RECURSIVE IS NOT SUPPORTED', 'warning');
            }
            // ----------------------------------------------------------


			$store 	= array_diff($storeArray,$storedArray);// we only store categories which are not stored yet by this product id
			$delete = array_diff($storedArray, $storeArray);// category is stored in db but we removed it in administration so it is
															// not more selected for this product and we need to remove it




			if (!empty($delete)) {
				foreach($delete as $k => $v) {
					$query = ' DELETE '
					.' FROM #__phocacart_product_categories'
					. ' WHERE product_id = '. (int)$productId
					. ' AND category_id = '. (int)$v;
					$db->setQuery($query);
					$db->execute();
				}
			}

			if (!empty($store)) {

				$values 		= array();
				$valuesString 	= '';

				$store		= array_unique($store);

				foreach($store as $k => $v) {
					$v = (int)$v;
					if (isset($categoryOrdering[$v]) && $categoryOrdering[$v] > 0) {
						// Import/Export function - we store the ordering
						// if for example all product items are exported to empty database
						// stay with stored ordering
						$o = $categoryOrdering[$v];
					} else {
						// New row added
						$o = self::getNextOrder((int)$productId, $v);
					}
					$values[] = ' ('.(int)$productId.', '.$v.', '.(int)$o.')';
				}

				if (!empty($values)) {
					$valuesString = implode(',', $values);
					$query = ' INSERT INTO #__phocacart_product_categories (product_id, category_id, ordering)'
								.' VALUES '.(string)$valuesString;
					$db->setQuery($query);
					$db->execute();
				}
			}
		}
	}

	public static function getNextOrder($pId, $cId) {
		$db 	= Factory::getDBO();
		$where	= 'category_id ='.(int)$cId;
		$query 	= $db->getQuery(true)
			->select('MAX(ordering)')
			->from('#__phocacart_product_categories')
			->where($where);


		$db->setQuery($query);
		$max = (int)$db->loadResult();

		return ($max + 1);
	}

	public static function getCategoriesByProducts($productsA) {

		$productsS = '';
		$categories = '';
		if (!empty($productsA)) {
			$productsS = implode(',', $productsA);
			$db 	= Factory::getDBO();
			$query = ' SELECT pc.product_id, c.id, c.alias, c.title FROM #__phocacart_categories AS c'
					.' LEFT JOIN #__phocacart_product_categories AS pc ON c.id = pc.category_id'
					.' WHERE pc.product_id IN ('.$productsS.')';
			$db->setQuery($query);
			$categories = $db->loadAssocList();
		}
		return $categories;
	}

	public static function getAllCategoriesByProduct($productId) {
		$db = Factory::getDBO();
		// Select stored categories for this ID
		$query = 'SELECT a.category_id'
		.' FROM #__phocacart_product_categories AS a'
		.' WHERE a.product_id = '.(int)$productId.' ORDER BY a.product_id';
		$db->setQuery($query);
		$storedArray = $db->loadColumn();
		return $storedArray;
	}


	/*
	*	index.php?option=com_phocacart&task=phocacartitem.removeduplicates
	*
	public static function removeDuplicates() {

		$db = Factory::getDBO();

		$query = ' ALTER IGNORE TABLE '
		.' #__phocacart_product_categories'
		. ' ADD UNIQUE INDEX idx_category (product_id, category_id);';
		$db->setQuery($query);
		$db->execute();


		return true;
	} */

	/*
	* Try to find best category of the produt to build SEF
	* (e.g. if we are in category 5 and product is included in category 5, select this category)
	* We can get it per sql with help of group_concat
	*/

	public static function setCurrentCategory($items) {

		$app	= Factory::getApplication();
		$catid	= $app->input->get('catid', 0, 'int');

		if (!empty($items) && (int)$catid > 0) {
			foreach ($items as $k => $v) {
				if ($v->categories != '') {
					$c = explode(',', $v->categories);
					if (!empty($c)) {
						foreach($c as $k2 => $v2) {
							$c2 = explode('|', $v2);
							if (isset($c2[0]) && (int)$c2[0] == (int)$catid) {

								$items[$k]->catid 		= $c2[0];
								$items[$k]->catalias	= '';
								if (isset($c2[1])) {
									$items[$k]->catalias 	= $c2[1];
								}
								$items[$k]->cattitle	= '';
								break;
							}
						}
					}
				}
			}
		}
		return $items;
	}

	public static function getCategoryByProduct($id, $catid) {

		$db 	= Factory::getDBO();
		$query 	=
			 ' SELECT c.id AS catid, c.title AS cattitle, c.alias AS catalias'
			.' FROM #__phocacart_categories AS c'
			.' LEFT JOIN #__phocacart_product_categories AS pc ON c.id = pc.category_id'
			.' WHERE pc.product_id = '.(int)$id.' AND c.id = '.(int)$catid
			.' ORDER BY c.id'
			.' LIMIT 1';
		$db->setQuery($query);
		$categories = $db->loadAssoc();
		return $categories;
	}

	public static function setBestMatchCategory(&$items, $categories, $object = 0) {


		if (!empty($items)) {

			if ($object) {
				foreach ($items as $k => &$v) {

					if (isset($v->count_categories) && (int)$v->count_categories > 1) {



                        // preferred catid set
                        if (isset($v->preferred_catid) && (int)$v->preferred_catid > 0) {
                            $catidA	        = explode(',', $v->catid);
                            $cattitleA	    = explode(',', $v->cattitle);
                            $cataliasA	    = explode(',', $v->catalias);
                            $key            = array_search((int)$v->preferred_catid, $catidA);
                            $v->catid	    = $catidA[$key];
                            $v->cattitle 	= $cattitleA[$key];
                            $v->catalias 	= $cataliasA[$key];
                            continue;
                        }

                        $catid	= explode(',', $v->catid);
						$id 	= (int)$v->id;
						$catid	= (int)$catid[0];

						if (isset($categories[$id]['catid']) && isset($catid) && (int)$categories[$id]['catid'] == $catid){
							continue;

						}
						// Try to find better category
						if (isset($categories[$id]['catid']) && isset($id)){
							$newItems = self::getCategoryByProduct($id, $categories[$id]['catid']);
							if (isset($newItems['catid']) && isset($newItems['cattitle']) && isset($newItems['catalias'])) {


								$v->catid 		= $newItems['catid'];
								$v->cattitle 	= $newItems['cattitle'];
								$v->catalias 	= $newItems['catalias'];
							}
						}
					}
				}

			} else {
				foreach ($items as $k => &$v) {
					if (isset($v['count_categories']) && (int)$v['count_categories'] > 1) {

                        // preferred catid set
                        if (isset($v['preferred_catid']) && (int)$v['preferred_catid'] > 0) {
                            $catidA	    = explode(',', $v['catid']);
                            $cattitleA	= explode(',', $v['cattitle']);
                            $cataliasA	= explode(',', $v['catalias']);
                            $key = array_search((int)$v['preferred_catid'], $catidA);
                            $v['catid'] 	= $catidA[$key];
                            $v['cattitle'] 	= $cattitleA[$key];
                            $v['catalias'] 	= $cataliasA[$key];
                            continue;
                        }

                        $id 	= (int)$v['id'];
						$catid	= (int)$v['catid'];

						if (isset($categories[$id]['catid']) && isset($catid) && (int)$categories[$id]['catid'] == $catid){
							continue;

						}
						// Try to find better category
						if (isset($categories[$id]['catid']) && isset($id)){
							$newItems = self::getCategoryByProduct($id, $categories[$id]['catid']);
							if (isset($newItems['catid']) && isset($newItems['cattitle']) && isset($newItems['catalias'])) {
								$v['catid'] 	= $newItems['catid'];
								$v['cattitle'] 	= $newItems['cattitle'];
								$v['catalias'] 	= $newItems['catalias'];
							}
						}
					}
				}
			}
		}

		return $items;
	}

	public static function getCurrentCategoryId() {

		$app	= Factory::getApplication();
		$id 	= $app->input->get('id', 0, 'int');
		$catid 	= $app->input->get('catid', 0, 'int');
		$view	= $app->input->get('view', '', 'string');
		$option	= $app->input->get('option', '', 'string');

		if ($option == 'com_phocacart' && $view == 'category') {
			return $id;
		} else if ($option == 'com_phocacart' && $view == 'item') {
			return $catid;
		}
		return 0;
	}


  public static function getCategoryChildrenString($id, $children = '') {

    $categories = PhocacartCategory::getChildren($id);
    if ($categories) {
      foreach ($categories as $v) {
        if ($children != '') {
          $children .= ',';
        }

        $children .= $v->id;
        $children = self::getCategoryChildrenString($v->id, $children);
      }
    }

    return $children;
  }

  public static function getCategoryChildrenArray($id) {
    $categories = PhocacartCategory::getChildren($id);
    $children = [];

    if ($categories) {
      foreach ($categories as $v) {
        $children[$v->id] = self::getCategoryChildrenArray($v->id);
      }
    }
    return $children;
  }
}
