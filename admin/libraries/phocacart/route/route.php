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
defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Phoca\PhocaCart\I18n\I18nHelper;

jimport('joomla.application.component.helper');


class PhocacartRoute
{
	public static function getCategoriesRoute($lang = [])
	{
		$app 		= Factory::getApplication();
		$menu 		= $app->getMenu();
		$active 	= $menu->getActive();
		$option		= $app->input->get( 'option', '', 'string' );
		$view		= $app->input->get( 'view', '', 'string' );

		$activeId 	= 0;
		if (isset($active->id)){
			$activeId    = $active->id;
		}

		$itemId 	= 0;
		if ((int)$activeId > 0 &&$option == 'com_phocacart' && $view == 'category') {
			// 2) if there are two menu links, try to select the one active
			$itemId = $activeId;
		}

		$needles = array(
			'categories' => ''
		);

		//Create the link
		$link = 'index.php?option=com_phocacart&view=categories';

		if($item = PhocacartRoute::_findItem($needles, 1, $lang)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			// $item->id should be a "categories view" and it should have preference to category view
			// so first we check item->id then itemId
			if (isset($item->id) && ((int)$item->id > 0)) {
				$link .= '&Itemid='.$item->id;
			} else if ((int)$itemId > 0) {
				$link .= '&Itemid='.(int)$itemId;
            }

            if (Multilanguage::isEnabled() || I18nHelper::useI18n()) {
                if (!empty($lang) && isset($lang[0]) && $lang[0] != '' && $lang[0] != '*'){
                    $link .= '&lang='.$lang[0];
                }
            }
		}

		return $link;
	}

	public static function cleanUrlItemsView ($url)
	{
		if (Factory::getApplication()->getConfig()->get('sef', 1)) {
			$url  = str_replace(':', '-', $url);
		}

		$url  = str_replace('?id=0', '', $url);
		$url  = str_replace('id=0', '', $url);

		return $url;
	}


	public static function getCategoryRoute($catid, $catidAlias = '', $lang = [])
	{
		$pC = PhocacartUtils::getComponentParameters();
        $skip_category_view = $pC->get('skip_category_view', 0);

        if ($skip_category_view == 1) {
        	return self::getItemsRoute('', '', 'c', (int)$catid .'-'.$catidAlias) ;
		}


		$app 		= Factory::getApplication();
		$menu 		= $app->getMenu();
		$active 	= $menu->getActive();
		$option		= $app->input->get( 'option', '', 'string' );
		$view		= $app->input->get( 'view', '', 'string' );

		$activeId 	= 0;
		if (isset($active->id)){
			$activeId    = $active->id;
		}

		if ((int)$activeId > 0 && $option == 'com_phocacart' && $view == 'category') {
			$needles 	= array(
				'category' => (int)$catid,
				'categories' => (int)$activeId
			);
		} else {
			$needles = array(
				'category' => (int)$catid,
				'categories' => ''
			);
		}

		if ($catidAlias != '') {
			$catid = $catid . ':' . $catidAlias;
		}

		$link = 'index.php?option=com_phocacart&view=category&id='.$catid;

		return self::_buildLink($link, $needles, $lang);
	}


	public static function getCategoryRouteByTag($tagId)
	{
		$app 		= Factory::getApplication();
		$menu 		= $app->getMenu();
		$active 	= $menu->getActive();
		$option		= $app->input->get( 'option', '', 'string' );

		$activeId 	= 0;
		if (isset($active->id)){
			$activeId    = $active->id;
		}
		if ((int)$activeId > 0 && $option == 'com_phocacart') {
			$needles 	= array(
				'category' => '',
				'categories' => (int)$activeId
			);
		} else {
			$needles = array(
				'category' => '',
				'categories' => ''
			);
		}

		$db = Factory::getDBO();

		$query = 'SELECT a.id, a.title, a.link_ext, a.link_cat'
		.' FROM #__phocacart_tags AS a'
		.' WHERE a.id = '.(int)$tagId
		.' ORDER BY a.id';

		$db->setQuery($query, 0, 1);
		$tag = $db->loadObject();


		if (isset($tag->id)) {
			$link = 'index.php?option=com_phocacart&view=category&id=tag&tagid='.(int)$tag->id;
		} else {
			$link = 'index.php?option=com_phocacart&view=category&id=tag&tagid=0';
		}
		return self::_buildLink($link, $needles);
	}


	/* Items route can be without id or with id, if id, then it is a category id
	*/
	public static function getItemsRoute($catid = '', $catidAlias = '', $parameter = '', $value = '', string $lang = '')
	{
		$app 		= Factory::getApplication();
		$menu 		= $app->getMenu();
		$active 	= $menu->getActive();
		$option		= $app->input->get( 'option', '', 'string' );

		$activeId 	= 0;
		if (isset($active->id)){
			$activeId    = $active->id;
		}

		if ((int)$activeId > 0 && $option == 'com_phocacart') {

			if (isset($catid) && (int)$catid > 0) {
				$needles = array(
					'items' => (int) $catid,
					'category' => (int) $catid,
					'categories' => (int)$activeId
				);
			} else {
				$needles = array(
					'items' => '',
					'category' => '',
					'categories' => ''
				);
			}

		} else {
			if (isset($catid) && (int)$catid > 0) {
				$needles = array(
					'items' => (int) $catid,
					'category' => (int) $catid,
					'categories' => ''
				);
			} else {
				$needles = array(
					'items' => '',
					'category' => '',
					'categories' => ''
				);
			}
		}

		if ($catidAlias != '') {
			$catid = $catid . ':' . $catidAlias;
		}

		if ($catid != '') {
			$link = 'index.php?option=com_phocacart&view=items&id='.$catid;
		} else if ($parameter != '' && $value != '') {
			$link = 'index.php?option=com_phocacart&view=items&'.htmlspecialchars($parameter).'='.htmlspecialchars($value);
		} else {
			$link = 'index.php?option=com_phocacart&view=items';
		}

		return self::_buildLink($link, $needles, [$lang]);
	}

	public static function getItemRoute($id, $catid = 0, $idAlias = '', $catidAlias = '', $lang = array(), $forceView = 0)
	{
		$app 			= Factory::getApplication();
		$menu 			= $app->getMenu();
		$active 		= $menu->getActive();
		$option			= $app->input->get( 'option', '', 'string' );
		$view 			= $app->input->get( 'view', '', 'string' );

		if ($forceView == 1) {
			$view = 'item';// We link the view from administration - to preview the product
		}

		$activeId 	= 0;
		if (isset($active->id)){
			$activeId    = $active->id;
		}

		if ((int)$activeId > 0 && $option == 'com_phocacart' && $view == 'item') {
			$needles = array(
				'item'  => (int) $id,
				'category' => (int) $catid,
				'categories' => (int)$activeId,
				'items' => (int)$activeId

			);
		} else {
			$needles = array(
				'item'  => (int) $id,
				'category' => (int) $catid,
				'categories' => '',
				'items' => ''
			);
		}

		if ($idAlias != '') {
			$id = (int)$id . ':' . $idAlias;
		}
		if ($catidAlias != '') {
			$catid = (int)$catid . ':' . $catidAlias;
		}

		$link = 'index.php?option=com_phocacart&view=item&id='. $id.'&catid='.$catid;

		return self::_buildLink($link, $needles, $lang);
	}

	public static function getCheckoutRoute($id = 0, $catid = 0, string $lang = '')
	{
		$needles = array(
			'checkout' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => '',
			'items' => ''
		);

		$link = 'index.php?option=com_phocacart&view=checkout';

		return self::_buildLink($link, $needles, [$lang]);
	}

	public static function getPosRoute($ticketId = 1, $unitId = 0, $sectionId = 0, $page = '', $id = 0, $catid = 0)
	{
		$needles = array(
			'pos' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => '',
			'items' => ''
		);

		$link = 'index.php?option=com_phocacart&view=pos';

		if ($page != '') {
			$suffix = '';
			if ((int)$id > 0) {
				switch ($page) {
					case 'section':
					default:
						$suffix = '&sectionid='.(int)$id;
					break;
				}
			}
			$link = $link . '&page='.htmlspecialchars($page). $suffix;
		} else if ($ticketId > 0) {
			$link = $link . '&ticketid='.(int)$ticketId .'&unitid='.(int)$unitId . '&sectionid='.(int)$sectionId;
		}

		return self::_buildLink($link, $needles);
	}

	public static function getAccountRoute($id = 0, $catid = 0, string $lang = '')
	{
		$needles = array(
			'account' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => '',
			'items' => ''
		);

		$link = 'index.php?option=com_phocacart&view=account';
		return self::_buildLink($link, $needles, [$lang]);
	}


	public static function getComparisonRoute($id = 0, $catid = 0, string $lang = '')
	{
		$needles = array(
			'comparison' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => '',
			'items' => ''
		);

		$link = 'index.php?option=com_phocacart&view=comparison';
		return self::_buildLink($link, $needles, [$lang]);
	}

	public static function getWishListRoute($id = 0, $catid = 0, string $lang = '')
	{
		$needles = array(
			'wishlist' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => '',
			'items' => ''
		);

		$link = 'index.php?option=com_phocacart&view=wishlist';
		return self::_buildLink($link, $needles, [$lang]);
	}

	public static function getPaymentRoute($id = 0, $catid = 0, string $lang = '')
	{
		$needles = array(
			//'payment' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => '',
			'items' => ''
		);

		$link = 'index.php?option=com_phocacart&view=payment';
		return self::_buildLink($link, $needles, [$lang]);
	}

	public static function getDownloadRoute($id = 0, $catid = 0, string $lang = '')
	{
		$needles = array(
			'download' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => '',
			'items' => ''
		);

		$link = 'index.php?option=com_phocacart&view=download';
		return self::_buildLink($link, $needles, [$lang]);
	}

	public static function getOrdersRoute($id = 0, $catid = 0, string $lang = '')
	{
		$needles = array(
			'orders' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => '',
			'items' => ''
		);

		$link = 'index.php?option=com_phocacart&view=orders';
		return self::_buildLink($link, $needles, [$lang]);
	}

	public static function getTermsRoute($id = 0, $catid = 0, $suffix = '', string $lang = '')
	{
		$needles = array(
			'terms' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => '',
			'items' => ''
		);

		$link = 'index.php?option=com_phocacart&view=terms';
		if ($suffix != '') {
			$link .= '&'.$suffix;
		}

		return self::_buildLink($link, $needles, [$lang]);
	}

	public static function getInfoRoute($id = 0, $catid = 0, string $lang = '')
	{
		$needles = array(
			//'info' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => '',
			'items' => ''
		);

		$link = 'index.php?option=com_phocacart&view=info';
		return self::_buildLink($link, $needles, $lang);
	}

	public static function getFeedRoute($id = 0, $idAlias = '', $noSEF = 0, string $lang = '')
	{
		$needles = array(
			'feed'  => (int) $id,
			'categories' => '',
			'items' => ''
		);

		if ($idAlias != '') {
			$id = $id . ':' . $idAlias;
		}

		$link = 'index.php?option=com_phocacart&view=feed&id='. $id.'&format=xml';
		if ($noSEF == 1) {
			return $link;
		}

		$xml = self::_buildLink($link, $needles, [$lang]);


		return $xml;
	}


	public static function getQuestionRoute($id = 0, $catid = 0, $idAlias = '', $catidAlias = '', $suffix = '', string $lang = '')
	{
		$app 			= Factory::getApplication();
		$menu 			= $app->getMenu();
		$active 		= $menu->getActive();
		$option			= $app->input->get( 'option', '', 'string' );
		$view			= $app->input->get( 'view', '', 'string' );

		$activeId 	= 0;
		if (isset($active->id)){
			$activeId    = $active->id;
		}

		if ((int)$activeId > 0 && $option == 'com_phocacart' && $view == 'question') {

			$needles = array(
				'question'  => '',
				'item' => (int) $id,
				'category' => (int) $catid,
				'categories' => (int)$activeId,
				'items' => (int)$catid
			);
		} else {
			$needles = array(
				'question'  => '',
				'item' => (int) $id,
				'category' => (int) $catid,
				'categories' => '',
				'items' => ''
			);
		}

		if ($idAlias != '') {
			$id = (int)$id . ':' . $idAlias;
		}
		if ($catidAlias != '') {
			$catid = (int)$catid . ':' . $catidAlias;
		}

		$link = 'index.php?option=com_phocacart&view=question';
		if ($catid != 0) {
			$link .= '&catid='. $catid;
		}

		if ($id != 0) {
			$link .= '&productid='. $id;
		}

		if ($id != 0) {
			$link .= '&id='. $id;
		}

		if ($suffix != '') {
			$link .= '&'.$suffix;
		}

		return self::_buildLink($link, $needles, [$lang]);
	}



	protected static function _buildLink($link, $needles, $lang = [])
	{
		if($item = self::_findItem($needles, 0, $lang)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}

			if (isset($item->id) && ((int)$item->id > 0)) {
				$link .= '&Itemid='.$item->id;
			}

			if (Multilanguage::isEnabled() || I18nHelper::useI18n()) {
                $lang = $lang[0] ?? '';
				if ($lang && $lang !== '*'){
					$link .= '&lang='.$lang;
				}
			}
		}

		return $link;
	}



	protected static function _findItem($needles, $notCheckId = 0, $lang = [])
	{
		$app = Factory::getApplication();
		$menus = AbstractMenu::getInstance('site');
		$active = $menus->getActive();

		$component = ComponentHelper::getComponent('com_phocacart');
		$attributes = ['component_id'];
		$values = [$component->id];

		// Find menu items of current language
		$items = $menus->getItems($attributes, $values);

		// Multilanguage feature - find only items of selected language (e.g. when language module displays flags of different language - each language can have own menu item)
		if (!empty($lang)) {
			$attributes[] 	= 'language';
			$values[]     	= $lang;

			// If multilanguage feature enabled and specific lang set then set menu item of such language
			$itemsLang = $menus->getItems($attributes, $values);

			// If no language items try to find items of current lang and if not found set the current Itemid
			if ($itemsLang) {
				$items = $itemsLang;
			}
		} else if (Multilanguage::isEnabled()) {
			// Just prioritize the current language menu item
			$langCurrent = Factory::getLanguage();
			$langTag = $langCurrent->getTag();

			if ($langTag != '' && $langTag != '*') {
				$attributes[] 	= 'language';
				$values[]     	= $langTag;
				$itemsLang 		= $menus->getItems($attributes, $values);
				if ($itemsLang) {
					$items = $itemsLang;
				}
			}
		}

		// Don't check ID for specific views. e.g. categories view does not have ID
		$notCheckIdArray = ['categories', 'checkout', 'comparison', 'download', 'terms', 'account', 'orders', 'payment', 'info', 'wishlist', 'pos', 'submit'];

		if(!$items) {
			$itemId =  $app->input->get('Itemid', 0, 'int');

			if ($itemId > 0) {
				$item = new stdClass();
				$item->id = $itemId;
				return $item;
			}
			return null;
		}

		$match = null;
		// FIRST - test active menu link
		foreach($needles as $needle => $id) {
			if (isset($active->query['option']) && $active->query['option'] == $component->option
				&& isset($active->query['view']) && $active->query['view'] == $needle
				&& (in_array($needle, $notCheckIdArray) || (isset($active->query['id']) && $active->query['id'] == $id ))
			) {
				$match = $active;
			}
		}

		if(isset($match)) {
			return $match;
		}

		// SECOND - if not find in active, try to run other items
		//          ordered by function which calls this function - e.g. file, category, categories
		//          as last the categories view should be checked, it has no ID so we skip the checking
		//          of ID for categories view with OR: in_array($needle, $notCheckIdArray) ||
		foreach($needles as $needle => $id) {
			foreach($items as $item) {
				// Correct problems when system returns differently null or 0
				if (!isset($item->query['id'])) {
					$item->query['id'] = 0;
				}

				if (isset($item->query['option']) && $item->query['option'] == $component->option
					&& isset($item->query['view']) && $item->query['view'] == $needle
					&& (in_array($needle, $notCheckIdArray) || (isset($item->query['id']) && $item->query['id'] == $id ))
				) {
					$match = $item;
				}
			}

			if(isset($match)) {
				break;// needs to be changed to return in case of THIRD
			}
		}

		if (!$match) {
			// Nothing found, try to set back "categories menu link" so e.g. menu links in module to some category
			// gets no ID from another category which do have a menu link
			// Category A have menu link
			// Category B gets its link becasue view = categories is assigned with active id, which is not categories active id
			foreach($items as $item) {
				// Nothing found, gets some categories view, better than some category view from another category
				if (@$item->query['view'] == 'categories') {
					$match = $item;
					break;
				} else if (@$item->query['view'] == 'items') {
					$match = $item;
					break;
				}
			}
		}

		return $match;
	}

	public static function getItemsRouteSuffix($type, $id, $alias)
	{
		$o = '&'.$type.'='.(int)$id.'-'.urlencode($alias);
		return $o;
	}

	/* ==================
	 * Handle ITEMS View - used for FILTERING AND SEARCHING
	 * 1) If we filter then we go to items view - because of javascript we need to know if we are now in items view or not
	 *    because we filter in module so we can be in every possible view
	 *
	 * 2) So the javascript for filtering needs to know the items view
	 *
	 * 3) Items view can be without ID (category ID) or with ID (category ID)
	 *    If we are in category view or items view we can filter including CATEGORY ID
	 *
	 * 4) GetAliasFromId it tool function only to get separated alias and id from SEF url
	 */
	public static function isItemsView()
	{
		$app	= Factory::getApplication();

		return $app->input->get( 'option', '', 'string') == 'com_phocacart'
			&& $app->input->get( 'view', '', 'string') == 'items';
	}

	public static function getJsItemsRoute($activeCategory = 0)
	{
		$a				= PhocacartRoute::getIdForItemsRoute();

		// Three cases
		if ($activeCategory == 0) {
			// 1) We don't want to include category in filter, e.g. mod_phocacart_filter does not
			// allow to include category filtering (deselecting category)
			// so don't include category
			$urlItemsView	= Route::_(PhocacartRoute::getItemsRoute());
		} else {
			// 2) We want to include category filter and user stays on page where category is active
			// Then he/she will be re-directed to items view but it will include category filtering
			//
			// 3) But if user stays on site where there is no active category, he gets ID = 0 (id of category)
			// so no filtering of category will be done - it is active but user didn't stay on category active page
			$urlItemsView	= Route::_(PhocacartRoute::getItemsRoute($a['id'], $a['alias']));
		}

		$urlItemsView 	= str_replace('&amp;', '&', $urlItemsView);

		return $urlItemsView;
	}

	public static function getJsItemsRouteWithoutParams()
	{
		$urlItemsView	= Route::_(PhocacartRoute::getItemsRoute());
		$urlItemsView 	= str_replace('&amp;', '&', $urlItemsView);

		return $urlItemsView;
	}


	/*
	 * If we are in category route or items route and we add ID, this means a category ID
	 * So we need to paste this ID to the URL of items route
	 */
	public static function getIdForItemsRoute()
	{
		$app			= Factory::getApplication();
		$option			= $app->input->get( 'option', '', 'string' );
		$view			= $app->input->get( 'view', '', 'string' );

		$a['id']		= '';
		$a['alias']		= '';
		$a['idalias']	= '';

		if ($option == 'com_phocacart' && ($view == 'category' || $view == 'items')) {
			$a['id']		= $app->input->get( 'id', '', 'int' );
			$category 		= PhocacartCategory::getCategoryById($a['id']);

			if (!$category) {
				$a['alias'] = '';
				$a['idalias'] = '';
			} else {
				$a['idalias'] = $app->input->get('id', '', 'string');
				$a['alias']   = self::getAliasFromId($a['idalias']);
				$a['idalias'] = str_replace(':', '-', $a['idalias']);
				if (isset($category->alias)) {
					$a['idalias'] = $a['id'] . '-' . $category->alias;
					$a['alias']   = $category->alias;
				}
			}

		} else if ($option == 'com_phocacart' && ($view == 'item')) {
			$a['id']		= $app->input->get( 'catid', '', 'int' );
			$category 		= PhocacartCategory::getCategoryById($a['id']);

			if (!$category) {
				$a['alias'] = '';
				$a['idalias'] = '';
			} else {
				$a['idalias']	= $app->input->get( 'catid', '', 'string' );
				$a['alias']		= self::getAliasFromId($a['idalias']);
				$a['idalias']	= str_replace(':', '-', $a['idalias']);
				if (isset($category->alias)) {
					$a['idalias']	= $a['id'] .'-'. $category->alias;
					$a['alias']		= $category->alias;
				}
			}

		}

		return $a;
	}

	/*
	 * Return only alias from ID url: 25:category-alias (25-category-alias) ==> "category-alias" (ID: 25, ALIAS: category-alias)
	 */
	public static function getAliasFromId($idAndAlias)
	{
		$alias = '';
		if ($idAndAlias != '') {
			$aliasA	= explode(':', $idAndAlias);
			if (isset($aliasA[1]) && $aliasA[1] != '') {
				$alias = $aliasA[1];
			}
		}

		return $alias;
	}

	public static function isFilterActive()
	{
		$app			= Factory::getApplication();
		$option			= $app->input->get( 'option', '', 'string' );
		$view			= $app->input->get( 'view', '', 'string' );

		if ($option == 'com_phocacart' && ($view == 'items')) {
			$id		= $app->input->get( 'id', '', 'int' ); // ID in items view is category id
			if ((int)$id > 0) {
				return true; // some filter is active
			}
		}

		$p = PhocacartUtilsSettings::getListFilterParams();

		if (!empty($p)) {
			foreach($p as $k => $v) {
				$value = $app->input->get( $v, '', 'string' );

				if ($value != '') {
					return true; // some filter is active
				}
			}
		}

		return false;
	}

	public static function getFullUrl($url)
	{
		$url = Route::_($url);

		$frontendUrl 	= str_replace(Uri::root(true).'/administrator/', '',$url);
		$frontendUrl 	= str_replace(Uri::root(true), '', $frontendUrl);
		$frontendUrl 	= str_replace('\\', '/', $frontendUrl);
		$frontendUrl 	= preg_replace('/([^:])(\/{2,})/', '$1/', Uri::root(false). $frontendUrl);

		return $frontendUrl;
	}


	public static function getProductCanonicalLink($id, $catid, $idAlias, $catidAlias, $preferredCatid = 0, string $lang = '')
    {
		if ((int)$preferredCatid > 0) {
            /** @var DatabaseInterface $db */
			$db    = Factory::getContainer()->get(DatabaseInterface::class);
			$query = 'SELECT c.id, c.alias'
				. ' FROM #__phocacart_categories AS c'
				. ' WHERE c.id = ' . (int)$preferredCatid
				. ' ORDER BY c.id';

			$db->setQuery($query, 0, 1);
			$catO = $db->loadObject();
			if (isset($catO->id) && isset($catO->alias)) {
				return self::getItemRoute($id, $catO->id, $idAlias, $catO->alias);
			}
		}

		return self::getItemRoute($id, $catid, $idAlias, $catidAlias, [$lang]);
	}
}
