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
use Phoca\PhocaCart\I18n\I18nHelper;

class PhocacartCompare
{
	protected $items     		= array();// compare items
	protected $itemsDb			= array();// real products (real products are stored in compare items but can differ, e.g. if product will be unpublished)

	public function __construct() {
		$session 		= Factory::getSession();
		$app 			= Factory::getApplication();
		$db				= Factory::getDbo();
		$this->items	= $session->get('compare', array(), 'phocaCart');

		// Recheck if we have access to all products:
		// This is different to wishlist, comparison is set only in session
		// so not needed to check database
		// even worse if checked database, then $items->itemDb is not empty and
		// this will not allow us to use updated list after addItem or removeItem functions
		// as both only update session
		// So when using AJAX and adding item to comparison list, such will be wrongly rendered
		// in renderList function because it will not get items from session but db
		/*$query				    = $this->getQueryList($this->items);

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
		}*/
	}

	public function addItem($id = 0, $catid = 0) {
		if ($id > 0) {
			$app 			= Factory::getApplication();

			$count = count($this->items);

			if ($count > 2) {
				$message = Text::_('COM_PHOCACART_ONLY_THREE_PRODUCTS_CAN_BE_LISTED_IN_COMPARISON_LIST');
				$app->enqueueMessage($message, 'error');
				return false;
			}

			if(isset($this->items[$id]) && (int)$this->items[$id] > 0) {

				$message = Text::_('COM_PHOCACART_PRODUCT_INCLUDED_IN_COMPARISON_LIST');
				$app->enqueueMessage($message, 'error');
				return false;
			} else {
				$this->items[$id]['id'] = $id;
				$this->items[$id]['catid'] = $catid;
				$session 		= Factory::getSession();
				$session->set('compare', $this->items, 'phocaCart');
			}
			return true;
		}
		return false;
	}

	public function removeItem($id = 0) {
		if ($id > 0) {
			if(isset($this->items[$id]) && (int)$this->items[$id] > 0) {
				unset($this->items[$id]);
				$session 		= Factory::getSession();
				$session->set('compare', $this->items, 'phocaCart');
				return true;
			} else {
				return false;
			}
			return false;
		}
		return false;
	}

	public function emptyCompare() {
		$session 		= Factory::getSession();
		$session->set('compare', array(), 'phocaCart');
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
		$wheres[] = " a.access IN (".$userLevels.")";
		$wheres[] = " (ga.group_id IN (".$userGroups.") OR ga.group_id IS NULL)";
		$wheres[] = " (gc.group_id IN (".$userGroups.") OR gc.group_id IS NULL)";
		$wheres[] = " c.published = 1";
		$wheres[] = " a.published = 1";
		$wheres[] = " c.type IN (0,1)";// compare only works in online shop (0 - all, 1 - online shop, 2 - pos)

		$where 		= ( count( $wheres ) ? ' WHERE '. implode( ' AND ', $wheres ) : '' );

		if ($full == 1) {
			$columns = I18nHelper::sqlCoalesce(['title', 'alias', 'description']);
			$columns .= ', a.id as id, a.price, a.image, a.type,'
			.' GROUP_CONCAT(DISTINCT c.id) as catid, COUNT(pc.category_id) AS count_categories, a.catid AS preferred_catid,'
			.' a.length, a.width, a.height, a.weight, a.volume, a.unit_amount, a.unit_unit, a.price_original,'
			.' a.stock, a.min_quantity, a.min_multiple_quantity, a.stockstatus_a_id, a.stockstatus_n_id, a.availability,'
			.' a.gift_types,'
			//.' m.title as manufacturer_title,'
			. I18nHelper::sqlCoalesce(['title'], 'm', 'manufacturer_', '', '', ',')
			.' t.id as taxid, t.tax_rate as taxrate,'
			. I18nHelper::sqlCoalesce(['title'], 't', 'tax', '', '', ',')
			.'t.calculation_type as taxcalculationtype, t.tax_hide as taxhide,'
			.' MIN(ppg.price) as group_price, MAX(pptg.points_received) as group_points_received,';
			$columns .= I18nHelper::sqlCoalesce(['title', 'alias'], 'c', 'cat', 'groupconcatdistinct');

			$groupsFull		= 'a.id, a.title, a.alias, a.description, a.price, a.image, a.type,'
			.' a.length, a.width, a.height, a.weight, a.volume,'
			.' a.stock, a.min_quantity, a.min_multiple_quantity, a.stockstatus_a_id, a.stockstatus_n_id, a.availability,'
			.' a.gift_types,'
			.' m.title,'
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
			.' LEFT JOIN #__phocacart_taxes AS t ON t.id = a.tax_id'
			 . I18nHelper::sqlJoin('#__phocacart_taxes_i18n', 't')
			.' LEFT JOIN #__phocacart_manufacturers AS m ON a.manufacturer_id = m.id'
		 	. I18nHelper::sqlJoin('#__phocacart_manufacturers_i18n', 'm')
			.' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
			.' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category

			// user is in more groups, select lowest price by best group
			. ' LEFT JOIN #__phocacart_product_price_groups AS ppg ON a.id = ppg.product_id AND ppg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN ('.$userGroups.') AND type = 3)'
			// user is in more groups, select highest points by best group
			. ' LEFT JOIN #__phocacart_product_point_groups AS pptg ON a.id = pptg.product_id AND pptg.group_id IN (SELECT group_id FROM #__phocacart_item_groups WHERE item_id = a.id AND group_id IN ('.$userGroups.') AND type = 3)'

			.  $where
			. ' GROUP BY '.$groups
			. ' ORDER BY a.id';
		} else {

			$columns = I18nHelper::sqlCoalesce(['title', 'alias', 'description']);

			$columns		.= ', a.id as id,'
			.' GROUP_CONCAT(DISTINCT c.id) as catid, COUNT(pc.category_id) AS count_categories, a.catid AS preferred_catid, ';
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
			.' LEFT JOIN #__phocacart_item_groups AS ga ON a.id = ga.item_id AND ga.type = 3'// type 3 is product
			.' LEFT JOIN #__phocacart_item_groups AS gc ON c.id = gc.item_id AND gc.type = 2'// type 2 is category
			.  $where
			.' GROUP BY '.$groups
			.' ORDER BY a.id';
		}

		return $query;
	}

	public function getItemsIdString($items) {

		$itemsR = '';
		if (!empty($items)) {
			$itemsA = array();
			foreach($items as $k => $v) {
				if (isset($v['id']) && (int)$v['id'] > 0) {
					$itemsA[] = $v['id'];
				}
			}
			$itemsR = implode (',', $itemsA);
		}

		if ($itemsR == '') {
			return false;
		}
		return $itemsR;
	}

	public function renderList() {

		$db 				= Factory::getDBO();
		$uri 				= Uri::getInstance();
		$action				= $uri->toString();
		$app				= Factory::getApplication();
		$s                  = PhocacartRenderStyle::getStyles();
		$paramsC 			= PhocacartUtils::getComponentParameters();
		$add_compare_method	= $paramsC->get( 'add_compare_method', 0 );

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
			$d['compare'] 			= $this->itemsDb;
			PhocacartCategoryMultiple::setBestMatchCategory($d['compare'] , $this->items, 1);// returned by reference
		}
		$d['actionbase64']		= base64_encode($action);
		$d['linkcomparison']	= Route::_(PhocacartRoute::getComparisonRoute());
		$d['method']			= $add_compare_method;

		$layoutC 			= new FileLayout('list_compare', null, array('component' => 'com_phocacart'));
		echo $layoutC->render($d);
	}

	public function getFullItems() {

		$db 		= Factory::getDBO();
		$query		= $this->getQueryList($this->items, 1);

		$products	= array();
		if ($query) {
			$db->setQuery($query);
			$products = $db->loadAssocList();

			PhocacartCategoryMultiple::setBestMatchCategory($products, $this->items);// returned by reference
		}
		return $products;

	}

	/* Deprecated method - typo will be removed*/
	public function getComapareCountItems() {
		return count($this->items);
	}

	public function getCompareCountItems() {
		return count($this->items);
	}
}
?>
