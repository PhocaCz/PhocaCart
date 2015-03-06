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
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');


class PhocaCartRoute
{

	public static function getItemRoute($id, $catid = 0, $idAlias = '', $catidAlias = '')
	{
		$needles = array(
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => ''
		);
		
		
		if ($idAlias != '') {
			$id = $id . ':' . $idAlias;
		}
		if ($catidAlias != '') {
			$catid = $catid . ':' . $catidAlias;
		}
		
		$link = 'index.php?option=com_phocacart&view=item&id='. $id;


		if($item = self::_findItem($needles)) {
			if (isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		}

		return $link;
	}
	
	public static function getCheckoutRoute($id = 0, $catid = 0)
	{
		$needles = array(
			'item'  => (int) $id,
			'category' => (int) $catid,
			'checkout' => '',
			'categories' => ''
		);

		//Create the link
		$link = 'index.php?option=com_phocacart&view=checkout';

		if($item = self::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			if(isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		};
		return $link;
	}
	
	public static function getAccountRoute($id = 0, $catid = 0)
	{
		$needles = array(
			'item'  => (int) $id,
			'category' => (int) $catid,
			'account' => '',
			'categories' => ''
		);

		//Create the link
		$link = 'index.php?option=com_phocacart&view=account';

		if($item = self::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			if(isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		};
		return $link;
	}

	
	public static function getComparisonRoute($id = 0, $catid = 0)
	{
		$needles = array(
			'item'  => (int) $id,
			'category' => (int) $catid,
			'comparison' => '',
			'categories' => ''
		);

		//Create the link
		$link = 'index.php?option=com_phocacart&view=comparison';

		if($item = self::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			if(isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		};
		return $link;
	}
	
	public static function getPaymentRoute($id = 0, $catid = 0)
	{
		$needles = array(
			'item'  => (int) $id,
			'category' => (int) $catid,
			//'payment' => '',
			'categories' => ''
		);

		//Create the link
		$link = 'index.php?option=com_phocacart&view=payment';

		if($item = self::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			if(isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		};
		return $link;
	}
	
	public static function getDownloadRoute($id = 0, $catid = 0)
	{
		$needles = array(
			'item'  => (int) $id,
			'category' => (int) $catid,
			'download' => '',
			'categories' => ''
		);

		//Create the link
		$link = 'index.php?option=com_phocacart&view=download';

		if($item = self::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			if(isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		};
		
		return $link;
	}
	
	public static function getOrdersRoute($id = 0, $catid = 0)
	{
		$needles = array(
			'item'  => (int) $id,
			'category' => (int) $catid,
			'orders' => '',
			'categories' => ''
		);

		//Create the link
		$link = 'index.php?option=com_phocacart&view=orders';

		if($item = self::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			if(isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		};
		return $link;
	}
	
	public static function getTermsRoute($id = 0, $catid = 0)
	{
		$needles = array(
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => ''
		);

		//Create the link
		$link = 'index.php?option=com_phocacart&view=terms';

		if($item = self::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			if(isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		};
		return $link;
	}
	
	
	public static function getCategoryRoute($catid, $catidAlias = '')
	{
		$needles = array(
			'category' => (int) $catid,
			//'section'  => (int) $sectionid,
			'categories' => ''
		);
		
		if ($catidAlias != '') {
			$catid = $catid . ':' . $catidAlias;
		}

		//Create the link
		$link = 'index.php?option=com_phocacart&view=category&id='.$catid;

		if($item = self::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			if(isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		};

		return $link;
	}
	
	
	public static function getCategoryRouteByTag($tagId)
	{
		$needles = array(
			'category' => '',
			//'section'  => (int) $sectionid,
			'categories' => ''
		);
		
		$db = JFactory::getDBO();
				
		$query = 'SELECT a.id, a.title, a.link_ext, a.link_cat'
		.' FROM #__phocacart_tags AS a'
		.' WHERE a.id = '.(int)$tagId;

		$db->setQuery($query, 0, 1);
		$tag = $db->loadObject();
		
		

		//Create the link
		if (isset($tag->id)) {
			$link = 'index.php?option=com_phocacart&view=category&id=tag&tagid='.(int)$tag->id;
		} else {
			$link = 'index.php?option=com_phocacart&view=category&id=tag&tagid=0';
		}

		if($item = self::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			if(isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		};

		return $link;
	}
	
	public static function getCategoriesRoute()
	{
		$needles = array(
			'categories' => ''
		);
		
		//Create the link
		$link = 'index.php?option=com_phocacart&view=categories';

		if($item = self::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			if (isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		}

		return $link;
	}
	
	public static function getInfoRoute($id = 0, $catid = 0)
	{
		$needles = array(
			//'info' => '',
			'item'  => (int) $id,
			'category' => (int) $catid,
			'categories' => ''
		);
		
		//Create the link
		$link = 'index.php?option=com_phocacart&view=info';

		if($item = self::_findItem($needles)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}
			if (isset($item->id)) {
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
}
?>
