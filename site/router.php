<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

/**
 * Method to build Route
 * @param array $query
 */ 
function PhocaCartBuildRoute(&$query)
{
	$viewsNoId 	= array('categories', 'checkout', 'comparison', 'download', 'terms', 'account', 'orders', 'payment', 'info');
	$viewsId	= array('category', 'item');
	
	static $items;
	$segments	= array();
	$itemid		= null;
	
	$app = JFactory::getApplication();
	$menu = $app->getMenu();
	
		
	// Break up the weblink/category id into numeric and alias values.
	if (isset($query['id']) && strpos($query['id'], ':')) {
		list($query['id'], $query['alias']) = explode(':', $query['id'], 2);
	}

	// Break up the category id into numeric and alias values.
/*	if (isset($query['catid']) && strpos($query['catid'], ':')) {
		list($query['catid'], $query['catalias']) = explode(':', $query['catid'], 2);
	}*/

	// Get the menu items for this component.
	if (!$items) {

		//$app		= JFactory::getApplication();
		$app    	= JApplication::getInstance('site');
		$menu		= $app->getMenu();
		$items		= $menu->getItems('component', 'com_phocacart');
		
	}
	

	// Search for an appropriate menu item.
	if (is_array($items))
	{
		// If only the option and itemid are specified in the query, return that item.
		if (!isset($query['view']) && !isset($query['id']) && !isset($query['catid']) && !isset($query['download']) && isset($query['Itemid'])) {
			$itemid = (int) $query['Itemid'];
		}

	
		// ------------------------------------------------------
		// Search for a specific link based on the critera given.
		
		if (!$itemid) {
			foreach ($items as $item) {
				
				if (isset($item->id) && isset($query['Itemid']) && $item->id == $query['Itemid']) {
					// We have found the same itemid, now check if the view is equal
					// Only if we find the menu link to the asked view, if there is other menu link
					// e.g. one level higher (categories - category - item) create the itemid below
					if (isset($query['view']) && isset($item->query['view']) && $query['view'] == $item->query['view']) {
						$itemid	= $item->id;
					}	
				}
				
				/*if (isset($item->query['view']) && $item->query['view'] == 'category' && isset($item->query['id']) && isset($query['id']) && $item->query['id'] == $query['id']) {
					$itemid	= $item->id;
				} else if (isset($item->query['view']) && $item->query['view'] == 'item' && isset($item->query['id']) && isset($query['id']) && $item->query['id'] == $query['id']) {
					$itemid	= $item->id;
				} else if (isset($item->query['view']) && $item->query['view'] == 'checkout' && isset($item->query['id']) && isset($query['id']) && $item->query['id'] == $query['id']) {
					$itemid	= $item->id;
				}
				
				else if (isset($item->query['view']) && $item->query['view'] == 'checkout'
					&& isset($query['view']) && $query['view'] != 'category'
					
					
					&& isset($item->query['id']) && isset($query['id']) && $item->query['id'] == $query['id']) {
						$itemid	= $item->id;
				}
				
				else if (isset($item->query['view']) && $item->query['view'] == 'account'
					&& isset($query['view']) && $query['view'] != 'category'
					
					
					&& isset($item->query['id']) && isset($query['id']) && $item->query['id'] == $query['id']) {
						$itemid	= $item->id;
				}
				
				else if (isset($item->query['view']) && $item->query['view'] == 'comparison'
					&& isset($query['view']) && $query['view'] != 'category'
					
					
					&& isset($item->query['id']) && isset($query['id']) && $item->query['id'] == $query['id']) {
						$itemid	= $item->id;
				}
				else if (isset($item->query['view']) && $item->query['view'] == 'download'
					&& isset($query['view']) && $query['view'] != 'category'
					
					
					&& isset($item->query['id']) && isset($query['id']) && $item->query['id'] == $query['id']) {
						$itemid	= $item->id;
						
						
				}
				else if (isset($item->query['view']) && $item->query['view'] == 'orders'
					&& isset($query['view']) && $query['view'] != 'category'
					
					
					&& isset($item->query['id']) && isset($query['id']) && $item->query['id'] == $query['id']) {
						$itemid	= $item->id;
					
				}
				else if (isset($item->query['view']) && $item->query['view'] == 'terms'
					&& isset($query['view']) && $query['view'] != 'category'
					
					
					&& isset($item->query['id']) && isset($query['id']) && $item->query['id'] == $query['id']) {
						$itemid	= $item->id;
				}*/
			}
			
		}
	}

	// Check if the router found an appropriate itemid.
	if (!$itemid) {
		// Check if a category was specified
		if (isset($query['id'])) { // Check if a id was specified.
			if (isset($query['alias'])) {
				$query['id'] .= ':'.$query['alias'];
			}

			// Push the id onto the stack.
			//$segments[] = $query['id'];
			if(isset($query['view'])) {
				$segments[]	= $query['view'];
			}
			$segments[] = $query['id'];
			unset($query['view']);
			unset($query['id']);
			unset($query['alias']);
			unset($query['catid']);
			unset($query['catalias']);
			
		} else {
			if (isset($query['view']) && in_array($query['view'], $viewsNoId)) {
				if(isset($query['view'])) {
					
					
					
					$segments[]	= $query['view']; // Every View without ID
				}
			}
			unset($query['view']);
		}
	} else {
		
		$query['Itemid'] = $itemid;
		// Remove the unnecessary URL segments.
		unset($query['view']);
		unset($query['id']);
		unset($query['alias']);
	}
	
	return $segments;
}

/**
 * Method to parse Route
 * @param array $segments
 */ 
function PhocaCartParseRoute($segments)
{
	
	$viewsNoId 	= array('categories', 'checkout', 'comparison', 'download', 'terms', 'account', 'orders', 'payment', 'info');
	$viewsId	= array('category', 'item');
	
	$vars = array();

	//Get the active menu item
	$app		= JFactory::getApplication();
	$menu		= $app->getMenu();
	$item 		= $menu->getActive();

	// Count route segments
	$count = count($segments);
	
	//Standard routing
	
	if(!isset($item))  {
		if ($count > 2) {
			$vars['view'] = 'categories';
		} else if ($count == 2) {
			$vars['view']  = $segments[$count - 2];
			$vars['id']    = $segments[$count - 1];
		} else if ($count == 1) {
			if(isset($segments[0]) && in_array($segments[0], $viewsNoId)) {
				$vars['view']  = $segments[0];
			} else {
				$vars['view']  = 'categories';
			}
		} 
	} else {
		
		if($count == 1) {
			if(isset($segments[0]) && in_array($segments[0], $viewsNoId)) {
				$vars['view'] 	= $segments[0];
			} else {
				switch($item->query['view']) {
					case 'category':			$vars['view'] 	= 'category';break;
					case 'item':				$vars['view'] 	= 'item';break;
					case 'categories': default:	$vars['view'] 	= 'categories';break;
				}
			}
		} else if ($count == 2) {
			$vars['view'] 	= $segments[$count-2];
			$vars['id'] 	= $segments[$count-1];
		}
	}
	return $vars;
}
?>