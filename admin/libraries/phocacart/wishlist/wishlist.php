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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Database\DatabaseInterface;
use Phoca\PhocaCart\Constants\WishListType;
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocacartWishlist
{
	protected $items     		= array();// wishlist items
	protected $itemsDb			= array();// real products (real products are stored in wish list items but can differ, e.g. if product will be unpublished)
	protected $user				= array();

	public function __construct() {
		$session 		= Factory::getSession();
		$this->user		= PhocacartUser::getUser();
		$app 			= Factory::getApplication();
		$db				= Factory::getDbo();


		if((int)$this->user->id > 0) {
			// DATABASE - logged in user - Singleton because of not load data from database every time wishlist instance is loaded
			// 1. Not found in DATABASE - maybe user logged in now, so:
			// 2. We try to find the data in SESSION, if they are still in SESSION - load them to our wishlist class and then
			// 3. Store them to DATABASE as all loged in users have wishlist in database and:
			// 4. Remove them from SESSION as they are stored in DATABASE
			$wLDb = $this->getWishListItemdDb();// user logged in - try to get wishlist from db
			$this->items 		= $wLDb;

			// Recheck if we have access to all products:
			$query				    = $this->getQueryList($this->items);

			if ($query) {
				//echo nl2br(str_replace('#__', 'jos_', $query));
				$db->setQuery($query);
				$this->itemsDb = $db->loadObjectList();
				$tempItems = array();
				if (!empty($this->itemsDb)){
					foreach ($this->itemsDb as $k => $v) {
						$id = (int)$v->id;
						if (isset($this->items[$id])) {
							$tempItems[$id] = $this->items[$id];
						}
					}
				}
				$this->items = $tempItems;
			}

			if(empty($this->items)) {
				$this->items	= $session->get('wishlist', array(), 'phocaCart');
				if(!empty($this->items)) {
					$this->updateWishListItems();
					$session->set('wishlist', array(), 'phocaCart');
				}
			}
		} else {
			$this->items	= $session->get('wishlist', array(), 'phocaCart');

		}
	}

	public function getWishListItemdDb() {
		if ($this->user->id > 0) {
			$db = Factory::getDBO();

			$query = 'SELECT a.product_id, a.category_id, a.user_id'
					.' FROM #__phocacart_wishlists AS a'
					.' WHERE a.user_id = '.(int)$this->user->id
					.' AND a.type = ' . WishListType::WishList
					.' ORDER BY a.id';
			$db->setQuery($query);
			$items = $db->loadAssocList();
			$itemsSorted = array();
			if (!empty($items)) {

				foreach($items as $k => $v) {
					$itemsSorted[$v['product_id']]['product_id'] 	= $v['product_id'];
					$itemsSorted[$v['product_id']]['category_id'] 	= $v['category_id'];
					$itemsSorted[$v['product_id']]['user_id'] 		= $v['user_id'];
				}

				if (!empty($itemsSorted)) {
					return $itemsSorted;
				}
				return false;
			}
		}
		return false;
	}

	public function updateWishListItems() {
		if ($this->user->id > 0) {
			$db 	= Factory::getDBO();
			//$items	= se rialize($this->items);
			$date 	= Factory::getDate();
			$now	= $date->toSql();


			if (!empty($this->items)) {

				$q = '';
				// Unfortunately we need to run SQL query more times in foreach as
				// ON DUPLICATE KEY UPDATE can work with one SQL uqery INSERT INTO ... VALUES (a,b,c), (a2,b2,c2)
				// but it does not work with multiple columns product_id x category_id x user_id in combination
				foreach($this->items as $k => $v) {
					if (isset($v['product_id']) && isset($v['category_id']) && (int)$v['product_id'] > 0 && (int)$v['category_id'] > 0) {
						$q = ' INSERT INTO #__phocacart_wishlists (product_id, category_id, user_id, date, type)';
						$q .= ' VALUES ('.(int)$v['product_id'].', '.(int)$v['category_id'].', '.(int)$this->user->id.',  '.$db->quote($now). ', ' . WishListType::WishList . ')';
						$q .= ' ON DUPLICATE KEY UPDATE  product_id = VALUES(product_id), category_id = VALUES(category_id), user_id = VALUES(user_id), date = VALUES(date);';
					}
				}
				if ($q != '') {
					$db->setQuery($q);
					$db->execute();
				}
				return true;
			}
			return false;
		} else {

			$session 		= Factory::getSession();
			$session->set('wishlist', $this->items, 'phocaCart');
		}
		return false;
	}

	public function addItem($id = 0, $catid = 0) {
		if ($id > 0) {

			$app			= Factory::getApplication();
			$paramsC 		= PhocacartUtils::getComponentParameters();
			$maxWishListItems	= $paramsC->get( 'max_wishlist_items', 20 );

			$count = count($this->items);

			if ($count > (int)$maxWishListItems || $count == (int)$maxWishListItems) {
				$message = Text::_('COM_PHOCACART_COUNT_OF_PRODUCTS_IN_WISH_LIST_IS_LIMITED');
				$app->enqueueMessage($message, 'error');
				return false;
			}

			if(isset($this->items[$id]) && (int)$this->items[$id] > 0) {

				$message = Text::_('COM_PHOCACART_PRODUCT_INCLUDED_IN_WISH_LIST');
				$app->enqueueMessage($message, 'error');
				return false;
			} else {

				$this->items[$id]['product_id'] 	= $id;
				$this->items[$id]['category_id'] 	= $catid;
				if ($this->user->id > 0) {
					$this->items[$id]['user_id'] 	= $this->user->id;
				}
				$this->updateWishListItems();
				return true;
			}
		}
		return false;
	}

	public function removeItem($id = 0) {
		if ($id > 0) {
			if(isset($this->items[$id]) && (int)$this->items[$id] > 0) {
				if ($this->user->id > 0) {
					if (isset($this->items[$id]['product_id']) && isset($this->items[$id]['category_id'])) {

						$db 	= Factory::getDBO();
						$query = ' DELETE '
						.' FROM #__phocacart_wishlists'
						.' WHERE product_id = '.(int)$this->items[$id]['product_id']
						.' AND category_id =  '.(int)$this->items[$id]['category_id']
						.' AND user_id =  '.(int)$this->user->id
						.' AND type =  '. WishListType::WishList;

						unset($this->items[$id]);// Because of ajax

						$db->setQuery($query);
						$db->execute();
						return true;
					} else {
						return false;
					}

				} else {
					unset($this->items[$id]);
					$session 		= Factory::getSession();
					$session->set('wishlist', $this->items, 'phocaCart');
					return true;
				}

			} else {
				return false;
			}
			return false;
		}
		return false;
	}

	public function emptyWishList() {
		$session 		= Factory::getSession();
		$session->set('wishlist', array(), 'phocaCart');
	}

	public function getItems() {
		return $this->items;
	}


	public function getQueryList($items, $full = 0){

		$user 		= PhocacartUser::getUser();
		$userLevels	= implode (',', $user->getAuthorisedViewLevels());
		$userGroups = implode (',', PhocacartGroup::getGroupsById($user->id, 1, 1));
		$itemsS		= $this->getItemsIdString($items);


		if ($itemsS == '') {
			return false;
		}

		$wheres[] = 'a.id IN ('.(string)$itemsS.')';
		$wheres[] = " c.access IN (".$userLevels.")";
		$wheres[] = " c.type IN (0,1)";// type: common, onlineshop, pos - not used outside online shop
		$wheres[] = " a.access IN (".$userLevels.")";
		$wheres[] = " (ga.group_id IN (".$userGroups.") OR ga.group_id IS NULL)";
		$wheres[] = " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";
		$wheres[] = " c.published = 1";
		$wheres[] = " a.published = 1";

		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );

		if ($full == 1) {

            $columns = I18nHelper::sqlCoalesce(['title', 'alias', 'description']);
            $columns .= ', a.id as id,'
                       .' GROUP_CONCAT(DISTINCT c.id) as catid,'
                       .' COUNT(pc.category_id) AS count_categories,'
                       .' a.catid AS preferred_catid,';

            $columns .= I18nHelper::sqlCoalesce(['title', 'alias'], 'c', 'cat', 'groupconcatdistinct');
			$columns .= ', a.price, a.image,'
			            .' a.stock, a.min_quantity, a.min_multiple_quantity, a.stockstatus_a_id, a.stockstatus_n_id, a.availability,'
			            .' MIN(ppg.price) as group_price, MAX(pptg.points_received) as group_points_received';

            $groupsFull		= 'a.id, a.title, a.alias, a.description, a.price, a.image,'
			                .' a.stock, a.min_quantity, a.min_multiple_quantity, a.stockstatus_a_id, a.stockstatus_n_id, a.availability,'
			                .' ppg.price, pptg.points_received';
			$groupsFast		= 'a.id';
			$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;

			$query =
			 ' SELECT '.$columns
			.' FROM #__phocacart_products AS a'
			 . I18nHelper::sqlJoin('#__phocacart_products_i18n')
			.' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id'
			.' LEFT JOIN #__phocacart_categories AS c ON c.id =  pc.category_id'
             . I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'c')
			//.' LEFT JOIN #__phocacart_manufacturers AS m ON a.manufacturer_id = m.id'
			. ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
			. ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
			// user is in more groups, select lowest price by best group
			. ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON a.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN ('.$userGroups.') AND type = 3)'
			// user is in more groups, select highest points by best group
			. ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON a.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN ('.$userGroups.') AND type = 3)'

			.  $where
			. ' GROUP BY '.$groups
			. ' ORDER BY a.id';
		} else {

            $columns = I18nHelper::sqlCoalesce(['title', 'alias', 'description']);
            $columns .= ', a.id as id,'
                       .' GROUP_CONCAT(DISTINCT c.id) as catid,'
                       .' COUNT(pc.category_id) AS count_categories,'
                       .' a.catid AS preferred_catid,';
            $columns .= I18nHelper::sqlCoalesce(['title', 'alias'], 'c', 'cat', 'groupconcatdistinct');

            $groupsFull		= 'a.id, a.title, a.alias, a.catid';
			$groupsFast		= 'a.id';
			$groups			= PhocacartUtilsSettings::isFullGroupBy() ? $groupsFull : $groupsFast;


			$query =
			 ' SELECT '.$columns
			 .' FROM #__phocacart_products AS a'
             . I18nHelper::sqlJoin('#__phocacart_products_i18n')
			 .' LEFT JOIN #__phocacart_product_categories AS pc ON pc.product_id =  a.id'
             .' LEFT JOIN #__phocacart_categories AS c ON c.id =  pc.category_id'
             . I18nHelper::sqlJoin('#__phocacart_categories_i18n', 'c')
             . ' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
			 . ' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
			 .  $where
			 . ' GROUP BY '.$groups
			 . ' ORDER BY a.id';
		}
		return $query;
	}

	public function getItemsIdString($items) {

		$itemsR = '';
		if (!empty($items)) {
			$itemsA = array();
			foreach($items as $k => $v) {
				if (isset($v['product_id']) && (int)$v['product_id'] > 0) {
					$itemsA[] = $v['product_id'];
				}
			}
			$itemsR = implode (',', $itemsA);
		}

		if ($itemsR == '') {
			return false;
		}

		return $itemsR;
	}

	public function renderList()
	{
		/** @var DatabaseInterface $db */
		$db 				    = Factory::getContainer()->get(DatabaseInterface::class);
		$uri 				    = Uri::getInstance();
		$action			        = $uri->toString();
		$s                      = PhocacartRenderStyle::getStyles();
		$paramsC 		        = PhocacartUtils::getComponentParameters();
		$add_wishlist_method	= $paramsC->get( 'add_wishlist_method', 0 );


		if (empty($this->itemsDb)) {
			// we asked them in construct, don't ask again
			$query				= $this->getQueryList($this->items);
			if ($query) {
				$db->setQuery($query);
				$this->itemsDb = $db->loadObjectList();
			}
		}
		$d					= array();
		$d['s']			    = $s;
		if (!empty($this->itemsDb)) {
			$d['wishlist'] 	= $this->itemsDb;
			$tempItems 		= $this->correctItems();
			PhocacartCategoryMultiple::setBestMatchCategory($d['wishlist'], $tempItems, 1);// returned by reference
		}
		$d['actionbase64']		= base64_encode($action);
		$d['linkwishlist']		= Route::_(PhocacartRoute::getWishListRoute());
		$d['method']			= $add_wishlist_method;

		$layoutW 			= new FileLayout('list_wishlist', null, array('component' => 'com_phocacart'));
		echo $layoutW->render($d);
	}

	public function getFullItems() {

		$db 		= Factory::getDBO();
		$query		= $this->getQueryList($this->items, 1);

		$products	= array();
		if ($query) {
			$db->setQuery($query);
			$products = $db->loadAssocList();


			$tempItems = array();
			if (!empty($this->items)) {
				foreach($this->items as $k => $v) {
					$tempItems[$k]['id'] 		= $v['product_id'];
					$tempItems[$k]['catid'] 	= $v['category_id'];
				}
			}
			$tempItems = $this->correctItems();
			PhocacartCategoryMultiple::setBestMatchCategory($products, $tempItems);// returned by reference
		}
		return $products;

	}

	// Correct $this->items array for finding the right category
	public function correctItems() {
		$tempItems = array();
		if (!empty($this->items)) {
			foreach($this->items as $k => $v) {
				$tempItems[$k]['id'] 		= $v['product_id'];
				$tempItems[$k]['catid'] 	= $v['category_id'];
			}
		}
		return $tempItems;
	}

	public function getWishListCountItems() {
		return count($this->items);
	}
}
