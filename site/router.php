<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_phocacart
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
/*
if (! class_exists('PhocacartLoader')) {
    require_once( JPATH_ADMINISTRATOR.'/components/com_phocacart/libraries/loader.php');
}
phocacart import('phocacart.category.category');
*/
JLoader::registerPrefix('Phocacart', JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart');

class PhocacartRouter extends JComponentRouterBase
{
	public function build(&$query) {

		$viewsNoId 		= array('categories', 'checkout', 'comparison', 'download', 'terms', 'account', 'orders', 'payment', 'info', 'items', 'wishlist', 'pos');
		$viewsId		= array('category', 'item', 'items', 'feed');
		$viewsNotOwnId	= array('question');
		$viewsAll	= array_merge($viewsNoId, $viewsId, $viewsNotOwnId);

		$segments = array();

		// Get a menu item based on Itemid or currently active
		$params = PhocacartUtils::getComponentParameters();
		$advanced = $params->get('sef_advanced_link', 0);

		// Unset limitstart=0 since it's pointless
		if (isset($query['limitstart']) && $query['limitstart'] == 0)
		{
			unset($query['limitstart']);
		}

		// We need a menu item.  Either the one specified in the query, or the current active one if none specified
		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
			$menuItemGiven = false;
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);
			$menuItemGiven = true;
		}

		// Check again
		if ($menuItemGiven && isset($menuItem) && $menuItem->component != 'com_phocacart')
		{
			$menuItemGiven = false;
			unset($query['Itemid']);
		}

		if (isset($query['view']))
		{
			$view = $query['view'];
		}
		else
		{
			// We need to have a view in the query or it is an invalid URL
			return $segments;
		}



		// Are we dealing with an item or category that is attached to a menu item?
	/*	if (($menuItem instanceof stdClass)
			&& $menuItem->query['view'] == $query['view']
			&& isset($query['id'])
			&& $menuItem->query['id'] == (int) $query['id'])
		{*/

		if (($menuItem instanceof stdClass)
			&& $menuItem->query['view'] == $query['view']
			&& isset($query['view']) && in_array($query['view'], $viewsNoId)
			) {

			unset($query['view']);

			if (isset($query['catid'])){
				unset($query['catid']);
			}

			if (isset($query['layout'])){
				unset($query['layout']);
			}

			unset($query['id']);

			return $segments;
		}


		// Category
		// Item
		if ($view == 'category' || $view == 'item'){
			if (!$menuItemGiven){
				$segments[] = $view;
			}


			unset($query['view']);



			if ($view == 'item')
			{
				if (isset($query['id']) && isset($query['catid']) && $query['catid'])
				{
					$catid = $query['catid'];



					// Make sure we have the id and the alias
					if (strpos($query['id'], ':') === false)
					{


						$db = JFactory::getDbo();
						$dbQuery = $db->getQuery(true)
							->select('alias')
							->from('#__phocacart_products')
							->where('id=' . (int) $query['id']);
						$db->setQuery($dbQuery);
						$alias = $db->loadResult();
						$query['id'] = $query['id'] . ':' . $alias;

					}
				}
				else
				{
					// We should have these two set for this view.  If we don't, it is an error

					return $segments;
				}
			} else {
				if (isset($query['id']))
				{
					$catid = $query['id'];
				}
				else
				{
					// We should have id set for this view.  If we don't, it is an error
					return $segments;
				}
			}

			if ($menuItemGiven && isset($menuItem->query['id']))
			{
				$mCatid = $menuItem->query['id'];
			}
			else
			{
				$mCatid = 0;
			}

			//$categories = JCategories::getInstance('Content');
			//$category = $categories->get($catid);
			$category = PhocacartCategory::getCategoryById($catid);


			if (!$category)
			{
				// We couldn't find the category we were given.  Bail.
				return $segments;
			}


			$path = PhocacartCategory::getPath(array(), (int)$category->id, (int)$category->parent_id, $category->title, $category->alias);


			$array = array();

			foreach ($path as $id)
			{
				$id = $id['id']. ':'.$id['alias'];
				if ((int) $id == (int) $mCatid)
				{
					break;
				}

				list($tmp, $id) = explode(':', $id, 2);

				$array[] = $id;
			}

			$array = array_reverse($array);

			if (!$advanced && count($array))
			{
				$array[0] = (int) $catid . ':' . $array[0];
			}

			$segments = array_merge($segments, $array);

			if ($view == 'item')
			{
				if ($advanced)
				{
					list($tmp, $id) = explode(':', $query['id'], 2);
				}
				else
				{
					$id = $query['id'];
				}

				$segments[] = $id;
			}

			unset($query['id']);
			unset($query['catid']);
		}

		// Question
		if ($view == 'question'){
			if (!$menuItemGiven){
				$segments[] = $view;
			}

			if (isset($query['view'])) {
				$segments[]	= $query['view'];
				unset($query['view']);
			}
			//unset($query['view']);

			if (isset($query['id']) && isset($query['catid']) && $query['catid']) {
				$catid = $query['catid'];

				// Make sure we have the id and the alias
				if (strpos($query['id'], ':') === false) {


					$db = JFactory::getDbo();
					$dbQuery = $db->getQuery(true)
						->select('alias')
						->from('#__phocacart_products')
						->where('id=' . (int) $query['id']);
					$db->setQuery($dbQuery);
					$alias = $db->loadResult();
					$query['id'] = $query['id'] . ':' . $alias;

				}
			} else {
				// We should have these two set for this view.  If we don't, it is an error

				return $segments;
			}

			if ($menuItemGiven && isset($menuItem->query['id'])) {
				$mCatid = $menuItem->query['id'];
			}
			else
			{
				$mCatid = 0;
			}

			$category = PhocacartCategory::getCategoryById($catid);


			if (!$category) {
				// We couldn't find the category we were given.  Bail.
				return $segments;
			}


			$path = PhocacartCategory::getPath(array(), (int)$category->id, (int)$category->parent_id, $category->title, $category->alias);


			$array = array();

			foreach ($path as $id)
			{
				$id = $id['id']. ':'.$id['alias'];
				if ((int) $id == (int) $mCatid)
				{
					break;
				}

				list($tmp, $id) = explode(':', $id, 2);

				$array[] = $id;
			}

			$array = array_reverse($array);

			if (!$advanced && count($array))
			{
				$array[0] = (int) $catid . ':' . $array[0];
			}

			$segments = array_merge($segments, $array);


			if ($advanced)
			{
				list($tmp, $id) = explode(':', $query['id'], 2);
			}
			else
			{
				$id = $query['id'];
			}

			$segments[] = $id;


			unset($query['id']);
			unset($query['catid']);
		}
		/*
		if ($view == 'question') {

			if (!$menuItemGiven){
				$segments[] = $view;
			}
			if (isset($query['view'])) {
				$segments[]	= $query['view'];
				unset($query['view']);
			}
			if (isset($query['catid'])) {
				$segments[]	= $query['catid'];
				unset($query['catid']);
			}
			if (isset($query['id'])) {
				$segments[]	= $query['id'];
				unset($query['id']);
			}
		}*/

		if (!isset($query['id'])) { // Check if a id was specified.
			if (isset($query['view']) && in_array($query['view'], $viewsNoId)) {
				$segments[]	= $query['view']; // Every View without ID
				unset($query['view']);
			}

		} else {
			if (isset($query['view']) && in_array($query['view'], $viewsId)) {

				$segments[]	= $query['view']; // Every View with ID except (category and item): items, feed
				$segments[]	= $query['id'];
				unset($query['id']);
				unset($query['view']);
			}

		}



		/*
		 * If the layout is specified and it is the same as the layout in the menu item, we
		 * unset it so it doesn't go into the query string.
		 */
		if (isset($query['layout']))
		{
			if ($menuItemGiven && isset($menuItem->query['layout']))
			{
				if ($query['layout'] == $menuItem->query['layout'])
				{
					unset($query['layout']);
				}
			}
			else
			{
				if ($query['layout'] == 'default')
				{
					unset($query['layout']);
				}
			}
		}

		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	public function parse(&$segments) {


		$viewsNoId 		= array('categories', 'checkout', 'comparison', 'download', 'terms', 'account', 'orders', 'payment', 'info', 'items', 'wishlist', 'pos');
		$viewsId		= array('category', 'item', 'items', 'feed');
		$viewsNotOwnId	= array('question');
		$viewsAll		= array_merge($viewsNoId, $viewsId, $viewsNotOwnId);

		// question - can be an ID page (id of product) but without ID page - direct link

		$total = count($segments);
		$vars = array();



		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		// Get the active menu item.
		$item = $this->menu->getActive();
		$params = PhocacartUtils::getComponentParameters();
		$advanced = $params->get('sef_advanced_link', 0);
		$db = JFactory::getDbo();

		// Count route segments
		$count = count($segments);


		/*
		 * Standard routing for items.  If we don't pick up an Itemid then we get the view from the segments
		 * the first segment is the view and the last segment is the id of the item or category.
		 */
		if (!isset($item)) {
			$vars['view'] = $segments[0];

			// Called if no menu item created
			$vars['id'] = $segments[$count - 1];

			unset($segments[0]);
			return $vars;
		}

		// First handle views without ID
		if ($count == 1) {
			if(isset($segments[0]) && in_array($segments[0], $viewsNoId)) {
					$vars['view']  = $segments[0];
				unset($segments[0]);
				return $vars;
			}

			// Question can include ID/CATID but can be without ID/CATID
			if(isset($segments[0]) && in_array($segments[0], $viewsNotOwnId)) {
					$vars['view']  = $segments[0];

				return $vars;
			}
		}

		/*
		 * If there is only one segment, then it points to either an item or a category.
		 * We test it first to see if it is a category.  If the id and alias match a category,
		 * then we assume it is a category.  If they don't we assume it is an item
		 */

		if ($count == 1) {
			// We check to see if an alias is given.  If not, we assume it is an item CATEGORY BETTER
			// Mostly handling of wrong URl: categories/wrong-alias ( return category = 0, no category found)
			//                               category/wrong-alias (return item = 0, no product found)
			if (strpos($segments[0], ':') === false)
			{
				if (isset($item->query['view']) && $item->query['view'] == 'category') {
					$vars['view'] = 'item';
				} else if (isset($item->query['view']) && $item->query['view'] == 'categories') {
					$vars['view'] = 'category';
				} else {
					$vars['view'] = 'categories';
				}

				$vars['id'] = (int) $segments[0];

				return $vars;
			}

			list($id, $alias) = explode(':', $segments[0], 2);

			// First we check if it is a category
			///$category = JCategories::getInstance('Content')->get($id);
			$category = PhocacartCategory::getCategoryById($id);


			if ($category && $category->alias == $alias)
			{
				$vars['view'] = 'category';
				$vars['id'] = $id;
				unset($segments[0]);
				return $vars;
			} else {
				// TO DO specify catid - load from libraries
				$query = $db->getQuery(true)
					->select($db->quoteName(array('alias', 'catid')))
					->from($db->quoteName('#__phocacart_products'))
					->where($db->quoteName('id') . ' = ' . (int) $id);
				$db->setQuery($query);
				$item1 = $db->loadObject();


				if ($item1) {
					if ($item1->alias == $alias) {

						$vars['view'] 	= 'item';
						$vars['id'] 	= (int) $id;
						$vars['catid']	 = (int) $item1->catid;

						// We have direct link to category view and item1->catid is null
						if ((int) $vars['catid'] == 0 && isset($item->query['id']) && $item->query['id'] > 0) {
							$vars['catid'] = (int)$item->query['id'];
						}


						return $vars;
					}
				}
			}
		}

		/*
		 * If there was more than one segment, then we can determine where the URL points to
		 * because the first segment will have the target category id prepended to it.  If the
		 * last segment has a number prepended, it is an item, otherwise, it is a category.
		 */


		if (!$advanced) {

			$view 	= '';
			$id 	= 0;
			$catid 	= 0;
			if ($count == 3) {

				// Second part can be category/subcategory string
				$second = explode(':', $segments[1]);

				if (isset($second[0]) && (int)$second[0] > 0) {
					// Question
					$view 		= $segments[0];
					$cat_id 	= (int)$segments[1];
					$item_id 	= (int)$segments[2];

				} else {
					$view 		= 'item';// can be category, right view will be solved after
					$cat_id 	= (int)$segments[0];
					// $segments[1] is a part of category/subcategory string
					$item_id 	= (int)$segments[2];

				}

			} else {
				// Item
				$view		= '';
				$cat_id 	= (int)$segments[0];
				$item_id 	= (int)$segments[$count - 1];

			}


			if ($cat_id > 0) {
				if ($item_id > 0) {
					if ($view != '') {
						$vars['view'] = $view;
					} else {
						$vars['view'] = 'item';
					}

					$vars['catid'] = $cat_id;
					$vars['id'] = $item_id;


				} else {
					$vars['view'] = 'category';
					$vars['id'] = $cat_id;
				}
			} else {
				if ($item_id > 0)
				{
					if ($count == 3) {
						$vars['view'] 	= $view;
						$vars['catid'] 	= $cat_id;
						$vars['id'] 	= $item_id;
					} else {
						// Other than category or item view with ID (items, feed)
						$vars['view'] = $segments[0];
						$vars['id'] = $item_id;
					}
				}
			}

		/*	if (empty($vars) && count($segments) > 1) {

				throw new Exception(JText::_('Nothing found'), 404);
				return false;
			}
		*/

			unset($segments[0]);
			unset($segments[1]);
			return $vars;
		}
/*
		// We get the category id from the menu item and search from there
		if (isset($item->query['id'])) {
			$id = $item->query['id'];
		} else {

			if ($count == 3) {
				// Question

				$id 	= (int)$segments[$count - 2];

			} else {
				// Item
				$id 	= (int)$segments[0];
			}
		}




		$category = PhocacartCategory::getCategoryById($id);

		if (!$category)
		{
			throw new Exception(JText::_('COM_PHOCACART_ERROR_PARENT_CATEGORY_NOT_FOUND'), 404);
			return $vars;
		}

		$categories = PhocacartCategory::getChildren($category->id);
		$vars['catid'] = $id;
		$vars['id'] = $id;
		$found = 0;

		foreach ($segments as $segment) {
			$segment = str_replace(':', '-', $segment);

			foreach ($categories as $category)
			{
				if ($category->alias == $segment)
				{
					$vars['id'] = $category->id;
					$vars['catid'] = $category->id;
					$vars['view'] = 'category';
					$categories = PhocacartCategory::getChildren($category->id);
					$found = 1;

					break;
				}
			}

			if ($found == 0)
			{
				if ($advanced)
				{
					$db = JFactory::getDbo();
					$query = $db->getQuery(true)
						->select($db->quoteName('id'))
						->from('#__phocacart_item')
						->where($db->quoteName('catid') . ' = ' . (int) $vars['catid'])
						->where($db->quoteName('alias') . ' = ' . $db->quote($segment));
					$db->setQuery($query);
					$cid = $db->loadResult();
				}
				else
				{
					$cid = $segment;
				}

				$vars['id'] = $cid;
				$vars['view'] = 'item';
			}

			$found = 0;
		}*/



		return $vars;
	}
}

/**
 * Content router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  An array of URL arguments
 *
 * @return  array  The URL arguments to use to assemble the subsequent URL.
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function PhocaCartBuildRoute(&$query)
{
	$router = new PhocacartRouter;

	return $router->build($query);
}

/**
 * Parse the segments of a URL.
 *
 * This function is a proxy for the new router interface
 * for old SEF extensions.
 *
 * @param   array  $segments  The segments of the URL to parse.
 *
 * @return  array  The URL attributes to be used by the application.
 *
 * @since   3.3
 * @deprecated  4.0  Use Class based routers instead
 */
function PhocaCartParseRoute($segments)
{
	$router = new PhocacartRouter;

	return $router->parse($segments);
}
