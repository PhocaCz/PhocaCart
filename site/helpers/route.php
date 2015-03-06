<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.helper');

class PhocaMapsHelperRoute
{

	function getOrderRoute($id, $idAlias = '') {
		$needles = array(
			'order'  => (int) $id
		);
		
		
		if ($idAlias != '') {
			$id = $id . ':' . $idAlias;
		}
		
		//Create the link
		$link = 'index.php?option=com_phocacart&view=order&id='. $id;

		if($item = PhocaMapsHelperRoute::_findItem($needles)) {
			if(isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		}

		return $link;
	}
	
	


	function _findItem($needles, $notCheckId = 0)
	{
		$component 	= JComponentHelper::getComponent('com_phocacart');
		$app		= JFactory::getApplication();
		//$menus	= &JApplication::getMenu('site', array());
		//$items	= $menus->getItems('componentid', $component->id);
		//$menu		= &J Site::getMenu();
		$app 	= JFactory::getApplication('site');
		$menu  = $app->getMenu();
		$items		= $menu->getItems('component', 'com_phocacart');

		if(!$items) {
			return  $app->input->get('id', 0, 'int');
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
