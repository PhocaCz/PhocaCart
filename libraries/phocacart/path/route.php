<?php
/**
 * @version		$Id: route.php 11190 2008-10-20 00:49:55Z ian $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.helper');


class PhocaCartRoute
{
	public static function getCategoriesRoute() {
		
		$app 		= JFactory::getApplication();
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

		if($item = PhocaCartRoute::_findItem($needles, 1)) {
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
		};
		return $link;
	}
	
	public static function getCategoryRoute($catid, $catidAlias = '') {

		$app 		= JFactory::getApplication();
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
		return self::_buildLink($link, $needles);
	}
	
	
	public static function getCategoryRouteByTag($tagId) {

		$app 		= JFactory::getApplication();
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
		
		$db = JFactory::getDBO();
				
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
	public static function getItemsRoute($catid = '', $catidAlias = '') {
		
		$app 		= JFactory::getApplication();
		$menu 		= $app->getMenu();
		$active 	= $menu->getActive();
		$option		= $app->input->get( 'option', '', 'string' );
		$view		= $app->input->get( 'view', '', 'string' );
		
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
		} else {
			$link = 'index.php?option=com_phocacart&view=items';
		}
		return self::_buildLink($link, $needles);
	}

	public static function getItemRoute($id, $catid = 0, $idAlias = '', $catidAlias = '') {

		$app 			= JFactory::getApplication();
		$menu 			= $app->getMenu();
		$active 		= $menu->getActive();
		$option			= $app->input->get( 'option', '', 'string' );
		$view			= $app->input->get( 'view', '', 'string' );
		/*$catidCurrent	= $app->input->get( 'id', 0, 'int' );
		
		if ($catidCurrent > 0) {
			$catid = $catidCurrent;
		}*/
		
		$activeId 	= 0;
		if (isset($active->id)){
			$activeId    = $active->id;
		}
		
		if ((int)$activeId > 0 && $option == 'com_phocacart' && $view == 'item') {
			$needles = array(
				'item'  => (int) $id,
				'category' => (int) $catid,
				'categories' => (int)$activeId
			);
		} else {
			$needles = array(
				'item'  => (int) $id,
				'category' => (int) $catid,
				'categories' => ''
			);
		}

		if ($idAlias != '') {
			$id = (int)$id . ':' . $idAlias;
		}
		if ($catidAlias != '') {
			$catid = (int)$catid . ':' . $catidAlias;
		}
		
		$link = 'index.php?option=com_phocacart&view=item&id='. $id.'&catid='.$catid;
		return self::_buildLink($link, $needles);
	}
	
	public static function getCheckoutRoute($id = 0, $catid = 0) {
		$needles = array(
			'checkout' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => ''
		);

		$link = 'index.php?option=com_phocacart&view=checkout';
		return self::_buildLink($link, $needles);
	}
	
	public static function getAccountRoute($id = 0, $catid = 0) {
		$needles = array(
			'account' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => ''
		);

		$link = 'index.php?option=com_phocacart&view=account';
		return self::_buildLink($link, $needles);
	}

	
	public static function getComparisonRoute($id = 0, $catid = 0) {
		$needles = array(
			'comparison' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => ''
		);

		$link = 'index.php?option=com_phocacart&view=comparison';
		return self::_buildLink($link, $needles);
	}
	
	public static function getWishListRoute($id = 0, $catid = 0) {
		$needles = array(
			'wishlist' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => ''
		);

		$link = 'index.php?option=com_phocacart&view=wishlist';
		return self::_buildLink($link, $needles);
	}
	
	public static function getPaymentRoute($id = 0, $catid = 0) {
		$needles = array(
			//'payment' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => ''
		);

		$link = 'index.php?option=com_phocacart&view=payment';
		return self::_buildLink($link, $needles);
	}
	
	public static function getDownloadRoute($id = 0, $catid = 0) {
		$needles = array(
			'download' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => ''
		);

		$link = 'index.php?option=com_phocacart&view=download';
		return self::_buildLink($link, $needles);
	}
	
	public static function getOrdersRoute($id = 0, $catid = 0) {
		$needles = array(
			'orders' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => ''
		);

		$link = 'index.php?option=com_phocacart&view=orders';
		return self::_buildLink($link, $needles);
	}
	
	public static function getTermsRoute($id = 0, $catid = 0, $suffix = '') {
		$needles = array(
			'terms' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => ''
		);

		$link = 'index.php?option=com_phocacart&view=terms';
		if ($suffix != '') {
			$link .= '&'.$suffix;
		}

		return self::_buildLink($link, $needles);
	}
	
	
	
	public static function getInfoRoute($id = 0, $catid = 0) {
		$needles = array(
			//'info' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => ''
		);
		
		$link = 'index.php?option=com_phocacart&view=info';
		return self::_buildLink($link, $needles);
	}
	
	public static function getFeedRoute($id = 0, $idAlias = '', $noSEF = 0) {
		$needles = array(
			'feed'  => (int) $id,
			'categories' => ''
		);
		
		if ($idAlias != '') {
			$id = $id . ':' . $idAlias;
		}
		
		$link = 'index.php?option=com_phocacart&view=feed&format=xml&id='. $id;
		if ($noSEF == 1) {
			return $link;
		}
		return self::_buildLink($link, $needles);
	}
	
	
	public static function getQuestionRoute($id = 0, $catid = 0, $idAlias = '', $catidAlias = '', $suffix = '') {

		$app 			= JFactory::getApplication();
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
				'categories' => (int)$activeId
			);
		} else {
			$needles = array(
				'question'  => '',
				'item' => (int) $id,
				'category' => (int) $catid,
				'categories' => ''
			);
		}

		if ($idAlias != '') {
			$id = (int)$id . ':' . $idAlias;
		}
		if ($catidAlias != '') {
			$catid = (int)$catid . ':' . $catidAlias;
		}
		
		$link = 'index.php?option=com_phocacart&view=question';
		if ($id != 0) {
			$link .= '&id='. $id;
		}
		if ($catid != 0) {
			$link .= '&catid='. $catid;
		}
		if ($suffix != '') {
			$link .= '&'.$suffix;
		}
		
		return self::_buildLink($link, $needles);
	}
	
	
	
	protected static function _buildLink($link, $needles) {
		
		if($item = self::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			if (isset($item->id) && ((int)$item->id > 0)) {
				$link .= '&Itemid='.$item->id;
			}
		}
		return $link;
	}
	
	

	protected static function _findItem($needles, $notCheckId = 0)
	{
		$app = JFactory::getApplication();
		$menus	= $app->getMenu('site', array());
		$items	= $menus->getItems('component', 'com_phocacart');

	
		if(!$items) {
			return $app->input->get('Itemid', 0, '', 'int');
			//return null;
		}
		
		$match = null;
		

		foreach($needles as $needle => $id)
		{
			
			if ($notCheckId == 0) {
				foreach($items as $item) {
					if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id)) {
						$match = $item;
						break;
					}
				}
			} else {
				foreach($items as $item) {
					if (@$item->query['view'] == $needle) {
						$match = $item;
						break;
					}
				}
			}

			if(isset($match)) {
				break;
			}
		}

		return $match;
	}
	
	public static function getItemsRouteSuffix($type, $id, $alias) {
		
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
	 
	public static function isItemsView() {
		
		$app	= JFactory::getApplication();
		$option	= $app->input->get( 'option', '', 'string' );
		$view	= $app->input->get( 'view', '', 'string' );
		
		if ($option == 'com_phocacart' && $view == 'items') {
			return true;
		}
		return false;
	}
	
	public static function getJsItemsRoute($activeCategory = 0) {
	
		$a				= PhocaCartRoute::getIdForItemsRoute();

		// Three cases
		if ($activeCategory == 0) {
			// 1) We don't want to include category in filter, e.g. mod_phocacart_filter does not
			// allow to include category filtering (deselecting category)
			// so don't include category
			$urlItemsView	= JRoute::_(PhocaCartRoute::getItemsRoute());
			
		} else {
			// 2) We want to include category filter and user stays on page where category is active
			// Then he/she will be redirected to items view but it will include category filtering
			//
			// 3) But if user stays on site where there is no active category, he gets ID = 0 (id of category)
			// so no filtering of category will be done - it is active but user didn't stay on category active page
			$urlItemsView	= JRoute::_(PhocaCartRoute::getItemsRoute($a['id'], $a['alias']));
			
		}

		$urlItemsView 	= str_replace('&amp;', '&', $urlItemsView);

		// Cause URL problems
		//$urlItemsView	= str_replace(JURI::root(true), '', $urlItemsView);
		//$urlItemsView	= ltrim($urlItemsView, '/');
		
		return $urlItemsView;
	}
	
	public static function getJsItemsRouteWithoutParams() {
	
		$urlItemsView	= JRoute::_(PhocaCartRoute::getItemsRoute());
		$urlItemsView 	= str_replace('&amp;', '&', $urlItemsView);

		// Cause URL problems
		//$urlItemsView	= str_replace(JURI::root(true), '', $urlItemsView);
		//$urlItemsView	= ltrim($urlItemsView, '/');
		
		return $urlItemsView;
	}
	
	
	/*
	 * If we are in category route or items route and we add ID, this means a category ID
	 * So we need to paste this ID to the URL of items route
	 */
	public static function getIdForItemsRoute() {
		
		$app			= JFactory::getApplication();
		$option			= $app->input->get( 'option', '', 'string' );
		$view			= $app->input->get( 'view', '', 'string' );
		
		
		
		$a['id']		= '';
		$a['alias']		= '';
		$a['idalias']	= '';
		
		if ($option == 'com_phocacart' && ($view == 'category' || $view == 'items')) {
			$a['id']		= $app->input->get( 'id', '', 'int' );
			$category 		= PhocaCartCategory::getCategoryById($a['id']);
			
			$a['idalias']	= $app->input->get( 'id', '', 'string' );
			$a['alias']		= self::getAliasFromId($a['idalias']);
			$a['idalias']	= str_replace(':', '-', $a['idalias']);
			if (isset($category->alias)) {
				$a['idalias']	= $a['id'] .'-'. $category->alias;
				$a['alias']		= $category->alias;
			}
			
		}
		
		return $a;
	}
		
	/*
	 * Return only alias from ID url: 25:category-alias (25-category-alias) ==> "category-alias" (ID: 25, ALIAS: category-alias)
	 */
	public static function getAliasFromId($idAndAlias) {
		
		$alias = '';
		if ($idAndAlias != '') {
			$aliasA	= explode(':', $idAndAlias);
			if (isset($aliasA[1]) && $aliasA[1] != '') {
				$alias = $aliasA[1];
			}
		}
		
		return $alias;
	}
	
	public static function isFilterActive() {
		
		$app			= JFactory::getApplication();
		$option			= $app->input->get( 'option', '', 'string' );
		$view			= $app->input->get( 'view', '', 'string' );
		
		if ($option == 'com_phocacart' && ($view == 'items')) {
			$id		= $app->input->get( 'id', '', 'int' ); // ID in items view is category id
			if ((int)$id > 0) {
				return true; // some filter is active
			}
		}
		
		$p = PhocaCartSettings::getListFilterParams();
		
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
	
	public static function getFullUrl($url) {
		
		$url = JRoute::_($url);
		$url = JURI::root(false). str_replace(JURI::root(true) . '/', '',$url);
		return $url;
	}
	/*
	public static function getCompleteAlias($id, $alias = '') {
		
		$aliasC = '';
		if ($alias != '') {
			$aliasC	= (int)$id '-'.htmlspecialchars($alias);
		}
		return $aliasC;
	} */
}
?>
